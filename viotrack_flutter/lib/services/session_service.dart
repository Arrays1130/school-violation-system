import 'package:flutter/foundation.dart';

/// Broadcasts when the API returns 401 (expired/invalid token).
class SessionService {
  SessionService._();

  static final ValueNotifier<bool> sessionExpired = ValueNotifier<bool>(false);

  static void markExpired() {
    if (!sessionExpired.value) {
      sessionExpired.value = true;
    }
  }

  static void reset() {
    sessionExpired.value = false;
  }
}
