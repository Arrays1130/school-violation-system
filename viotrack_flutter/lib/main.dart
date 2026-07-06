import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'screens/main_layout.dart';
import 'screens/login_screen.dart';
import 'theme/app_theme.dart';
import 'services/auth_storage_service.dart';
import 'services/security_service.dart';
import 'services/session_service.dart';
import 'services/push_bootstrap.dart';
import 'services/push_navigation_service.dart';
import 'api_service.dart';

void main() {
  runZonedGuarded(
    () async {
      WidgetsFlutterBinding.ensureInitialized();

      FlutterError.onError = (details) {
        FlutterError.presentError(details);
        debugPrint('FlutterError: ${details.exception}');
      };

      runApp(const ProviderScope(child: VioTrackApp()));

      // Non-blocking: lets VS Code / DDS attach before heavy startup work.
      unawaited(GoogleFonts.pendingFonts());
      unawaited(PushBootstrap.init());
    },
    (error, stack) {
      debugPrint('Uncaught error: $error\n$stack');
    },
  );
}

class VioTrackApp extends StatelessWidget {
  const VioTrackApp({super.key});

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
      ),
    );

    return MaterialApp(
      debugShowCheckedModeBanner: false,
      navigatorKey: PushNavigationService.navigatorKey,
      title: 'VioTrack',
      theme: AppTheme.lightTheme,
      home: const AuthWrapper(),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  State<AuthWrapper> createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  bool _isLoading = true;
  bool _isLoggedIn = false;
  bool _isLocked = false;
  bool _isUnlocking = false;
  bool _didAttemptAutoUnlock = false;
  String? _unlockMessage;
  String _biometricLabel = 'Fingerprint';

  @override
  void initState() {
    super.initState();
    SessionService.sessionExpired.addListener(_onSessionExpired);
    _checkStatus();
  }

  @override
  void dispose() {
    SessionService.sessionExpired.removeListener(_onSessionExpired);
    super.dispose();
  }

  void _onSessionExpired() {
    if (!SessionService.sessionExpired.value || !mounted) return;
    ApiService().logout().then((_) {
      if (!mounted) return;
      setState(() {
        _isLoggedIn = false;
        _isLocked = false;
        _isUnlocking = false;
        _didAttemptAutoUnlock = false;
        _unlockMessage = null;
      });
    });
  }

  Future<void> _checkStatus() async {
    try {
      final hasToken = await AuthStorageService.hasToken();
      final biometricEnabled = await SecurityService.isBiometricLockEnabled();
      final biometricLabel = await SecurityService.getBiometricLabel();

      if (!mounted) return;
      setState(() {
        _isLoggedIn = hasToken;
        _isLocked = _isLoggedIn && biometricEnabled;
        _isLoading = false;
        _unlockMessage = null;
        _biometricLabel = biometricLabel;
        if (!_isLocked) {
          _didAttemptAutoUnlock = false;
        }
      });

      if (_isLocked && !_didAttemptAutoUnlock) {
        _didAttemptAutoUnlock = true;
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (!mounted || !_isLocked) return;
          _authenticate(triggeredAutomatically: true);
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _authenticate({bool triggeredAutomatically = false}) async {
    if (_isUnlocking) return;
    if (mounted) {
      setState(() {
        _isUnlocking = true;
        if (!triggeredAutomatically) {
          _unlockMessage = null;
        }
      });
    }
    try {
      final ok = await SecurityService.authenticate();
      if (!mounted) return;
      setState(() {
        _isUnlocking = false;
        if (ok) {
          _isLocked = false;
          _unlockMessage = null;
        } else {
          _unlockMessage = triggeredAutomatically
              ? 'Biometric unlock was dismissed. Tap unlock to try again.'
              : 'Unlock was not completed. Try again to continue.';
        }
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _isUnlocking = false;
        _unlockMessage =
            'Biometric unlock is currently unavailable. Please try again.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: AppTheme.bgLight,
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: AppTheme.primaryLight,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.shield_outlined,
                  color: AppTheme.primary,
                  size: 32,
                ),
              ),
              const SizedBox(height: 16),
              const CircularProgressIndicator(color: AppTheme.primary),
            ],
          ),
        ),
      );
    }

    if (!_isLoggedIn) return const LoginScreen();

    if (_isLocked) {
      return Scaffold(
        backgroundColor: AppTheme.bgLight,
        body: SafeArea(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: Container(
                  padding: const EdgeInsets.all(28),
                  decoration: BoxDecoration(
                    gradient: AppTheme.heroGradient,
                    borderRadius: BorderRadius.circular(32),
                    border: Border.all(
                      color: Colors.white.withValues(alpha: 0.08),
                    ),
                    boxShadow: AppTheme.floatShadow,
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 96,
                        height: 96,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.14),
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.2),
                          ),
                        ),
                        child: const Icon(
                          Icons.fingerprint_rounded,
                          color: Colors.white,
                          size: 48,
                        ),
                      ),
                      const SizedBox(height: 22),
                      Text(
                        'App locked',
                        textAlign: TextAlign.center,
                        style: GoogleFonts.inter(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.w700,
                          letterSpacing: -0.4,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Use your $_biometricLabel to continue.',
                        textAlign: TextAlign.center,
                        style: GoogleFonts.inter(
                          color: Colors.white.withValues(alpha: 0.82),
                          fontSize: 14,
                          height: 1.4,
                        ),
                      ),
                      if (_unlockMessage != null) ...[
                        const SizedBox(height: 16),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: Colors.white.withValues(alpha: 0.12),
                            ),
                          ),
                          child: Text(
                            _unlockMessage!,
                            textAlign: TextAlign.center,
                            style: GoogleFonts.inter(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: _isUnlocking
                              ? null
                              : () => _authenticate(
                                  triggeredAutomatically: false,
                                ),
                          icon: _isUnlocking
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: AppTheme.primary,
                                  ),
                                )
                              : const Icon(Icons.fingerprint_rounded),
                          label: Text(
                            _isUnlocking
                                ? 'Verifying...'
                                : 'Unlock with $_biometricLabel',
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: AppTheme.primaryNavy,
                            minimumSize: const Size(double.infinity, 52),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      );
    }

    return const MainLayout();
  }
}
