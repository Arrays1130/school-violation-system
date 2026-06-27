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
  bool _hasAutoPromptedBiometric = false;

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
      
      final configured = isSupported && hasCredentials && isEnabled;

      if (mounted) {
        if (mounted) setState(() {
          _isBiometricConfigured = configured;
        });
        
        if (configured && !_hasAutoPromptedBiometric) {
          _hasAutoPromptedBiometric = true;
          WidgetsBinding.instance.addPostFrameCallback((_) {
            _biometricLogin();
          });
        }
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
    if (mounted) setState(() => _isAuthenticating = true);
    try {
      bool authenticated = await SecurityService.authenticate();
      if (authenticated) {
        HapticFeedback.mediumImpact();
        final credentials = await SecurityService.getCredentials();
        if (mounted &&
            credentials['email'] != null &&
            credentials['password'] != null) {
          if (mounted) setState(() {
            _isLoading = true;
            _isAuthenticating = false;
          });
          final result = await _apiService.login(
              credentials['email']!, credentials['password']!);
          if (mounted) {
            if (mounted) setState(() => _isLoading = false);
            if (result['success']) {
              FCMService.syncTokenWithBackend();
              Navigator.pushReplacement(context,
                  MaterialPageRoute(builder: (context) => const MainLayout()));
            } else {
              _showError("Session expired. Please log in manually.");
              if (mounted) setState(() => _currentStage = LoginStage.passwordForm);
            }
          }
        }
      } else {
        if (mounted) setState(() => _isAuthenticating = false);
      }
    } catch (e) {
      if (mounted) {
        if (mounted) setState(() => _isAuthenticating = false);
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
                    color: AppTheme.accentCyan)),
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
    if (mounted) setState(() => _isLoading = true);
    final result = await _apiService.login(email, password);
    if (mounted) setState(() => _isLoading = false);
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

  // â”€â”€â”€ Build â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        backgroundColor: const Color(0xFFFAFBFC),
        body: Stack(
          children: [
            // â”€â”€ Animated Background â”€â”€
            _buildBackground(size),

            // â”€â”€ Content â”€â”€
            SafeArea(
              child: Column(
                children: [
                  const SizedBox(height: 24),
                  _buildHeader(),
                  Expanded(
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 450),
                        child: Animate(
                          controller: _shakeController,
                          autoPlay: false,
                          effects: [
                            ShakeEffect(
                                curve: Curves.easeInOutCubic,
                                duration: 400.ms,
                                hz: 4),
                          ],
                          child: ScrollConfiguration(
                            behavior: ScrollConfiguration.of(context).copyWith(scrollbars: false),
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
          // Base background
          Container(
            color: const Color(0xFFFAFBFC), // Ultra clean, slightly cool white
          ),
          
          // Liquid Blob 1 (Cyan)
          Positioned(
            top: -size.height * 0.1,
            right: -size.width * 0.1,
            child: Container(
              width: size.width * 0.9,
              height: size.width * 0.9,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppTheme.primarySlate.withOpacity(0.25),
              ),
            ).animate(onPlay: (controller) => controller.repeat(reverse: true))
             .moveX(begin: -50, end: 50, duration: 8.seconds, curve: Curves.easeInOutSine)
             .moveY(begin: -30, end: 40, duration: 6.seconds, curve: Curves.easeInOutSine)
             .scale(begin: const Offset(1, 1), end: const Offset(1.1, 1.2), duration: 7.seconds, curve: Curves.easeInOutSine),
          ),
          
          // Liquid Blob 2 (Gold)
          Positioned(
            bottom: -size.height * 0.2,
            left: -size.width * 0.2,
            child: Container(
              width: size.width * 0.85,
              height: size.width * 0.85,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppTheme.accentGold.withOpacity(0.25),
              ),
            ).animate(onPlay: (controller) => controller.repeat(reverse: true))
             .moveX(begin: 40, end: -40, duration: 7.seconds, curve: Curves.easeInOutSine)
             .moveY(begin: 50, end: -30, duration: 9.seconds, curve: Curves.easeInOutSine)
             .scale(begin: const Offset(1, 1), end: const Offset(1.15, 1.05), duration: 6.seconds, curve: Curves.easeInOutSine),
          ),

          // Liquid Blob 3 (Subtle Navy)
          Positioned(
            top: size.height * 0.4,
            left: size.width * 0.1,
            child: Container(
              width: size.width * 0.7,
              height: size.width * 0.7,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: AppTheme.primaryNavy.withOpacity(0.15),
              ),
            ).animate(onPlay: (controller) => controller.repeat(reverse: true))
             .moveX(begin: -30, end: 60, duration: 9.seconds, curve: Curves.easeInOutSine)
             .moveY(begin: -40, end: 30, duration: 8.seconds, curve: Curves.easeInOutSine),
          ),

          // Massive Blur Overlay to create the "Liquid Mesh" blending effect
          Positioned.fill(
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 120, sigmaY: 120),
              child: const SizedBox(),
            ),
          ),

          // Subtle dot grid (decorative)
          Positioned.fill(
            child: Opacity(
              opacity: 0.5,
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
        // Logo badge (Clean Light)
        Container(
          width: 72,
          height: 72,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: AppTheme.primaryNavy.withOpacity(0.04), width: 1),
            boxShadow: [
              BoxShadow(color: const Color(0xFF8B9BB4).withOpacity(0.12), blurRadius: 24, offset: const Offset(0, 8)),
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
                  fontSize: 26, fontWeight: FontWeight.w900, color: AppTheme.primaryNavy, letterSpacing: 1),
            ),
            TextSpan(
              text: "CST",
              style: GoogleFonts.outfit(
                  fontSize: 26,
                  fontWeight: FontWeight.w900,
                  foreground: Paint()
                    ..shader = const LinearGradient(colors: [AppTheme.accentGold, AppTheme.accentAmber])
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
              color: AppTheme.textMuted,
              letterSpacing: 3),
        ).animate().fadeIn(delay: 300.ms),
      ],
    );
  }

  Widget _buildSelectionView() {
    return SingleChildScrollView(
      key: const ValueKey('selection'),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(32),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 32, sigmaY: 32),
          child: Container(
            padding: const EdgeInsets.fromLTRB(28, 44, 28, 44),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.55),
              borderRadius: BorderRadius.circular(32),
              border: Border.all(color: Colors.white.withOpacity(0.6), width: 1.5),
              boxShadow: [
                BoxShadow(
                    color: const Color(0xFF8B9BB4).withOpacity(0.2),
                    blurRadius: 40,
                    offset: const Offset(0, 15)),
              ],
            ),
            child: Column(
          children: [
            Text(
              "Welcome back, Dean",
              style: GoogleFonts.outfit(
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.primaryNavy),
            ).animate().fadeIn(delay: 100.ms),
            const SizedBox(height: 8),
            Text(
              "Choose how you'd like to access the system",
              style: GoogleFonts.outfit(
                  fontSize: 14, color: AppTheme.textMuted),
              textAlign: TextAlign.center,
            ).animate().fadeIn(delay: 200.ms),
            const SizedBox(height: 40),

            // Biometric option
            _buildCleanOption(
              icon: Icons.fingerprint_rounded,
              title: "Biometric Login",
              subtitle: "Fingerprint or Face ID",
              onTap: _biometricLogin,
              isPrimary: true,
              isLocked: !_isBiometricConfigured,
              isLoading: _isAuthenticating,
              delay: 300,
            ),
            const SizedBox(height: 16),
            // Password option
            _buildCleanOption(
              icon: Icons.password_rounded,
              title: "Email & Password",
              subtitle: "Manual credential entry",
              onTap: () => if (mounted) setState(() => _currentStage = LoginStage.passwordForm),
              isPrimary: false,
              delay: 400,
            ),
          ],
        ),
      ),
        ),
      ),
    );
  }

  Widget _buildCleanOption({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    bool isPrimary = false,
    bool isLocked = false,
    bool isLoading = false,
    int delay = 0,
  }) {
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: () {
        HapticFeedback.lightImpact();
        onTap();
      },
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: isPrimary ? AppTheme.bgLight.withOpacity(0.8) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
              color: isPrimary ? AppTheme.primaryNavy.withOpacity(0.08) : AppTheme.inputBorder.withOpacity(0.5)),
        ),
        child: Row(
          children: [
            Container(
              width: 52,
              height: 52,
              decoration: BoxDecoration(
                color: isPrimary ? AppTheme.primaryNavy : AppTheme.bgLight,
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
                  : Icon(icon, color: isPrimary ? Colors.white : AppTheme.primaryNavy, size: 26),
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
                          color: AppTheme.textMain)),
                  const SizedBox(height: 3),
                  Text(subtitle,
                      style: GoogleFonts.outfit(
                          fontSize: 12,
                          color: AppTheme.textMuted)),
                ],
              ),
            ),
            if (isLocked)
              Icon(Icons.lock_outline_rounded,
                  color: AppTheme.textMuted.withOpacity(0.5), size: 18)
            else
              Icon(Icons.arrow_forward_ios_rounded,
                  color: AppTheme.textMuted.withOpacity(0.5), size: 14),
          ],
        ),
      ),
    ).animate().fadeIn(delay: Duration(milliseconds: delay)).slideX(begin: 0.05);
  }

  Widget _buildPasswordView() {
    return SingleChildScrollView(
      key: const ValueKey('password'),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(32),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 32, sigmaY: 32),
          child: Container(
            padding: const EdgeInsets.fromLTRB(32, 32, 32, 40),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.55),
              borderRadius: BorderRadius.circular(32),
              border: Border.all(color: Colors.white.withOpacity(0.6), width: 1.5),
              boxShadow: [
                BoxShadow(
                    color: const Color(0xFF8B9BB4).withOpacity(0.2),
                    blurRadius: 40,
                    offset: const Offset(0, 15)),
              ],
            ),
            child: Column(
          children: [
            // Back button row
            Align(
              alignment: Alignment.centerLeft,
              child: GestureDetector(
                behavior: HitTestBehavior.opaque,
                onTap: () => if (mounted) setState(() => _currentStage = LoginStage.selection),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: AppTheme.bgLight,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.arrow_back_ios_new_rounded,
                          size: 14, color: AppTheme.textMuted),
                      const SizedBox(width: 6),
                      Text("Back",
                          style: GoogleFonts.outfit(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: AppTheme.textMuted)),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 32),

            // Title
            Text(
              "Sign In",
              style: GoogleFonts.outfit(
                  fontSize: 28,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.primaryNavy),
            ),
            const SizedBox(height: 6),
            Text(
              "Enter your academic credentials",
              style: GoogleFonts.outfit(
                  fontSize: 14, color: AppTheme.textMuted),
            ),
            const SizedBox(height: 40),

            // Email
            _buildLabel("EMAIL ADDRESS"),
            const SizedBox(height: 8),
            _buildCleanTextField(
              controller: _emailController,
              hintText: "dean@college.edu.ph",
              icon: Icons.alternate_email_rounded,
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 24),

            // Password
            _buildLabel("PASSWORD"),
            const SizedBox(height: 8),
            _buildCleanTextField(
              controller: _passwordController,
              hintText: "â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢",
              icon: Icons.lock_outline_rounded,
              obscureText: _obscurePassword,
              onToggleObscure: () => if (mounted) setState(() => _obscurePassword = !_obscurePassword),
            ),
            const SizedBox(height: 36),

            // Sign-in button
            SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _login,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryNavy,
                  foregroundColor: Colors.white,
                  disabledBackgroundColor: AppTheme.primaryNavy.withOpacity(0.5),
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16)),
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 24,
                        width: 24,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2.5))
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text("ACCESS DASHBOARD",
                              style: GoogleFonts.outfit(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.2)),
                          const SizedBox(width: 10),
                          const Icon(Icons.arrow_forward_rounded, size: 18),
                        ],
                      ),
              ),
            ),
            const SizedBox(height: 24),
            TextButton(
              onPressed: () {
                showDialog(
                  context: context,
                  builder: (context) => AlertDialog(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                    title: Text("Forgot Password?",
                        style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: AppTheme.primaryNavy)),
                    content: Text(
                        "For security reasons, password resets must be requested through the IT Helpdesk. Please contact them to reset your account credentials.",
                        style: GoogleFonts.outfit(color: AppTheme.textMain)),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(context),
                            child: Text("UNDERSTOOD",
                                style: GoogleFonts.outfit(
                                    fontWeight: FontWeight.bold,
                                    color: AppTheme.primarySlate)),
                      ),
                    ],
                  ),
                );
              },
              child: Text("FORGOT PASSWORD?",
                  style: GoogleFonts.outfit(
                      fontSize: 12,
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

  Widget _buildCleanTextField({
    required TextEditingController controller,
    required String hintText,
    required IconData icon,
    bool obscureText = false,
    TextInputType keyboardType = TextInputType.text,
    VoidCallback? onToggleObscure,
  }) {
    return TextField(
      controller: controller,
      obscureText: obscureText,
      keyboardType: keyboardType,
      style: GoogleFonts.outfit(fontSize: 15, color: AppTheme.textMain, fontWeight: FontWeight.w500),
      decoration: InputDecoration(
        hintText: hintText,
        hintStyle: GoogleFonts.outfit(color: AppTheme.textHint),
        filled: true,
        fillColor: AppTheme.bgLight.withOpacity(0.7),
        prefixIcon: Icon(icon, size: 20, color: AppTheme.textMuted),
        suffixIcon: onToggleObscure != null
            ? IconButton(
                onPressed: onToggleObscure,
                icon: Icon(
                  obscureText ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                  size: 20,
                  color: AppTheme.textMuted,
                ),
              )
            : null,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Colors.transparent),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppTheme.primarySlate, width: 1.5),
        ),
        contentPadding: const EdgeInsets.symmetric(vertical: 18, horizontal: 20),
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Text(
        text,
        style: GoogleFonts.outfit(
            fontSize: 11,
            fontWeight: FontWeight.w800,
            color: AppTheme.textMuted,
            letterSpacing: 1.5),
      ),
    );
  }

  Widget _buildFooter() {
    return Text(
      "Â© 2026 CST DEAN'S PORTAL  â€¢  SECURE ACCESS",
      style: GoogleFonts.outfit(
          fontSize: 9,
          fontWeight: FontWeight.w600,
          color: AppTheme.textMuted.withOpacity(0.5),
          letterSpacing: 1.5),
    );
  }
}

// â”€â”€â”€ Dot Grid Painter â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

class _DotGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = AppTheme.primaryNavy.withOpacity(0.04)
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
