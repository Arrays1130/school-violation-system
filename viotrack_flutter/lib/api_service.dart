import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart';
import 'package:viotrack_flutter/config/api_config.dart';

class ApiService {
  static String get baseUrl => ApiConfig.baseUrl;
  
  static final ValueNotifier<bool> isOfflineNotifier = ValueNotifier<bool>(false);
  
  // Optimization: In-memory cache for speed
  static final Map<String, dynamic> _cache = {};
  static final Map<String, DateTime> _cacheExpiry = {};
  static const Duration cacheDuration = Duration(seconds: 25);

  bool _isCacheValid(String key) {
    if (!_cache.containsKey(key)) return false;
    return DateTime.now().isBefore(_cacheExpiry[key]!);
  }

  void _invalidateCache(String key) {
    _cache.remove(key);
    _cacheExpiry.remove(key);
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

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/mobile/login'),
        headers: {'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true'},
        body: {
          'email': email,
          'password': password,
          'device_name': 'mobile_app',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', data['token']);
        await prefs.setString('user', jsonEncode(data['user']));
        return {'success': true, 'message': 'Success'};
      } else {
        try {
          final data = jsonDecode(response.body);
          final message = data['message'] ?? 'Login failed (${response.statusCode})';
          return {'success': false, 'message': message};
        } catch (_) {
          return {'success': false, 'message': 'Error Code: ${response.statusCode}'};
        }
      }
    } catch (e) {
      return {'success': false, 'message': 'Connection error: ${e.toString()}'};
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    if (token != null) {
      await http.post(
        Uri.parse('$baseUrl/mobile/logout'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
          'ngrok-skip-browser-warning': 'true',
        },
      );
    }
    
    await prefs.remove('token');
    await prefs.remove('user');
  }

  Future<dynamic> getViolations({bool forcedRefresh = false}) async {
    const String cacheKey = 'violations';
    if (!forcedRefresh && _isCacheValid(cacheKey)) {
      return _cache[cacheKey];
    }

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/mobile/violations'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
          'ngrok-skip-browser-warning': 'true',
        },
      ).timeout(const Duration(seconds: 20));

      if (response.statusCode == 200) {
        isOfflineNotifier.value = false;
        final decoded = jsonDecode(response.body);
        _cache[cacheKey] = decoded;
        _cacheExpiry[cacheKey] = DateTime.now().add(cacheDuration);
        _saveToPersistentCache(cacheKey, decoded);
        return decoded;
      } else {
        throw Exception('Failed to load violations');
      }
    } catch (e) {
      isOfflineNotifier.value = true;
      final cached = await getPersistentCache(cacheKey);
      if (cached != null) return cached;
      throw Exception('Walang internet connection at walang naka-save na data.');
    }
  }

  Future<dynamic> getCaseDetails(int id) async {
    final String cacheKey = 'case_$id';
    if (_isCacheValid(cacheKey)) {
      return _cache[cacheKey];
    }

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/mobile/violations/$id'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      ).timeout(const Duration(seconds: 20));

      if (response.statusCode == 200) {
        isOfflineNotifier.value = false;
        final decoded = jsonDecode(response.body);
        _cache[cacheKey] = decoded;
        _cacheExpiry[cacheKey] = DateTime.now().add(cacheDuration);
        _saveToPersistentCache(cacheKey, decoded);
        return decoded;
      } else {
        throw Exception('Failed to load case details');
      }
    } catch (e) {
      isOfflineNotifier.value = true;
      final cached = await getPersistentCache(cacheKey);
      if (cached != null) return cached;
      throw Exception('Walang internet connection at walang naka-save na data.');
    }
  }

  Future<dynamic> getStats({bool forcedRefresh = false}) async {
    const String cacheKey = 'stats';
    if (!forcedRefresh && _isCacheValid(cacheKey)) {
      return _cache[cacheKey];
    }

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/mobile/stats'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      ).timeout(const Duration(seconds: 20));

      if (response.statusCode == 200) {
        isOfflineNotifier.value = false;
        final decoded = jsonDecode(response.body);
        _cache[cacheKey] = decoded;
        _cacheExpiry[cacheKey] = DateTime.now().add(cacheDuration);
        _saveToPersistentCache(cacheKey, decoded);
        return decoded;
      } else {
        throw Exception('Failed to load stats');
      }
    } catch (e) {
      isOfflineNotifier.value = true;
      final cached = await getPersistentCache(cacheKey);
      if (cached != null) return cached;
      throw Exception('Walang internet connection at walang naka-save na data.');
    }
  }

  Future<int> getUnreadCount() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    final response = await http.get(
      Uri.parse('$baseUrl/mobile/notifications/unread-count'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body)['count'] ?? 0;
    }
    return 0;
  }

  Future<dynamic> getNotifications({bool forcedRefresh = false}) async {
    const String cacheKey = 'notifications';
    if (!forcedRefresh && _isCacheValid(cacheKey)) {
      return _cache[cacheKey];
    }

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/mobile/notifications'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      ).timeout(const Duration(seconds: 20));

      if (response.statusCode == 200) {
        isOfflineNotifier.value = false;
        final decoded = jsonDecode(response.body);
        _cache[cacheKey] = decoded;
        _cacheExpiry[cacheKey] = DateTime.now().add(cacheDuration);
        _saveToPersistentCache(cacheKey, decoded);
        return decoded;
      } else {
        throw Exception('Failed to load notifications');
      }
    } catch (e) {
      isOfflineNotifier.value = true;
      final cached = await getPersistentCache(cacheKey);
      if (cached != null) return cached;
      throw Exception('Walang internet connection at walang naka-save na data.');
    }
  }

  Future<void> markNotificationAsRead(String id) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    await http.post(
      Uri.parse('$baseUrl/mobile/notifications/$id/read'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );
  }
}
