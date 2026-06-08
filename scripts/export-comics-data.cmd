@echo off
setlocal

cd /d "%~dp0\.."
powershell -ExecutionPolicy Bypass -File scripts\export-comics-data.ps1 -Scope comics -Mode data -OutDir backups

echo.
if errorlevel 1 (
    echo Export failed. Check the error above.
    pause
    exit /b 1
)

echo Done. Check the backups folder for the generated .sql.gz file.
pause
