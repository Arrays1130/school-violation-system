import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:local_auth/local_auth.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import 'webauthn_gate_stub.dart'
    if (dart.library.html) 'webauthn_gate_web.dart';

class SecurityService {
  static final LocalAuthentication _auth = LocalAuthentication();
  static const _storage = FlutterSecureStorage();
  static const String _biometricKey = 'biometric_enabled';
  static const String _webAuthnCredKey = 'webauthn_credential_id';

  static const String _keyEmail = 'user_email';
  static const String _keyPassword = 'user_password';

  /// Pag-save ng credentials nang secure (iOS Keychain / Android Keystore / web)
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
    await _clearWebAuthnCredentialId();
  }

  static Future<List<BiometricType>> getAvailableBiometrics() async {
    if (kIsWeb) return const [];
    try {
      return await _auth.getAvailableBiometrics();
    } catch (_) {
      return [];
    }
  }

  /// Human-readable label: Face ID (iPhone PWA/native), Fingerprint (Android), etc.
  static Future<String> getBiometricLabel() async {
    if (kIsWeb) return webAuthnPreferredLabel();

    final types = await getAvailableBiometrics();
    if (types.contains(BiometricType.face)) return 'Face ID';
    if (types.contains(BiometricType.fingerprint)) return 'Fingerprint';
    if (types.contains(BiometricType.iris)) return 'Iris scan';
    if (types.contains(BiometricType.strong) ||
        types.contains(BiometricType.weak)) {
      return 'Biometric unlock';
    }
    return 'Biometrics';
  }

  /// Icon matching the device biometric type (Face ID on modern iPhones).
  static Future<IconData> getBiometricIcon() async {
    final label = await getBiometricLabel();
    return iconForLabel(label);
  }

  static IconData iconForLabel(String label) {
    final lower = label.toLowerCase();
    if (lower.contains('face')) return Icons.face_rounded;
    if (lower.contains('iris')) return Icons.remove_red_eye_rounded;
    if (lower.contains('finger') || lower.contains('touch')) {
      return Icons.fingerprint_rounded;
    }
    return Icons.lock_rounded;
  }

  /// Pag-check kung may biometric hardware ang device (Fingerprint/FaceID)
  static Future<bool> canCheckBiometrics() async {
    if (kIsWeb) return webAuthnIsSupported();
    try {
      return await _auth.canCheckBiometrics;
    } catch (_) {
      return false;
    }
  }

  /// Pag-check kung supported ang system at may naka-enroll na biometrics
  static Future<bool> isBiometricsSupported() async {
    if (kIsWeb) return webAuthnIsSupported();
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

  /// Register Face ID / platform authenticator on PWA (or verify existing).
  /// Native apps skip registration and use local_auth directly.
  static Future<bool> ensurePlatformAuthenticator({
    required String email,
    required String displayName,
  }) async {
    if (!kIsWeb) {
      return authenticate(
        reason: 'Enable biometric login for your dean account.',
      );
    }

    final existing = await _getWebAuthnCredentialId();
    if (existing != null && existing.isNotEmpty) {
      return webAuthnAuthenticate(credentialIdBase64: existing);
    }

    final credentialId = await webAuthnRegister(
      email: email,
      displayName: displayName,
    );
    if (credentialId == null || credentialId.isEmpty) return false;

    await _setWebAuthnCredentialId(credentialId);
    return true;
  }

  /// Pag-authenticate gamit ang biometrics / Face ID (PWA WebAuthn)
  static Future<bool> authenticate({String? reason}) async {
    try {
      if (kIsWeb) {
        final credentialId = await _getWebAuthnCredentialId();
        if (credentialId == null || credentialId.isEmpty) return false;
        return webAuthnAuthenticate(credentialIdBase64: credentialId);
      }

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
    if (!enabled) {
      await _clearWebAuthnCredentialId();
    }
  }

  static Future<String?> _getWebAuthnCredentialId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_webAuthnCredKey);
  }

  static Future<void> _setWebAuthnCredentialId(String id) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_webAuthnCredKey, id);
  }

  static Future<void> _clearWebAuthnCredentialId() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_webAuthnCredKey);
  }
}
