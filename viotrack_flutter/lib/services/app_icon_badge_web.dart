import 'dart:js_interop';
import 'dart:js_interop_unsafe';

import 'package:flutter/foundation.dart';

/// PWA / browser Badging API (`navigator.setAppBadge`).
Future<void> syncAppIconBadgeImpl(int count) async {
  try {
    final navigator = _navigator;
    if (!navigator.has('setAppBadge')) return;

    if (count <= 0) {
      if (!navigator.has('clearAppBadge')) return;
      final result = navigator.callMethod<JSAny?>('clearAppBadge'.toJS);
      if (result != null) {
        await (result as JSPromise).toDart;
      }
      return;
    }

    final result = navigator.callMethod<JSAny?>(
      'setAppBadge'.toJS,
      count.toJS,
    );
    if (result != null) {
      await (result as JSPromise).toDart;
    }
  } catch (e) {
    debugPrint('PWA app icon badge update failed: $e');
  }
}

@JS('navigator')
external JSObject get _navigator;
