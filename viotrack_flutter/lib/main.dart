import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/main_layout.dart';
import 'screens/login_screen.dart';
import 'theme/app_theme.dart';
import 'services/notification_service.dart';
import 'services/fcm_service.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart'; // Riverpod

import 'services/security_service.dart';
import 'config/api_config.dart';


void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // 1. Initialize Firebase
  // Note: Kailangan mo pang i-set up ang google-services.json para gumana ito
  try {
    if (!kIsWeb) {
      if (Firebase.apps.isEmpty) {
        await Firebase.initializeApp();
      }
      FirebaseMessaging.onBackgroundMessage(handleBackgroundMessage);
      await FCMService.initialize();
    } else {
      print("Running on Web: Skipping Firebase FCM init");
    }
  } catch (e) {
    print("Firebase init error: $e");
  }

  runApp(const ProviderScope(child: VioTrackApp())); // Wrapped with ProviderScope
}

class VioTrackApp extends StatelessWidget {
  const VioTrackApp({super.key});

  @override
  Widget build(BuildContext context) {
    SystemChrome.setSystemUIOverlayStyle(SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ));

    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'VioTrack Dean',
      theme: AppTheme.lightTheme.copyWith(
        pageTransitionsTheme: const PageTransitionsTheme(
          builders: {
            TargetPlatform.android: CupertinoPageTransitionsBuilder(),
            TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
          },
        ),
      ),
      builder: (context, child) {
        return Container(
          color: const Color(0xFFF0F2F5), // subtle dark gray background for web
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 500),
              child: ClipRRect(
                child: child!,
              ),
            ),
          ),
        );
      },
      home: const AuthWrapper(),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  const AuthWrapper({super.key});

  @override
  _AuthWrapperState createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  bool _isLoading = true;
  bool _isLoggedIn = false;
  bool _isLocked = false;

  @override
  void initState() {
    super.initState();
    _checkStatus();
    _initNotifications();
  }

  void _initNotifications() {
    NotificationService().connect(ApiConfig.wsHost, (msg) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(msg, style: GoogleFonts.outfit()),
            backgroundColor: AppTheme.accentCyan,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    });
  }

  Future<void> _checkStatus() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final biometricEnabled = await SecurityService.isBiometricLockEnabled();

    setState(() {
      _isLoggedIn = token != null;
      _isLocked = _isLoggedIn && biometricEnabled;
      _isLoading = false;
    });

    if (_isLocked) {
      _authenticate();
    }
  }

  Future<void> _authenticate() async {
    bool authenticated = await SecurityService.authenticate();
    if (authenticated) {
      if (mounted) setState(() => _isLocked = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: Colors.white,
        body: Center(
          child: CircularProgressIndicator(color: AppTheme.primaryNavy),
        ),
      );
    }

    if (!_isLoggedIn) {
      return LoginScreen();
    }

    if (_isLocked) {
      return Scaffold(
        backgroundColor: AppTheme.primaryNavy,
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.lock_outline_rounded, size: 80, color: Colors.white).animate().shake(delay: 500.ms),
              const SizedBox(height: 24),
              Text(
                "DASHBOARD LOCKED",
                style: GoogleFonts.outfit(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900, letterSpacing: 2),
              ),
              const SizedBox(height: 8),
              Text(
                "Please authenticate to proceed",
                style: GoogleFonts.outfit(color: Colors.white70, fontSize: 14),
              ),
              const SizedBox(height: 48),
              ElevatedButton.icon(
                onPressed: _authenticate,
                icon: const Icon(Icons.fingerprint_rounded),
                label: const Text("UNLOCK NOW"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.accentCyan,
                  padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return const MainLayout();
  }
}
