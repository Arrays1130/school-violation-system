# Build VioTrack Dean web app (iPhone PWA — same as Android APK)
param(
    [string]$ApiUrl = "",
    [switch]$Local
)

$Root = Split-Path $PSScriptRoot -Parent
$FlutterDir = Join-Path $Root "viotrack_flutter"
$OutDir = Join-Path $Root "public\dean-app"

if ($Local) {
    $ApiUrl = "http://127.0.0.1/school%20violation%20system/public/api"
} elseif ($ApiUrl -eq "") {
    $ApiUrl = "https://school-violation-system.onrender.com/api"
}

Write-Host "Building Dean Web App..." -ForegroundColor Cyan
Write-Host "API: $ApiUrl" -ForegroundColor Gray
Write-Host "Output: $OutDir" -ForegroundColor Gray

Set-Location $FlutterDir
flutter pub get
if (-not $?) { exit 1 }

flutter build web --release `
    --base-href /dean-app/ `
    --dart-define=API_BASE_URL=$ApiUrl

if (-not $?) { exit 1 }

if (Test-Path $OutDir) {
    Get-ChildItem $OutDir -Exclude ".gitkeep" | Remove-Item -Recurse -Force
}
New-Item -ItemType Directory -Path $OutDir -Force | Out-Null
Copy-Item -Path "build\web\*" -Destination $OutDir -Recurse -Force

Write-Host ""
Write-Host "Done! Dean web app built to public/dean-app/" -ForegroundColor Green
Write-Host ""
Write-Host "Local test:" -ForegroundColor Yellow
Write-Host "  php artisan serve" -ForegroundColor White
Write-Host "  http://127.0.0.1:8000/dean-app/" -ForegroundColor White
Write-Host ""
Write-Host "Production:" -ForegroundColor Yellow
Write-Host "  https://school-violation-system.onrender.com/dean-app/" -ForegroundColor White
