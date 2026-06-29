# VioTrack mobile app launcher (Laragon)
# Usage:
#   .\run_mobile.ps1                    # Production (Render) — default
#   .\run_mobile.ps1 -Local             # Laragon on this PC
#   .\run_mobile.ps1 -Phone             # Physical phone on same WiFi (LAN IP)
#   .\run_mobile.ps1 -Device emulator-5554

param(
    [switch]$Local,
    [switch]$Phone,
    [string]$Device = ""
)

$LanIp = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.InterfaceAlias -notmatch 'Loopback' -and $_.IPAddress -notmatch '^169\.'
} | Select-Object -First 1).IPAddress

if ($Local) {
    $apiUrl = "http://127.0.0.1/school%20violation%20system/public/api"
} elseif ($Phone -and $LanIp) {
    $apiUrl = "http://$LanIp/school%20violation%20system/public/api"
} else {
    $apiUrl = "https://school-violation-system.onrender.com/api"
}

Write-Host "API URL: $apiUrl" -ForegroundColor Cyan
Set-Location $PSScriptRoot

flutter pub get
if (-not $?) { exit 1 }

$args = @("run", "--dart-define=API_BASE_URL=$apiUrl")
if ($Device) { $args += "-d", $Device }

& flutter @args
