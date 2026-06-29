@echo off
REM VioTrack mobile app launcher
REM   run_mobile.bat              Production (Render) — default
REM   run_mobile.bat local        Laragon on this PC
REM   set PHONE_API=http://192.168.x.x/school%%20violation%%20system/public/api
REM   run_mobile.bat phone        Physical phone on same WiFi

set MODE=%1
set API_URL=

if /I "%MODE%"=="local" (
  set API_URL=http://127.0.0.1/school%%20violation%%20system/public/api
) else if /I "%MODE%"=="phone" (
  if not "%PHONE_API%"=="" set API_URL=%PHONE_API%
)
if "%API_URL%"=="" set API_URL=https://school-violation-system.onrender.com/api

echo Starting VioTrack with API: %API_URL%
cd /d "%~dp0"
flutter pub get
flutter run --dart-define=API_BASE_URL=%API_URL% %2 %3 %4 %5 %6 %7 %8 %9
