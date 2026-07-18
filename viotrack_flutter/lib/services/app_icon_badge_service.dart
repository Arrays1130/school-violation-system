import 'package:flutter/foundation.dart';

import 'app_icon_badge_stub.dart'
    if (dart.library.html) 'app_icon_badge_web.dart'
    if (dart.library.io) 'app_icon_badge_io.dart';

/// Syncs the launcher / installed-PWA icon badge with unread notification count.
///
/// - Android / iOS: [app_badge_plus]
/// - Installed PWA (Chrome/Edge): Badging API via `navigator.setAppBadge`
class AppIconBadgeService {
  AppIconBadgeService._();

  static int? _lastCount;

  static Future<void> update(int count) async {
    final normalized = count < 0 ? 0 : count;
    if (_lastCount == normalized) return;
    _lastCount = normalized;
    await syncAppIconBadgeImpl(normalized);
  }

  static Future<void> clear() => update(0);

  @visibleForTesting
  static void resetCache() {
    _lastCount = null;
  }
}
