import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class RoleHome {
  static Future<String> currentRole() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString('user');
    if (raw == null || raw.isEmpty) return 'dean';
    try {
      final user = jsonDecode(raw) as Map<String, dynamic>;
      return (user['role'] ?? 'dean').toString();
    } catch (_) {
      return 'dean';
    }
  }

  static bool isGso(String role) => role == 'gso';
}
