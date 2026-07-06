import 'package:flutter/foundation.dart';
import 'package:local_auth/local_auth.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecurityService {
  static final LocalAuthentication _auth = LocalAuthentication();
  static const _storage = FlutterSecureStorage();
  static const String _biometricKey = 'biometric_enabled';

  static const String _keyEmail = 'user_email';
  static const String _keyPassword = 'user_password';

  /// Pag-save ng credentials nang secure (iOS Keychain / Android Keystore)
  static Future<void> saveCredentials(String email, String password) async {
    await _storage.write(key: _keyEmail, value: email);
    await _storage.write(key: _keyPassword, value: password);
  }

  /// Pagkuha ng saved credentials
  static Future<Map<String, String?>> getCredentials() async {
    final email = await _storage.read(key: _keyEmail);
    final password = await _storage.read(key: _keyPassword);
    return {'email': email, 'password': password};
  }

  static Future<bool> hasStoredCredentials() async {
    final creds = await getCredentials();
    return (creds['email'] ?? '').toString().isNotEmpty &&
        (creds['password'] ?? '').toString().isNotEmpty;
  }

  /// Pag-delete ng credentials (halimbawa pag in-off ang biometric login)
  static Future<void> clearCredentials() async {
    await _storage.delete(key: _keyEmail);
    await _storage.delete(key: _keyPassword);
  }

  static Future<List<BiometricType>> getAvailableBiometrics() async {
    try {
      return await _auth.getAvailableBiometrics();
    } catch (_) {
      return [];
    }
  }

  /// Human-readable label: Fingerprint, Face ID, etc.
  static Future<String> getBiometricLabel() async {
    final types = await getAvailableBiometrics();
    if (types.contains(BiometricType.face)) return 'Face ID';
    if (types.contains(BiometricType.fingerprint)) return 'Fingerprint';
    if (types.contains(BiometricType.iris)) return 'Iris scan';
    if (types.contains(BiometricType.strong) ||
        types.contains(BiometricType.weak)) {
      return 'Biometric unlock';
    }
    return 'Fingerprint or Face ID';
  }

  /// Pag-check kung may biometric hardware ang device (Fingerprint/FaceID)
  static Future<bool> canCheckBiometrics() async {
    try {
      return await _auth.canCheckBiometrics;
    } catch (_) {
      return false;
    }
  }

  /// Pag-check kung supported ang system at may naka-enroll na biometrics
  static Future<bool> isBiometricsSupported() async {
    try {
      final deviceSupported = await _auth.isDeviceSupported();
      if (!deviceSupported) return false;

      final canCheck = await _auth.canCheckBiometrics;
      if (!canCheck) return deviceSupported;

      final enrolled = await getAvailableBiometrics();
      return enrolled.isNotEmpty || deviceSupported;
    } catch (_) {
      return false;
    }
  }

  /// Pag-authenticate gamit ang biometrics
  static Future<bool> authenticate({String? reason}) async {
    try {
      final label = await getBiometricLabel();
      final result = await _auth.authenticate(
        localizedReason: reason ??
            'Use your $label to access the I-LINK dean portal.',
        options: const AuthenticationOptions(
          stickyAuth: true,
          biometricOnly: true,
          useErrorDialogs: true,
          sensitiveTransaction: true,
        ),
      );
      return result;
    } catch (e) {
      debugPrint('Auth error: $e');
      return false;
    }
  }

  /// Kunin ang preference ng user kung naka-enable ang biometric lock
  static Future<bool> isBiometricLockEnabled() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_biometricKey) ?? false;
  }

  /// I-save ang preference ng user para sa biometric lock
  static Future<void> setBiometricLock(bool enabled) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_biometricKey, enabled);
  }
}
