/// Which staff app this binary is: `dean` or `gso`.
///
/// Set at build time:
///   --dart-define=APP_FLAVOR=dean
///   --dart-define=APP_FLAVOR=gso
class AppFlavor {
  static const String value = String.fromEnvironment(
    'APP_FLAVOR',
    defaultValue: 'dean',
  );

  static bool get isGso => value == 'gso';

  static bool get isDean => !isGso;

  static String get appName => isGso ? 'VIOTRACK GSO' : 'VIOTRACK';

  static String get loginSubtitle => isGso
      ? 'GSO — monitor community service & DTR'
      : 'Dean portal — cases & notifications';

  /// Roles allowed to sign in on this APK.
  static bool allowsRole(String? role) {
    final r = (role ?? '').toLowerCase();
    if (isGso) {
      return r == 'gso';
    }
    return r == 'dean' || r == 'admin' || r == 'super_admin';
  }

  static String get unauthorizedMessage => isGso
      ? 'This app is for GSO accounts only. Use VIOTRACK for Dean/Admin.'
      : 'This app is for Dean/Admin accounts. Use VIOTRACK GSO for GSO staff.';
}
