import 'package:flutter/foundation.dart' show kIsWeb, debugPrint;
import 'dart:io' show Platform;
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'fcm_service.dart';

/// Initializes Firebase push on Android when configuration is present.
class PushBootstrap {
  PushBootstrap._();

  static bool _initialized = false;
  static bool get isInitialized => _initialized;

  static Future<void> init() async {
    if (kIsWeb || _initialized) return;

    try {
      if (!Platform.isAndroid) return;
      await Firebase.initializeApp();
      FirebaseMessaging.onBackgroundMessage(handleBackgroundMessage);
      await FCMService.initialize();
      _initialized = true;
    } catch (e) {
      debugPrint('Push bootstrap skipped: $e');
    }
  }
}
