param(
    [string] $DbService,
    [string] $DbContainer,
    [string] $DbName,
    [string] $DbUser,
    [string] $DbPassword,
    [string] $OutDir = "backups"
)

$ErrorActionPreference = "Stop"

function Read-DotEnv {
    param([string] $Path)

    $values = @{}

    if (-not (Test-Path $Path)) {
        return $values
    }

    foreach ($line in Get-Content $Path) {
        $trimmed = $line.Trim()

        if ($trimmed.Length -eq 0 -or $trimmed.StartsWith("#") -or -not $trimmed.Contains("=")) {
            continue
        }

        $parts = $trimmed.Split("=", 2)
        $key = $parts[0].Trim()
        $value = $parts[1].Trim()

        if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
            $value = $value.Substring(1, $value.Length - 2)
        }

        $values[$key] = $value
    }

    return $values
}

function Quote-Sh {
    param([AllowNull()][string] $Value)

    if ($null -eq $Value) {
        return "''"
    }

    return "'" + $Value.Replace("'", "'\''") + "'"
}

function Container-Env {
    param(
        [string] $Container,
        [string] $Name
    )

    $value = docker exec $Container sh -lc "printenv $(Quote-Sh $Name) 2>/dev/null || true"

    if ($LASTEXITCODE -ne 0) {
        return $null
    }

    $value = ($value | Select-Object -First 1)

    if ($null -eq $value -or $value.Trim().Length -eq 0) {
        return $null
    }

    return $value.Trim()
}

function First-NonEmptyLine {
    param($Lines)

    foreach ($line in $Lines) {
        if ($null -ne $line -and $line.ToString().Trim().Length -gt 0) {
            return $line.ToString().Trim()
        }
    }

    return $null
}

function Is-DatabaseContainer {
    param([string] $Container)

    if (-not $Container) {
        return $false
    }

    $nameImage = docker inspect --format "{{.Name}} {{.Config.Image}}" $Container 2>$null

    if ($LASTEXITCODE -eq 0 -and $nameImage -match "(mysql|mariadb)") {
        return $true
    }

    $envLines = docker inspect --format "{{range .Config.Env}}{{println .}}{{end}}" $Container 2>$null

    if ($LASTEXITCODE -eq 0) {
        foreach ($envLine in $envLines) {
            if ($envLine -match "^(MYSQL_DATABASE|MYSQL_ROOT_PASSWORD|MARIADB_DATABASE|MARIADB_ROOT_PASSWORD)=") {
                return $true
            }
        }
    }

    return $false
}

if (-not (Test-Path $OutDir)) {
    New-Item -ItemType Directory -Path $OutDir | Out-Null
}

$envValues = Read-DotEnv ".env"

if (-not $DbContainer) {
    if ($DbService) {
        $DbContainer = First-NonEmptyLine (docker compose ps -q $DbService 2>$null)
    } else {
        $composeContainers = docker compose ps -q 2>$null

        foreach ($candidate in $composeContainers) {
            $candidate = First-NonEmptyLine @($candidate)

            if ($candidate -and (Is-DatabaseContainer $candidate)) {
                $DbContainer = $candidate
                break
            }
        }
    }
}

if (-not $DbContainer) {
    $runningContainers = docker ps --format "{{.ID}}" 2>$null

    foreach ($candidate in $runningContainers) {
        $candidate = First-NonEmptyLine @($candidate)

        if ($candidate -and (Is-DatabaseContainer $candidate)) {
            $DbContainer = $candidate
            break
        }
    }
}

if (-not $DbContainer) {
    throw "Could not detect a running MySQL/MariaDB container. Pass -DbService <service> or -DbContainer <container>."
}

if (-not (Is-DatabaseContainer $DbContainer)) {
    throw "Container $DbContainer does not look like a MySQL/MariaDB container. Pass the real DB container with -DbContainer <container>."
}

if (-not $DbName) {
    if ($envValues.ContainsKey("DB_DATABASE")) {
        $DbName = $envValues["DB_DATABASE"]
    } else {
        $DbName = Container-Env $DbContainer "MYSQL_DATABASE"
        if (-not $DbName) {
            $DbName = Container-Env $DbContainer "MARIADB_DATABASE"
        }
    }
}

if (-not $DbUser) {
    if ($envValues.ContainsKey("DB_USERNAME")) {
        $DbUser = $envValues["DB_USERNAME"]
    } else {
        $DbUser = Container-Env $DbContainer "MYSQL_USER"
        if (-not $DbUser) {
            $DbUser = Container-Env $DbContainer "MARIADB_USER"
        }
        if (-not $DbUser) {
            $DbUser = "root"
        }
    }
}

if (-not $DbPassword) {
    if ($envValues.ContainsKey("DB_PASSWORD")) {
        $DbPassword = $envValues["DB_PASSWORD"]
    } else {
        $DbPassword = Container-Env $DbContainer "MYSQL_PASSWORD"
        if (-not $DbPassword) {
            $DbPassword = Container-Env $DbContainer "MARIADB_PASSWORD"
        }
        if (-not $DbPassword) {
            $DbPassword = Container-Env $DbContainer "MYSQL_ROOT_PASSWORD"
        }
        if (-not $DbPassword) {
            $DbPassword = Container-Env $DbContainer "MARIADB_ROOT_PASSWORD"
        }
    }
}

if (-not $DbName) {
    throw "Could not detect database name. Pass -DbName <database>."
}

if ($DbPassword -eq "null") {
    $DbPassword = ""
}

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$fileName = "local-db-$DbName-$timestamp.sql.gz"
$remotePath = "/tmp/$fileName"
$localPath = Join-Path $OutDir $fileName
$dumpTool = "command -v mysqldump >/dev/null 2>&1 && echo mysqldump || echo mariadb-dump"
$remoteCommand = @"
set -eu
DUMP_TOOL=`$($dumpTool)
MYSQL_PWD=$(Quote-Sh $DbPassword) `$DUMP_TOOL --user=$(Quote-Sh $DbUser) --single-transaction --quick --skip-lock-tables --routines --triggers --default-character-set=utf8mb4 $(Quote-Sh $DbName) | gzip -9 > $(Quote-Sh $remotePath)
ls -lh $(Quote-Sh $remotePath)
"@

Write-Host "Container: $DbContainer"
if ($DbService) {
    Write-Host "Service: $DbService"
}
Write-Host "Database: $DbName"
Write-Host "Output: $localPath"

docker exec $DbContainer sh -lc $remoteCommand

if ($LASTEXITCODE -ne 0) {
    throw "Database export failed."
}

docker cp "${DbContainer}:$remotePath" $localPath

if ($LASTEXITCODE -ne 0) {
    throw "Could not copy backup file from container."
}

docker exec $DbContainer sh -lc "rm -f $(Quote-Sh $remotePath)" | Out-Null

Write-Host "Backup created: $localPath"
