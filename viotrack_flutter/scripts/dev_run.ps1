# Single clean Flutter dev session — avoids VS Code "app not found" errors.
# Usage: .\scripts\dev_run.ps1

param(
    [string]$Device = "emulator-5554",
    [switch]$Local
)

$apiUrl = if ($Local) {
    "http://127.0.0.1/school%20violation%20system/public/api"
} else {
    "https://school-violation-system.onrender.com/api"
}

Set-Location $PSScriptRoot\..

Write-Host "Stopping old VioTrack instance on $Device..." -ForegroundColor Yellow
adb -s $Device shell am force-stop com.viotrack.dean 2>$null

Write-Host "API: $apiUrl" -ForegroundColor Cyan
Write-Host "Press R = hot restart | q = quit" -ForegroundColor Gray

flutter run -d $Device --dart-define=API_BASE_URL=$apiUrl
