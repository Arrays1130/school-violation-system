// ignore_for_file: avoid_web_libraries_in_flutter, deprecated_member_use

import 'dart:convert';
import 'dart:html' as html;
import 'dart:js_util' as js_util;
import 'dart:typed_data';

import 'package:flutter/foundation.dart';

Future<bool> webAuthnIsSupported() async {
  try {
    if (html.window.isSecureContext != true) return false;
    if (js_util.getProperty(html.window, 'PublicKeyCredential') == null) {
      return false;
    }

    final publicKeyCredential = js_util.getProperty(
      html.window,
      'PublicKeyCredential',
    );
    if (!js_util.hasProperty(
      publicKeyCredential,
      'isUserVerifyingPlatformAuthenticatorAvailable',
    )) {
      return true;
    }

    final promise = js_util.callMethod(
      publicKeyCredential,
      'isUserVerifyingPlatformAuthenticatorAvailable',
      [],
    );
    final result = await js_util.promiseToFuture(promise);
    return result == true;
  } catch (e) {
    debugPrint('WebAuthn support check failed: $e');
    return false;
  }
}

String webAuthnPreferredLabel() {
  final ua = html.window.navigator.userAgent.toLowerCase();
  if (ua.contains('iphone') || ua.contains('ipad') || ua.contains('ipod')) {
    return 'Face ID';
  }
  if (ua.contains('android')) return 'Fingerprint';
  if (ua.contains('mac os') || ua.contains('macintosh')) return 'Touch ID';
  return 'Biometrics';
}

Future<String?> webAuthnRegister({
  required String email,
  required String displayName,
}) async {
  try {
    final credentials = html.window.navigator.credentials;
    if (credentials == null) return null;

    final challenge = html.window.crypto!.getRandomValues(Uint8List(32));
    final userId = Uint8List.fromList(utf8.encode(email));

    final publicKey = js_util.newObject();
    js_util.setProperty(publicKey, 'challenge', challenge);
    js_util.setProperty(publicKey, 'timeout', 60000);

    final rp = js_util.newObject();
    js_util.setProperty(rp, 'name', 'VioTrack');
    js_util.setProperty(rp, 'id', Uri.base.host);
    js_util.setProperty(publicKey, 'rp', rp);

    final user = js_util.newObject();
    js_util.setProperty(user, 'id', userId);
    js_util.setProperty(user, 'name', email);
    js_util.setProperty(
      user,
      'displayName',
      displayName.isEmpty ? email : displayName,
    );
    js_util.setProperty(publicKey, 'user', user);

    js_util.setProperty(
      publicKey,
      'pubKeyCredParams',
      js_util.jsify([
        {'type': 'public-key', 'alg': -7},
        {'type': 'public-key', 'alg': -257},
      ]),
    );

    final selection = js_util.newObject();
    js_util.setProperty(selection, 'authenticatorAttachment', 'platform');
    js_util.setProperty(selection, 'userVerification', 'required');
    js_util.setProperty(selection, 'residentKey', 'preferred');
    js_util.setProperty(publicKey, 'authenticatorSelection', selection);

    final options = js_util.newObject();
    js_util.setProperty(options, 'publicKey', publicKey);

    final cred = await js_util.promiseToFuture(
      js_util.callMethod(credentials, 'create', [options]),
    );
    if (cred == null) return null;

    final rawId = js_util.getProperty(cred, 'rawId');
    if (rawId == null) return null;

    return _bytesToBase64Url(_asUint8List(rawId));
  } catch (e) {
    debugPrint('WebAuthn register failed: $e');
    return null;
  }
}

Future<bool> webAuthnAuthenticate({required String credentialIdBase64}) async {
  try {
    final credentials = html.window.navigator.credentials;
    if (credentials == null) return false;

    final challenge = html.window.crypto!.getRandomValues(Uint8List(32));
    final credId = _base64UrlToBytes(credentialIdBase64);

    final publicKey = js_util.newObject();
    js_util.setProperty(publicKey, 'challenge', challenge);
    js_util.setProperty(publicKey, 'timeout', 60000);
    js_util.setProperty(publicKey, 'rpId', Uri.base.host);
    js_util.setProperty(publicKey, 'userVerification', 'required');

    final item = js_util.newObject();
    js_util.setProperty(item, 'type', 'public-key');
    js_util.setProperty(item, 'id', credId);
    js_util.setProperty(item, 'transports', js_util.jsify(['internal']));

    final arr = js_util.callMethod(html.window, 'Array', []);
    js_util.callMethod(arr, 'push', [item]);
    js_util.setProperty(publicKey, 'allowCredentials', arr);

    final options = js_util.newObject();
    js_util.setProperty(options, 'publicKey', publicKey);

    final cred = await js_util.promiseToFuture(
      js_util.callMethod(credentials, 'get', [options]),
    );
    return cred != null;
  } catch (e) {
    debugPrint('WebAuthn authenticate failed: $e');
    return false;
  }
}

Uint8List _asUint8List(Object rawId) {
  if (rawId is ByteBuffer) {
    return Uint8List.view(rawId);
  }
  if (rawId is TypedData) {
    return Uint8List.view(
      rawId.buffer,
      rawId.offsetInBytes,
      rawId.lengthInBytes,
    );
  }
  final view = js_util.callConstructor(
    js_util.getProperty(html.window, 'Uint8Array'),
    [rawId],
  );
  final length = js_util.getProperty(view, 'length') as int;
  final out = Uint8List(length);
  for (var i = 0; i < length; i++) {
    out[i] = js_util.getProperty(view, i) as int;
  }
  return out;
}

String _bytesToBase64Url(Uint8List bytes) {
  return base64Url.encode(bytes).replaceAll('=', '');
}

Uint8List _base64UrlToBytes(String input) {
  var output = input.replaceAll('-', '+').replaceAll('_', '/');
  while (output.length % 4 != 0) {
    output += '=';
  }
  return Uint8List.fromList(base64Decode(output));
}
