<#.SYNOPSIS
.Start SSIS Backend PHP Server
.Description
.Runs the PHP built-in server with correct paths for the SSIS backend.
.NOTES
Must be run from the directory containing this file or specify full paths.
.Example
.PowerShell
.\start-backend.ps1
#>

$ErrorActionPreference = "Stop"

$ssisRoot = "C:\Users\john paul\OneDrive\Documents\Coding\SSIS"
$backendDir = Join-Path $ssisRoot "backend"
$publicDir = Join-Path $backendDir "public"
$routerScript = Join-Path $publicDir "index.php"

Write-Host "Current directory: $(Get-Location)" -ForegroundColor Gray

# Change to the backend directory
Set-Location $backendDir
Write-Host "Changed to: $(Get-Location)" -ForegroundColor Gray

# Use start-process to run in a new window so we can keep the terminal
$proc = Start-Process -FilePath "cmd.exe" -ArgumentList "/c `"cd /d `" + '"' + $backendDir + '"' + " && `"c:\xammp\php\php.exe -S 127.0.0.1:8000 -t backend/public backend/public/index.php`" -PassThru -Wait -WindowStyle Hidden

if ($proc.ExitCode -eq 0) {
    Write-Host "Server started successfully." -ForegroundColor Green
} else {
    Write-Host "Server failed to start. Exit code: $($proc.ExitCode)" -ForegroundColor Red
}
