import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';

import 'package:flutter/services.dart';
import 'dart:ui';
import '../api_service.dart';
import '../theme/app_theme.dart';
import 'main_layout.dart';
import '../services/security_service.dart';
import '../services/fcm_service.dart';

enum LoginStage { selection, passwordForm }

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _apiService = ApiService();
  late AnimationController _shakeController;

  LoginStage _currentStage = LoginStage.selection;
  bool _isLoading = false;
  bool _isAuthenticating = false;
  bool _obscurePassword = true;
  bool _isBiometricConfigured = false;

  @override
  void initState() {
    super.initState();
    _shakeController = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 400));
    _checkStatus();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _shakeController.dispose();
    super.dispose();
  }

  Future<void> _checkStatus() async {
    try {
      final bool isSupported = await SecurityService.isBiometricsSupported();
      final credentials = await SecurityService.getCredentials();
      final bool hasCredentials =
          credentials['email'] != null && credentials['password'] != null;
      final bool isEnabled = await SecurityService.isBiometricLockEnabled();
      if (mounted) {
        setState(() {
          _isBiometricConfigured = isSupported && hasCredentials && isEnabled;
        });
      }
    } catch (e) {
      debugPrint("Status check error: $e");
    }
  }

  Future<void> _biometricLogin() async {
    if (_isAuthenticating || _isLoading) return;
    if (!_isBiometricConfigured) {
      _showLinkDeviceDialog();
      return;
    }
    setState(() => _isAuthenticating = true);
    try {
      bool authenticated = await SecurityService.authenticate();
      if (authenticated) {
        HapticFeedback.mediumImpact();
        final credentials = await SecurityService.getCredentials();
        if (mounted &&
            credentials['email'] != null &&
            credentials['password'] != null) {
          setState(() {
            _isLoading = true;
            _isAuthenticating = false;
          });
          final result = await _apiService.login(
              credentials['email']!, credentials['password']!);
          if (mounted) {
            setState(() => _isLoading = false);
            if (result['success']) {
              FCMService.syncTokenWithBackend();
              Navigator.pushReplacement(context,
                  MaterialPageRoute(builder: (context) => const MainLayout()));
            } else {
              _showError("Session expired. Please log in manually.");
              setState(() => _currentStage = LoginStage.passwordForm);
            }
          }
        }
      } else {
        if (mounted) setState(() => _isAuthenticating = false);
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isAuthenticating = false);
        _showError("Biometric error. Please use password.");
      }
    }
  }

  void _showLinkDeviceDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text("Link Device",
            style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
        content: Text(
            "To use biometric login, log in with email & password first, then enable 'Biometric Login' in your profile settings.",
            style: GoogleFonts.outfit()),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text("GOT IT",
                style: GoogleFonts.outfit(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentPurple)),
          ),
        ],
      ),
    );
  }

  void _showError(String message) {
    HapticFeedback.vibrate();
    _shakeController.forward(from: 0.0);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Row(children: [
        const Icon(Icons.error_outline_rounded, color: Colors.white, size: 18),
        const SizedBox(width: 10),
        Expanded(child: Text(message, style: GoogleFonts.outfit())),
      ]),
      backgroundColor: AppTheme.accentRose,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      margin: const EdgeInsets.all(16),
    ));
  }

  void _login() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text;
    if (email.isEmpty || password.isEmpty) {
      _showError("Please fill in all fields");
      return;
    }
    setState(() => _isLoading = true);
    final result = await _apiService.login(email, password);
    setState(() => _isLoading = false);
    if (result['success']) {
      final bool isBiometricEnabled =
          await SecurityService.isBiometricLockEnabled();
      if (isBiometricEnabled) {
        await SecurityService.saveCredentials(email, password);
      }
      FCMService.syncTokenWithBackend();
      if (mounted) {
        Navigator.pushReplacement(context,
            MaterialPageRoute(builder: (context) => const MainLayout()));
      }
    } else {
      _showError(result['message']);
    }
  }

  // ─── Build ────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        backgroundColor: AppTheme.primaryNavy,
        body: Stack(
          children: [
            // ── Animated Background ──
            _buildBackground(size),

            // ── Content ──
            SafeArea(
              child: Column(
                children: [
                  const SizedBox(height: 24),
                  _buildHeader(),
                  Expanded(
                    child: Animate(
                      controller: _shakeController,
                      autoPlay: false,
                      effects: [
                        ShakeEffect(
                            curve: Curves.easeInOutCubic,
                            duration: 400.ms,
                            hz: 4),
                      ],
                      child: AnimatedSwitcher(
                        duration: 350.ms,
                        transitionBuilder: (child, anim) => SlideTransition(
                          position: anim.drive(
                            Tween(
                                    begin: const Offset(0.15, 0),
                                    end: Offset.zero)
                                .chain(CurveTween(
                                    curve: Curves.easeOutCubic)),
                          ),
                          child: FadeTransition(
                              opacity: anim, child: child),
                        ),
                        child: _currentStage == LoginStage.selection
                            ? _buildSelectionView()
                            : _buildPasswordView(),
                      ),
                    ),
                  ),
                  _buildFooter(),
                  const SizedBox(height: 24),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBackground(Size size) {
    return SizedBox.expand(
      child: Stack(
        children: [
          // Base gradient
          Container(
            decoration: const BoxDecoration(gradient: AppTheme.heroGradient),
          ),
          // Glow orb top-right
          Positioned(
            top: -80,
            right: -80,
            child: Container(
              width: 300,
              height: 300,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(colors: [
                  AppTheme.accentPurple.withOpacity(0.5),
                  Colors.transparent,
                ]),
              ),
            ),
          ),
          // Glow orb bottom-left
          Positioned(
            bottom: -100,
            left: -60,
            child: Container(
              width: 280,
              height: 280,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(colors: [
                  AppTheme.accentCyan.withOpacity(0.3),
                  Colors.transparent,
                ]),
              ),
            ),
          ),
          // Subtle dot grid (decorative)
          Positioned.fill(
            child: Opacity(
              opacity: 0.03,
              child: CustomPaint(painter: _DotGridPainter()),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Column(
      children: [
        // Logo badge
        Container(
          width: 72,
          height: 72,
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.08),
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: Colors.white.withOpacity(0.15), width: 1.5),
            boxShadow: [
              BoxShadow(color: AppTheme.accentPurple.withOpacity(0.4), blurRadius: 24, offset: const Offset(0, 8)),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(22),
            child: Image.asset('assets/images/logo.png', fit: BoxFit.contain),
          ),
        ).animate().scale(duration: 600.ms, curve: Curves.easeOutBack).fadeIn(),
        const SizedBox(height: 16),
        RichText(
          text: TextSpan(children: [
            TextSpan(
              text: "I-LINK ",
              style: GoogleFonts.outfit(
                  fontSize: 26, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: 1),
            ),
            TextSpan(
              text: "CST",
              style: GoogleFonts.outfit(
                  fontSize: 26,
                  fontWeight: FontWeight.w900,
                  foreground: Paint()
                    ..shader = const LinearGradient(colors: [Color(0xFFA78BFA), Color(0xFF06B6D4)])
                        .createShader(const Rect.fromLTWH(0, 0, 80, 30))),
            ),
          ]),
        ).animate().fadeIn(delay: 200.ms).slideY(begin: 0.2),
        const SizedBox(height: 6),
        Text(
          "ACADEMIC LEADERS PORTAL",
          style: GoogleFonts.outfit(
              fontSize: 9,
              fontWeight: FontWeight.w700,
              color: Colors.white.withOpacity(0.5),
              letterSpacing: 3),
        ).animate().fadeIn(delay: 300.ms),
      ],
    );
  }

  Widget _buildSelectionView() {
    return SingleChildScrollView(
      key: const ValueKey('selection'),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
      child: Column(
        children: [
          Text(
            "Welcome back, Dean",
            style: GoogleFonts.outfit(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                color: Colors.white),
          ).animate().fadeIn(delay: 100.ms),
          const SizedBox(height: 6),
          Text(
            "Choose how you'd like to access the system",
            style: GoogleFonts.outfit(
                fontSize: 13, color: Colors.white.withOpacity(0.55)),
            textAlign: TextAlign.center,
          ).animate().fadeIn(delay: 200.ms),
          const SizedBox(height: 36),

          // Biometric card
          _buildGlassCard(
            icon: Icons.fingerprint_rounded,
            title: "Biometric Login",
            subtitle: "Fingerprint or Face ID",
            gradient: AppTheme.accentGradient,
            onTap: _biometricLogin,
            isLocked: !_isBiometricConfigured,
            isLoading: _isAuthenticating,
            delay: 300,
          ),

          const SizedBox(height: 16),

          // Password card
          _buildGlassCard(
            icon: Icons.password_rounded,
            title: "Email & Password",
            subtitle: "Manual credential entry",
            gradient: const LinearGradient(
              colors: [Color(0xFF1E2D3D), Color(0xFF334155)],
            ),
            onTap: () =>
                setState(() => _currentStage = LoginStage.passwordForm),
            delay: 400,
          ),
        ],
      ),
    );
  }

  Widget _buildGlassCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required LinearGradient gradient,
    required VoidCallback onTap,
    bool isLocked = false,
    bool isLoading = false,
    int delay = 0,
  }) {
    return GestureDetector(
      onTap: () {
        HapticFeedback.lightImpact();
        onTap();
      },
      child: ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: gradient,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: Colors.white.withOpacity(0.12)),
              boxShadow: [
                BoxShadow(
                    color: Colors.black.withOpacity(0.2),
                    blurRadius: 20,
                    offset: const Offset(0, 8)),
              ],
            ),
            child: Row(
              children: [
                Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: isLoading
                      ? const Center(
                          child: SizedBox(
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(
                                color: Colors.white, strokeWidth: 2),
                          ),
                        )
                      : Icon(icon, color: Colors.white, size: 26),
                ),
                const SizedBox(width: 18),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title,
                          style: GoogleFonts.outfit(
                              fontSize: 15,
                              fontWeight: FontWeight.w800,
                              color: Colors.white)),
                      const SizedBox(height: 3),
                      Text(subtitle,
                          style: GoogleFonts.outfit(
                              fontSize: 12,
                              color: Colors.white.withOpacity(0.6))),
                    ],
                  ),
                ),
                if (isLocked)
                  Icon(Icons.lock_outline_rounded,
                      color: Colors.white.withOpacity(0.5), size: 18)
                else
                  Icon(Icons.arrow_forward_ios_rounded,
                      color: Colors.white.withOpacity(0.6), size: 14),
              ],
            ),
          ),
        ),
      ),
    ).animate().fadeIn(delay: Duration(milliseconds: delay)).slideX(begin: 0.08);
  }

  Widget _buildPasswordView() {
    return SingleChildScrollView(
      key: const ValueKey('password'),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(32),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
          child: Container(
            padding: const EdgeInsets.fromLTRB(28, 28, 28, 36),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.95),
              borderRadius: BorderRadius.circular(32),
              border: Border.all(color: Colors.white.withOpacity(0.6)),
              boxShadow: [
                BoxShadow(
                    color: AppTheme.primaryNavy.withOpacity(0.25),
                    blurRadius: 40,
                    offset: const Offset(0, 20)),
              ],
            ),
            child: Column(
              children: [
                // Back button row
                Align(
                  alignment: Alignment.centerLeft,
                  child: GestureDetector(
                    onTap: () =>
                        setState(() => _currentStage = LoginStage.selection),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppTheme.bgLight,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.arrow_back_ios_new_rounded,
                              size: 12, color: AppTheme.textMuted),
                          const SizedBox(width: 4),
                          Text("Back",
                              style: GoogleFonts.outfit(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: AppTheme.textMuted)),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 28),

                // Title
                Text(
                  "Sign In",
                  style: GoogleFonts.outfit(
                      fontSize: 28,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.textMain),
                ),
                const SizedBox(height: 6),
                Text(
                  "Enter your academic credentials",
                  style: GoogleFonts.outfit(
                      fontSize: 13, color: AppTheme.textMuted),
                ),
                const SizedBox(height: 32),

                // Email
                _buildLabel("EMAIL ADDRESS"),
                const SizedBox(height: 8),
                TextField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  style: GoogleFonts.outfit(
                      fontSize: 14, color: AppTheme.textMain),
                  decoration: const InputDecoration(
                    hintText: "dean@college.edu.ph",
                    prefixIcon:
                        Icon(Icons.alternate_email_rounded, size: 20),
                  ),
                ),
                const SizedBox(height: 20),

                // Password
                _buildLabel("PASSWORD"),
                const SizedBox(height: 8),
                TextField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  style: GoogleFonts.outfit(
                      fontSize: 14, color: AppTheme.textMain),
                  decoration: InputDecoration(
                    hintText: "••••••••",
                    prefixIcon:
                        const Icon(Icons.lock_outline_rounded, size: 20),
                    suffixIcon: IconButton(
                      onPressed: () => setState(
                          () => _obscurePassword = !_obscurePassword),
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_off_outlined
                            : Icons.visibility_outlined,
                        size: 20,
                        color: AppTheme.textMuted,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 28),

                // Sign-in button
                SizedBox(
                  width: double.infinity,
                  height: 54,
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: _isLoading
                          ? null
                          : AppTheme.accentGradient,
                      color: _isLoading ? AppTheme.textHint : null,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: _isLoading
                          ? null
                          : [
                              BoxShadow(
                                  color: AppTheme.accentPurple
                                      .withOpacity(0.4),
                                  blurRadius: 20,
                                  offset: const Offset(0, 8)),
                            ],
                    ),
                    child: ElevatedButton(
                      onPressed: _isLoading ? null : _login,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        disabledBackgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16)),
                      ),
                      child: _isLoading
                          ? const SizedBox(
                              height: 22,
                              width: 22,
                              child: CircularProgressIndicator(
                                  color: Colors.white, strokeWidth: 2.5))
                          : Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text("ACCESS DASHBOARD",
                                    style: GoogleFonts.outfit(
                                        fontSize: 14,
                                        fontWeight: FontWeight.w800,
                                        letterSpacing: 1,
                                        color: Colors.white)),
                                const SizedBox(width: 10),
                                const Icon(Icons.arrow_forward_rounded,
                                    size: 18, color: Colors.white),
                              ],
                            ),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                TextButton(
                  onPressed: () {},
                  child: Text("FORGOT PASSWORD?",
                      style: GoogleFonts.outfit(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textMuted,
                          letterSpacing: 1)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Text(
        text,
        style: GoogleFonts.outfit(
            fontSize: 10,
            fontWeight: FontWeight.w900,
            color: AppTheme.textMuted,
            letterSpacing: 1.5),
      ),
    );
  }

  Widget _buildFooter() {
    return Text(
      "© 2026 CST DEAN'S PORTAL  •  SECURE ACCESS",
      style: GoogleFonts.outfit(
          fontSize: 9,
          fontWeight: FontWeight.w600,
          color: Colors.white.withOpacity(0.3),
          letterSpacing: 1.5),
    );
  }
}

// ─── Dot Grid Painter ────────────────────────────────────────────────────────

class _DotGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white
      ..strokeWidth = 1.5;
    const double spacing = 28;
    for (double x = 0; x < size.width; x += spacing) {
      for (double y = 0; y < size.height; y += spacing) {
        canvas.drawCircle(Offset(x, y), 1.5, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
