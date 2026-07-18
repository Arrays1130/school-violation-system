import 'package:app_badge_plus/app_badge_plus.dart';
import 'package:flutter/foundation.dart';

Future<void> syncAppIconBadgeImpl(int count) async {
  try {
    final supported = await AppBadgePlus.isSupported();
    if (!supported) return;
    await AppBadgePlus.updateBadge(count < 0 ? 0 : count);
  } catch (e) {
    debugPrint('App icon badge update failed: $e');
  }
}
