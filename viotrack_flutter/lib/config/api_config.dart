import 'dart:io' show Platform;
import 'package:flutter/foundation.dart' show kIsWeb;

/// Base URL for the Laravel API (no trailing slash).
///
/// Build examples:
///   Production (default): uses Render
///   Local Laragon: flutter run --dart-define=API_BASE_URL=http://127.0.0.1/school%20violation%20system/public/api
///   Local artisan:   flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
class ApiConfig {
  static const String productionUrl =
      'https://school-violation-system.onrender.com/api';

  static const String laragonUrl =
      'http://127.0.0.1/school%20violation%20system/public/api';

  static const String _rawBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: productionUrl,
  );

  static String get baseUrl {
    if (kIsWeb) {
      if (_rawBaseUrl != productionUrl && _rawBaseUrl != laragonUrl) {
        return _rawBaseUrl;
      }
      // Same-origin API when dean web app is hosted on Laravel (e.g. /dean-app/)
      final uri = Uri.base;
      if (uri.path.contains('/public/')) {
        final idx = uri.path.indexOf('/public/');
        return '${uri.origin}${uri.path.substring(0, idx + '/public'.length)}/api';
      }
      return '${uri.origin}/api';
    }

    try {
      if (Platform.isAndroid) {
        var url = _rawBaseUrl;
        if (url.contains('127.0.0.1')) {
          url = url.replaceAll('127.0.0.1', '10.0.2.2');
        }
        if (url.contains('localhost')) {
          url = url.replaceAll('localhost', '10.0.2.2');
        }
        return url;
      }
    } catch (_) {
      // Platform not available (e.g. tests)
    }

    return _rawBaseUrl;
  }

  static bool get isProduction => baseUrl.contains('onrender.com');

  /// Host for WebSocket / Reverb (port 8080).
  static String get wsHost => Uri.parse(baseUrl).host;
}
