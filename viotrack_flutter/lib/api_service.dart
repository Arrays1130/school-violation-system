import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:viotrack_flutter/config/api_config.dart';
import 'package:viotrack_flutter/services/session_service.dart';

class ApiService {
  static String get baseUrl => ApiConfig.baseUrl;

  static final ValueNotifier<bool> isOfflineNotifier = ValueNotifier<bool>(false);

  static final Map<String, dynamic> _cache = {};
  static final Map<String, DateTime> _cacheExpiry = {};
  static const Duration cacheDuration = Duration(seconds: 25);

  bool _isCacheValid(String key) {
    if (!_cache.containsKey(key)) return false;
    return DateTime.now().isBefore(_cacheExpiry[key]!);
  }

  void clearCache() {
    _cache.clear();
    _cacheExpiry.clear();
  }

  Future<Map<String, String>> _authHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    return {
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  Future<void> _saveToPersistentCache(String key, dynamic data) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('cache_$key', jsonEncode(data));
  }

  Future<dynamic> getPersistentCache(String key) async {
    final prefs = await SharedPreferences.getInstance();
    final cachedData = prefs.getString('cache_$key');
    if (cachedData != null) {
      return jsonDecode(cachedData);
    }
    return null;
  }

  Future<void> _clearPersistentCache() async {
    final prefs = await SharedPreferences.getInstance();
    final keys = prefs.getKeys().where((k) => k.startsWith('cache_'));
    for (final key in keys) {
      await prefs.remove(key);
    }
  }

  void _handleUnauthorized(http.Response response) {
    if (response.statusCode == 401) {
      SessionService.markExpired();
      throw Exception('Session expired. Please log in again.');
    }
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http
          .post(
            Uri.parse('$baseUrl/mobile/login'),
            headers: {'Accept': 'application/json'},
            body: {
              'email': email,
              'password': password,
              'device_name': 'viotrack_mobile',
            },
          )
          .timeout(Duration(seconds: ApiConfig.isProduction ? 90 : 30));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', data['token']);
        await prefs.setString('user', jsonEncode(data['user']));
        SessionService.reset();
        isOfflineNotifier.value = false;
        return {'success': true, 'message': 'Success'};
      }

      if (response.statusCode == 403) {
        return {
          'success': false,
          'message': 'This account is not authorized for mobile access.',
        };
      }

      try {
        final data = jsonDecode(response.body);
        final message = data['message'] ?? 'Login failed (${response.statusCode})';
        return {'success': false, 'message': message};
      } catch (_) {
        return {'success': false, 'message': 'Login failed (${response.statusCode})'};
      }
    } on Exception catch (e) {
      final msg = e.toString();
      if (msg.contains('TimeoutException') || msg.contains('timed out')) {
        return {
          'success': false,
          'message':
              'Server is waking up. Wait a moment and try again. (Render free tier)',
        };
      }
      return {
        'success': false,
        'message': 'Walang connection sa server. Check your internet.',
      };
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token != null) {
      try {
        await http.post(
          Uri.parse('$baseUrl/mobile/logout'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      } catch (_) {
        // Ignore network errors during logout
      }
    }

    clearCache();
    await _clearPersistentCache();
    await prefs.remove('token');
    await prefs.remove('user');
    SessionService.reset();
  }

  Future<dynamic> getViolations({bool forcedRefresh = false}) async {
    return _getWithCache(
      cacheKey: 'violations',
      uri: Uri.parse('$baseUrl/mobile/violations'),
      forcedRefresh: forcedRefresh,
    );
  }

  Future<dynamic> getCaseDetails(int id, {bool forcedRefresh = false}) async {
    return _getWithCache(
      cacheKey: 'case_$id',
      uri: Uri.parse('$baseUrl/mobile/violations/$id'),
      forcedRefresh: forcedRefresh,
    );
  }

  Future<dynamic> getStats({bool forcedRefresh = false}) async {
    return _getWithCache(
      cacheKey: 'stats',
      uri: Uri.parse('$baseUrl/mobile/stats'),
      forcedRefresh: forcedRefresh,
    );
  }

  Future<dynamic> getNotifications({bool forcedRefresh = false}) async {
    return _getWithCache(
      cacheKey: 'notifications',
      uri: Uri.parse('$baseUrl/mobile/notifications'),
      forcedRefresh: forcedRefresh,
    );
  }

  Future<dynamic> _getWithCache({
    required String cacheKey,
    required Uri uri,
    bool forcedRefresh = false,
  }) async {
    if (!forcedRefresh && _isCacheValid(cacheKey)) {
      return _cache[cacheKey];
    }

    final headers = await _authHeaders();

    try {
      final response = await http
          .get(uri, headers: headers)
          .timeout(const Duration(seconds: 30));

      _handleUnauthorized(response);

      if (response.statusCode == 200) {
        isOfflineNotifier.value = false;
        final decoded = jsonDecode(response.body);
        _cache[cacheKey] = decoded;
        _cacheExpiry[cacheKey] = DateTime.now().add(cacheDuration);
        await _saveToPersistentCache(cacheKey, decoded);
        return decoded;
      }

      throw Exception('Request failed (${response.statusCode})');
    } catch (e) {
      if (e.toString().contains('Session expired')) rethrow;

      isOfflineNotifier.value = true;
      final cached = await getPersistentCache(cacheKey);
      if (cached != null) return cached;

      throw Exception('Walang internet connection at walang naka-save na data.');
    }
  }

  Future<int> getUnreadCount() async {
    try {
      final headers = await _authHeaders();
      final response = await http
          .get(
            Uri.parse('$baseUrl/mobile/notifications/unread-count'),
            headers: headers,
          )
          .timeout(const Duration(seconds: 15));

      _handleUnauthorized(response);

      if (response.statusCode == 200) {
        return jsonDecode(response.body)['count'] ?? 0;
      }
    } catch (_) {}

    return 0;
  }

  Future<void> markNotificationAsRead(String id) async {
    final headers = await _authHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/notifications/$id/read'),
      headers: headers,
    );
    _handleUnauthorized(response);
  }

  Future<void> markAllNotificationsAsRead() async {
    final headers = await _authHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/notifications/mark-all-read'),
      headers: headers,
    );
    _handleUnauthorized(response);
    _cache.remove('notifications');
    _cacheExpiry.remove('notifications');
  }
}
