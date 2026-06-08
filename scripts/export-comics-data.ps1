param(
    [string] $Service = "app",
    [ValidateSet("comics", "all")]
    [string] $Scope = "comics",
    [ValidateSet("data", "full")]
    [string] $Mode = "data",
    [string] $OutDir = "backups",
    [string] $DbConnection,
    [string] $DbHost,
    [string] $DbPort,
    [string] $DbDatabase,
    [string] $DbUsername,
    [string] $DbPassword
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

if (-not (Test-Path $OutDir)) {
    New-Item -ItemType Directory -Path $OutDir | Out-Null
}

$helper = "scripts/export-db.sh"
if (-not (Test-Path $helper)) {
    throw "Missing $helper"
}

$envValues = Read-DotEnv ".env"
$dbKeys = @("DB_CONNECTION", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD")
$explicitValues = @{
    DB_CONNECTION = $DbConnection
    DB_HOST = $DbHost
    DB_PORT = $DbPort
    DB_DATABASE = $DbDatabase
    DB_USERNAME = $DbUsername
    DB_PASSWORD = $DbPassword
}
$exports = @()

foreach ($key in $dbKeys) {
    $value = $null

    if ($explicitValues[$key]) {
        $value = $explicitValues[$key]
    } elseif ($envValues.ContainsKey($key)) {
        $value = $envValues[$key]
    } elseif ([Environment]::GetEnvironmentVariable($key)) {
        $value = [Environment]::GetEnvironmentVariable($key)
    }

    if ($null -ne $value) {
        $exports += "$key=$(Quote-Sh $value)"
    }
}

if (-not $envValues.ContainsKey("DB_DATABASE") -and -not [Environment]::GetEnvironmentVariable("DB_DATABASE")) {
    Write-Host "DB_DATABASE was not found in local .env. The script will still try the container environment."
}

docker compose cp $helper "${Service}:/tmp/export-db.sh" | Out-Null
docker compose exec -T $Service sh -lc "chmod +x /tmp/export-db.sh"

$exportPrefix = ""
if ($exports.Count -gt 0) {
    $exportPrefix = ($exports -join " ") + " "
}

$command = "${exportPrefix}BACKUP_SCOPE=$(Quote-Sh $Scope) DUMP_MODE=$(Quote-Sh $Mode) /tmp/export-db.sh"
$remoteOutput = docker compose exec -T $Service sh -lc $command
$exitCode = $LASTEXITCODE

if ($exitCode -ne 0) {
    throw "Export failed. Check DB_* values in .env and make sure the database container is reachable from $Service."
}

$remotePath = ($remoteOutput | Select-Object -Last 1)

if ($null -eq $remotePath) {
    throw "Export did not return a backup path."
}

$remotePath = $remotePath.Trim()

if (-not $remotePath.EndsWith(".sql.gz")) {
    throw "Could not detect backup path from container output: $remotePath"
}

$fileName = Split-Path $remotePath -Leaf
$localPath = Join-Path $OutDir $fileName

docker compose cp "${Service}:$remotePath" $localPath | Out-Null

Write-Host "Exported: $localPath"
Write-Host "Import later with:"
Write-Host "  gzip -dc $fileName | docker compose -f docker-compose.yml exec -T app sh -lc 'mysql -h `$DB_HOST -P `$DB_PORT -u `$DB_USERNAME -p`$DB_PASSWORD `$DB_DATABASE'"
