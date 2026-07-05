import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Persists the API auth token in the platform secure store (Keychain / Keystore).
class AuthStorageService {
  AuthStorageService._();

  static const _tokenKey = 'auth_token';
  static const _legacyTokenKey = 'token';

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static Future<void> migrateLegacyTokenIfNeeded() async {
    final prefs = await SharedPreferences.getInstance();
    final legacy = prefs.getString(_legacyTokenKey);
    if (legacy == null || legacy.isEmpty) return;

    final existing = await _storage.read(key: _tokenKey);
    if (existing == null || existing.isEmpty) {
      await _storage.write(key: _tokenKey, value: legacy);
    }
    await prefs.remove(_legacyTokenKey);
  }

  static Future<String?> getToken() async {
    await migrateLegacyTokenIfNeeded();
    return _storage.read(key: _tokenKey);
  }

  static Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_legacyTokenKey);
  }

  static Future<void> clearToken() async {
    await _storage.delete(key: _tokenKey);
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_legacyTokenKey);
  }

  static Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}
