import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'screens/main_layout.dart';
import 'screens/login_screen.dart';
import 'theme/app_theme.dart';
import 'services/security_service.dart';
import 'services/session_service.dart';
import 'api_service.dart';

void main() {
  runZonedGuarded(() async {
    WidgetsFlutterBinding.ensureInitialized();
    await GoogleFonts.pendingFonts();

    FlutterError.onError = (details) {
      FlutterError.presentError(details);
      debugPrint('FlutterError: ${details.exception}');
    };

    runApp(const ProviderScope(child: VioTrackApp()));
  }, (error, stack) {
    debugPrint('Uncaught error: $error\n$stack');
  });
}

class VioTrackApp extends StatelessWidget {
  const VioTrackApp({super.key});

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ));

    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'VioTrack v2',
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
      });
    });
  }

  Future<void> _checkStatus() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final biometricEnabled = await SecurityService.isBiometricLockEnabled();

      if (!mounted) return;
      setState(() {
        _isLoggedIn = token != null;
        _isLocked = _isLoggedIn && biometricEnabled;
        _isLoading = false;
      });

      if (_isLocked) _authenticate();
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _authenticate() async {
    try {
      final ok = await SecurityService.authenticate();
      if (ok && mounted) setState(() => _isLocked = false);
    } catch (_) {}
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
                child: const Icon(Icons.shield_outlined, color: AppTheme.primary, size: 32),
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
        backgroundColor: AppTheme.primaryDark,
        body: SafeArea(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(32),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.fingerprint, size: 72, color: Colors.white),
                  const SizedBox(height: 24),
                  Text(
                    'App Locked',
                    style: GoogleFonts.inter(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Use fingerprint to unlock',
                    style: GoogleFonts.inter(color: Colors.white70),
                  ),
                  const SizedBox(height: 32),
                  ElevatedButton(
                    onPressed: _authenticate,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: AppTheme.primaryDark,
                      minimumSize: const Size(200, 48),
                    ),
                    child: const Text('Unlock'),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return const MainLayout();
  }
}
