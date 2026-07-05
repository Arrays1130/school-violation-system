/// Push notifications require `android/app/google-services.json`.
/// Set `--dart-define=ENABLE_FCM=false` to disable explicitly.
class PushConfig {
  PushConfig._();

  static const bool enabled = bool.fromEnvironment(
    'ENABLE_FCM',
    defaultValue: true,
  );
}
