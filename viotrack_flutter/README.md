# VioTrack Dean Mobile App

Dean-focused **view · notify · track** companion for the VioTrack school violation system.

| Platform | Install |
|----------|---------|
| **Android** | Build APK (`flutter build apk --release`) |
| **iPhone** | Safari → Add to Home Screen PWA at `/dean-app/` |

See also [DEAN_WEB_IOS.md](DEAN_WEB_IOS.md) for iPhone PWA steps.

**Readiness, limitations, and test checklist:** [DEAN_APP_READINESS.md](DEAN_APP_READINESS.md)

## Features

- Dashboard, cases, insights, alerts, profile
- Near-real-time alerts (15s polling while app is open)
- Biometric lock (Android APK)
- Offline cache for core data

## Setup

```bash
cd viotrack_flutter
flutter pub get
```

### API URL

| Environment | Command |
|-------------|---------|
| Production (Render) | `flutter run` (default) |
| Local Laragon | `flutter run --dart-define=API_BASE_URL=http://127.0.0.1/school%20violation%20system/public/api` |
| Android emulator + artisan | `flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api` |

### Assets

Place branding images in `assets/images/`:

- `ilink_college_logo.png` — app icon / splash / branded auth logo
- `login-campus-bg.png` — login background
- `ilink_logo.png` — login logo

Then run:

```bash
dart run flutter_launcher_icons
dart run flutter_native_splash:create
```

### Android release signing

1. Copy `android/key.properties.example` → `android/key.properties`
2. Generate keystore: `keytool -genkey -v -keystore android/viotrack-release.keystore -alias viotrack -keyalg RSA -keysize 2048 -validity 10000`
3. Build: `flutter build apk --release`

Application ID: `com.viotrack.dean`

### Firebase push (Android, optional)

1. Create Firebase project with package `com.viotrack.dean`
2. Download `google-services.json` → `android/app/google-services.json`
3. Set `FCM_SERVER_KEY` in Laravel `.env`
4. Rebuild APK (`flutter build apk --release`)

When configured, the app will:
- Sync the device FCM token after login and on token refresh
- Show alerts while the app is open (foreground)
- Deliver system notifications when the app is backgrounded or closed
- Open **Case Details** when a dean taps a notification that includes `case_id`

Disable push explicitly with `--dart-define=ENABLE_FCM=false`.

Push is skipped gracefully if Firebase is not configured.

### iOS PWA build

From repo root:

```powershell
.\scripts\build-dean-web.ps1
```

Deploy `public/dean-app/` with Laravel.

## Tests

```bash
flutter test
php artisan test --filter=MobileApiTest
```
