Future<bool> webAuthnIsSupported() async => false;

String webAuthnPreferredLabel() => 'Biometrics';

Future<String?> webAuthnRegister({
  required String email,
  required String displayName,
}) async =>
    null;

Future<bool> webAuthnAuthenticate({required String credentialIdBase64}) async =>
    false;
