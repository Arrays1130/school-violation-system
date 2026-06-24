# VioTrack mobile app launcher (Laragon)
# Usage:
#   .\run_mobile.ps1              # Windows desktop / emulator via Laragon
#   .\run_mobile.ps1 -Phone       # Physical Android on same WiFi (uses LAN IP)

param(
    [switch]$Phone,
    [string]$Device = ""
)

$LanIp = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.InterfaceAlias -notmatch 'Loopback' -and $_.IPAddress -notmatch '^169\.'
} | Select-Object -First 1).IPAddress

if ($Phone -and $LanIp) {
    $apiUrl = "http://$LanIp/school%20violation%20system/public/api"
} else {
    $apiUrl = "http://127.0.0.1/school%20violation%20system/public/api"
}

Write-Host "API URL: $apiUrl" -ForegroundColor Cyan
Set-Location $PSScriptRoot

flutter pub get
if (-not $?) { exit 1 }

$args = @("run", "--dart-define=API_BASE_URL=$apiUrl")
if ($Device) { $args += "-d", $Device }

& flutter @args
