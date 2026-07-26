/// Push notifications require `android/app/google-services.json`.
///
/// Default is **off** so missing Firebase config does not throw at startup.
/// Enable only when the real google-services.json is present:
///   flutter run --dart-define=ENABLE_FCM=true
///   flutter build apk --dart-define=ENABLE_FCM=true
class PushConfig {
  PushConfig._();

  static const bool enabled = bool.fromEnvironment(
    'ENABLE_FCM',
    defaultValue: false,
  );
}
