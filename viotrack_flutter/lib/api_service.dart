import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:viotrack_flutter/config/api_config.dart';
import 'package:viotrack_flutter/services/auth_storage_service.dart';
import 'package:viotrack_flutter/services/session_service.dart';

class ApiService {
  static String get baseUrl => ApiConfig.baseUrl;

  static final ValueNotifier<bool> isOfflineNotifier = ValueNotifier<bool>(false);

  static final Map<String, dynamic> _cache = {};
  static final Map<String, DateTime> _cacheExpiry = {};
  static final Map<String, Future<dynamic>> _inFlight = {};
  static const Duration cacheDuration = Duration(seconds: 45);

  bool _isCacheValid(String key) {
    if (!_cache.containsKey(key)) return false;
    return DateTime.now().isBefore(_cacheExpiry[key]!);
  }

  void clearCache() {
    _cache.clear();
    _cacheExpiry.clear();
    _inFlight.clear();
  }

  Future<Map<String, String>> _authHeaders() async {
    final token = await AuthStorageService.getToken();

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
        await AuthStorageService.saveToken(data['token'] as String);
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
    final token = await AuthStorageService.getToken();

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
    await AuthStorageService.clearToken();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('user');
    SessionService.reset();
  }

  Future<dynamic> getViolations({bool forcedRefresh = false, int page = 1}) async {
    return _getWithCache(
      cacheKey: page == 1 ? 'violations' : 'violations_page_$page',
      uri: Uri.parse('$baseUrl/mobile/violations?page=$page'),
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

  Future<dynamic> getAnalytics({bool forcedRefresh = false}) async {
    return _getWithCache(
      cacheKey: 'analytics',
      uri: Uri.parse('$baseUrl/mobile/analytics'),
      forcedRefresh: forcedRefresh,
    );
  }

  Future<dynamic> getNotifications({bool forcedRefresh = false, int page = 1}) async {
    return _getWithCache(
      cacheKey: page == 1 ? 'notifications' : 'notifications_page_$page',
      uri: Uri.parse('$baseUrl/mobile/notifications?page=$page'),
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

    final existing = _inFlight[cacheKey];
    if (existing != null) {
      return existing;
    }

    final request = _fetchAndCache(cacheKey: cacheKey, uri: uri);
    _inFlight[cacheKey] = request;
    try {
      return await request;
    } finally {
      if (identical(_inFlight[cacheKey], request)) {
        _inFlight.remove(cacheKey);
      }
    }
  }

  Future<dynamic> _fetchAndCache({
    required String cacheKey,
    required Uri uri,
  }) async {
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
      throw Exception('Unread count failed (${response.statusCode})');
    } catch (e) {
      if (e.toString().contains('Session expired')) rethrow;
      rethrow;
    }
  }

  Future<void> markNotificationAsRead(String id) async {
    final headers = await _authHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/notifications/$id/read'),
      headers: headers,
    );
    _handleUnauthorized(response);
    if (response.statusCode != 200) {
      throw Exception('Failed to mark notification as read');
    }
    _cache.remove('notifications');
    _cacheExpiry.remove('notifications');
  }

  Future<void> acknowledgeCase(int caseId) async {
    final headers = await _authHeaders();
    headers['Content-Type'] = 'application/json';
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/cases/$caseId/acknowledge'),
      headers: headers,
    );
    _handleUnauthorized(response);
    if (response.statusCode != 200) {
      throw Exception('Failed to acknowledge case');
    }
    clearCache();
  }

  Future<Map<String, String>> authHeadersForImages() => _authHeaders();

  Future<void> markAllNotificationsAsRead() async {
    final headers = await _authHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/notifications/mark-all-read'),
      headers: headers,
    );
    _handleUnauthorized(response);
    if (response.statusCode != 200) {
      throw Exception('Failed to mark all notifications as read');
    }
    _cache.removeWhere((key, _) => key.startsWith('notifications'));
    _cacheExpiry.removeWhere((key, _) => key.startsWith('notifications'));
  }
}
