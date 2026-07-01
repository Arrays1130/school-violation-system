# VioTrack Dean — Web App for iPhone (no Mac needed)

Same features as the Android APK: **view cases**, **dashboard**, **notifications**.

## iPhone install (Add to Home Screen)

1. Open **Safari** on iPhone (not Chrome)
2. Go to: **https://school-violation-system.onrender.com/dean-app/**
3. Login: `dean.cce@example.com` / `password`
4. Tap **Share** (square with arrow)
5. Tap **Add to Home Screen**
6. Tap **Add**

The VioTrack icon will appear on your home screen like a native app.

## Android

Use the APK (`VioTrack-v2.apk`) — better performance than web.

## Build locally (Windows)

```powershell
cd "c:\laragon\www\school violation system"
.\scripts\build-dean-web.ps1
php artisan serve
# Open http://127.0.0.1:8000/dean-app/
```

Laragon:

```powershell
.\scripts\build-dean-web.ps1 -Local
# Open http://127.0.0.1/school%20violation%20system/public/dean-app/
```

## Production deploy

The `Dockerfile` builds Flutter web automatically on Render deploy.
URL: **https://school-violation-system.onrender.com/dean-app/**

## Notifications on iPhone

- **In-app alerts** (Alerts tab + badge): works like Android
- **Push pop-up when app is closed**: requires native iOS app (Mac + Apple Developer)
