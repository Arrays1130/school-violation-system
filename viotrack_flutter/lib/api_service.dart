import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:viotrack_flutter/config/api_config.dart';

class ApiService {
  static const String baseUrl = ApiConfig.baseUrl;
  
  // Optimization: In-memory cache for speed
  final Map<String, dynamic> _cache = {};
  final Map<String, DateTime> _cacheExpiry = {};
  static const Duration cacheDuration = Duration(seconds: 25);

  bool _isCacheValid(String key) {
    if (!_cache.containsKey(key)) return false;
    return DateTime.now().isBefore(_cacheExpiry[key]!);
  }

  void _invalidateCache(String key) {
    _cache.remove(key);
    _cacheExpiry.remove(key);
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/mobile/login'),
        headers: {'Accept': 'application/json'},
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
        return {'success': false, 'message': 'Error Code: ${response.statusCode}'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Walang connection sa server'};
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

    final response = await http.get(
      Uri.parse('$baseUrl/mobile/violations'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      final decoded = jsonDecode(response.body);
      _cache[cacheKey] = decoded;
      _cacheExpiry[cacheKey] = DateTime.now().add(cacheDuration);
      return decoded;
    } else {
      throw Exception('Failed to load violations');
    }
  }

  Future<dynamic> getCaseDetails(int id) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    final response = await http.get(
      Uri.parse('$baseUrl/mobile/violations/$id'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to load case details');
    }
  }

  Future<dynamic> getStats({bool forcedRefresh = false}) async {
    const String cacheKey = 'stats';
    if (!forcedRefresh && _isCacheValid(cacheKey)) {
      return _cache[cacheKey];
    }

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    final response = await http.get(
      Uri.parse('$baseUrl/mobile/stats'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      final decoded = jsonDecode(response.body);
      _cache[cacheKey] = decoded;
      _cacheExpiry[cacheKey] = DateTime.now().add(cacheDuration);
      return decoded;
    } else {
      throw Exception('Failed to load stats');
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

  Future<dynamic> getNotifications() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    final response = await http.get(
      Uri.parse('$baseUrl/mobile/notifications'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to load notifications');
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
