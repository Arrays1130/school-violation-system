# VioTrack Dean App — Readiness & Deep Dive

**Version:** `3.0.0+5` · **Package:** `com.viotrack.dean`  
**Last updated:** July 2026

---

## Verdict

| Use case | Ready? |
|----------|--------|
| Dean pilot / internal daily use | **Yes** — core flows are solid |
| Official school-wide rollout | **Almost** — finish release build + Firebase + real-device pass |

**Bottom line:** Not “zero problems forever,” but **no known major app bugs** in code. Remaining items are mostly **configuration, ops, and final testing**.

---

## What’s done (code)

| Area | Status |
|------|--------|
| I-LINK UI / Inter typography | Done across main screens |
| Login + biometric lock | Polished flow |
| Dashboard, cases, case details, analytics | Refreshed + overflow-safe |
| Auth API token | Secure storage (Keychain / Keystore) |
| Alerts while app is open | Poller with backoff + offline-aware |
| Firebase push (Android) | Code wired — needs `google-services.json` |
| Dead dependencies | Removed (`lottie`, `web_socket_channel`, old WebSocket service) |
| Code health | `flutter analyze` clean · `flutter test` passing |

---

## Still needs setup (not code bugs)

### 1. Firebase push (optional but recommended)

1. Create Firebase project → Android app `com.viotrack.dean`
2. Download `google-services.json` → `android/app/google-services.json`
3. Set `FCM_SERVER_KEY` in Laravel `.env`
4. Rebuild: `flutter build apk --release`

Until then: alerts work via **15s polling** while the app is open; **no push when app is closed**.

### 2. Release APK

1. Copy `android/key.properties.example` → `android/key.properties`
2. Generate keystore (see README)
3. Build: `flutter build apk --release`
4. Install APK on a **real Android phone** (not only emulator)

### 3. Backend (Render)

- Free tier may **cold-start** → first login can be slow (“server waking up”)
- Confirm production API: `https://school-violation-system.onrender.com/api`

---

## Known limitations (acceptable tradeoffs)

- **`user` profile JSON** — still in `SharedPreferences` (display data only; token is secure)
- **Biometric login** — saves email/password in secure storage when enabled
- **iPhone** — PWA via Safari (`/dean-app/`), not a native iOS app
- **Push** — Android-first in current bootstrap; iOS uses different path (PWA)
- **Offline UX** — cached data works; banners could be clearer (future polish)

---

## Real device test checklist

Run on a physical Android phone with production API:

- [ ] **Login** — email/password succeeds
- [ ] **Biometric** — enable in Profile, lock/unlock on reopen
- [ ] **Dashboard** — stats load, hearing cards no overflow
- [ ] **Cases** — list, filters, search, open case
- [ ] **Case details** — long names/titles, acknowledge if applicable
- [ ] **Analytics** — charts load (empty + populated)
- [ ] **Notifications** — list, mark read, tap → case details
- [ ] **Profile** — shows user, sign out works
- [ ] **Background / resume** — badge updates after reopen
- [ ] **Airplane mode** — offline banner, cached data where available
- [ ] **Push** (if Firebase configured) — notification when app closed, tap opens case
- [ ] **Logout** — returns to login, session cleared

---

## Quick commands

```bash
cd viotrack_flutter
flutter pub get
flutter analyze
flutter test
flutter run                                    # debug, production API
flutter build apk --release                    # release APK
```

Disable FCM explicitly:

```bash
flutter run --dart-define=ENABLE_FCM=false
```

---

## What to do next (recommended order)

1. Real device test checklist (above)
2. Release signed APK
3. Add Firebase + test push
4. Hand APK to dean pilot group
5. Collect feedback → next polish (offline UX, etc.)

---

## Support notes for deans

- **Slow login first time:** wait ~30s and retry (Render wake-up)
- **No alerts when app closed:** enable Firebase push OR keep app open / check Alerts tab
- **Biometric failed:** use email/password; re-enable biometrics in Profile
