@echo off
REM VioTrack mobile app — Laragon (default)
REM Physical phone: set PHONE_API to your PC LAN IP first, e.g. set PHONE_API=http://192.168.0.117/school%%20violation%%20system/public/api

set API_URL=%PHONE_API%
if "%API_URL%"=="" set API_URL=http://127.0.0.1/school%%20violation%%20system/public/api

echo Starting VioTrack with API: %API_URL%
cd /d "%~dp0"
flutter pub get
flutter run --dart-define=API_BASE_URL=%API_URL% %*
