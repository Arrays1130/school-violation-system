import 'package:flutter/foundation.dart' show kIsWeb;

/// Base URL for the Laravel API (no trailing slash).
/// Currently using ngrok tunnel for public access.
class ApiConfig {
  // 🌐 Production URL (Render)
  static const String _ngrokUrl =
      'https://school-violation-system.onrender.com/api';

  // Local fallback (Laragon)
  static const String _laragonDefault =
      'http://127.0.0.1/school%20violation%20system/public/api';

  static String get baseUrl {
    if (kIsWeb) return _laragonDefault;
    return _ngrokUrl;
  }

  /// Host for WebSocket / Reverb (port 8080).
  static String get wsHost {
    final uri = Uri.parse(baseUrl);
    return uri.host;
  }
}
