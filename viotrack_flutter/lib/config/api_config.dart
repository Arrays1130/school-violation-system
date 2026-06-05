import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;

/// Base URL for the Laravel API (no trailing slash).
///
/// Override at build time:
/// `flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api`
class ApiConfig {
  static const String _rawBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8000/api',
  );

  static String get baseUrl {
    if (kIsWeb) {
      return _rawBaseUrl;
    }
    try {
      if (Platform.isAndroid) {
        if (_rawBaseUrl.contains('127.0.0.1')) {
          return _rawBaseUrl.replaceAll('127.0.0.1', '10.0.2.2');
        }
        if (_rawBaseUrl.contains('localhost')) {
          return _rawBaseUrl.replaceAll('localhost', '10.0.2.2');
        }
      }
    } catch (_) {
      // Fallback
    }
    return _rawBaseUrl;
  }
}

