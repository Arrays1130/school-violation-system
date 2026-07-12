import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/app_ui.dart';
import '../services/security_service.dart';
import '../services/push_bootstrap.dart';
import '../services/fcm_service.dart';
import '../utils/page_transitions.dart';
import 'main_layout.dart';

/// GCash-style login: dark hero, saved account pill, dual login card.
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _apiService = ApiService();
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _canBiometric = false;
  bool _biometricSupported = false;
  String _biometricLabel = 'Fingerprint';
  String? _errorMessage;
  String? _savedEmail;
  bool _showPasswordForm = false;

  @override
  void initState() {
    super.initState();
    _checkBiometric();
  }

  Future<void> _checkBiometric() async {
    try {
      final supported = await SecurityService.isBiometricsSupported();
      final label = await SecurityService.getBiometricLabel();
      final creds = await SecurityService.getCredentials();
      final enabled = await SecurityService.isBiometricLockEnabled();
      final canUse =
          supported &&
          enabled &&
          creds['email'] != null &&
          creds['password'] != null;

      if (mounted) {
        setState(() {
          _biometricSupported = supported;
          _biometricLabel = label;
          _canBiometric = canUse;
          _savedEmail = creds['email'];
          if (_savedEmail != null && _savedEmail!.isNotEmpty) {
            _emailController.text = _savedEmail!;
          }
        });
      }
    } catch (_) {}
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _openPasswordForm() {
    HapticFeedback.selectionClick();
    setState(() {
      _showPasswordForm = true;
      _errorMessage = null;
    });
  }

  void _closePasswordForm() {
    HapticFeedback.selectionClick();
    setState(() {
      _showPasswordForm = false;
      _errorMessage = null;
    });
  }

  void _switchAccount() {
    HapticFeedback.selectionClick();
    setState(() {
      _savedEmail = null;
      _emailController.clear();
      _passwordController.clear();
      _showPasswordForm = true;
      _errorMessage = null;
    });
  }

  Future<void> _login({String? email, String? password}) async {
    final e = email ?? _emailController.text.trim();
    final p = password ?? _passwordController.text;

    if (e.isEmpty || p.isEmpty) {
      setState(() => _errorMessage = 'Please enter your email and password');
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await _apiService.login(e, p);
      if (!mounted) return;

      if (result['success'] == true) {
        HapticFeedback.lightImpact();
        await SecurityService.saveCredentials(e, p);
        if (PushBootstrap.isInitialized) {
          await FCMService.syncTokenWithBackend();
        }
        if (!mounted) return;
        Navigator.pushReplacement(
          context,
          AppPageTransitions.fadeScale(const MainLayout()),
        );
      } else {
        setState(() {
          _isLoading = false;
          _errorMessage = result['message']?.toString() ?? 'Login failed';
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage =
              'Connection error. Check your internet and try again.';
        });
      }
    }
  }

  Future<void> _biometricLogin() async {
    if (!_canBiometric) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            _biometricSupported
                ? 'Sign in with password first, then enable $_biometricLabel in Profile.'
                : 'Biometric login is not available on this device.',
            style: GoogleFonts.inter(),
          ),
          behavior: SnackBarBehavior.floating,
          backgroundColor: AppTheme.primaryNavy,
        ),
      );
      _openPasswordForm();
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final ok = await SecurityService.authenticate(
        reason: 'Sign in to VioTrack with your $_biometricLabel',
      );
      if (!ok) {
        if (mounted) setState(() => _isLoading = false);
        return;
      }
      final creds = await SecurityService.getCredentials();
      if (creds['email'] != null && creds['password'] != null) {
        await _login(email: creds['email'], password: creds['password']);
      } else if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'No saved account. Sign in with password first.';
        });
        _openPasswordForm();
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  String _maskEmail(String email) {
    final parts = email.split('@');
    if (parts.length != 2) return email;
    final name = parts[0];
    if (name.length <= 2) return email;
    final masked = '${name.substring(0, 2)}${'*' * (name.length - 2)}';
    return '$masked@${parts[1]}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        fit: StackFit.expand,
        children: [
          const _LoginBackground(),
          SafeArea(
            child: _showPasswordForm ? _buildPasswordView() : _buildHomeView(),
          ),
        ],
      ),
    );
  }

  // ─── GCash-style home: logo + account pill + dual login card ───

  Widget _buildHomeView() {
    return Column(
      children: [
        const SizedBox(height: 28),
        _buildLogo(),
        const SizedBox(height: 28),
        if (_savedEmail != null && _savedEmail!.isNotEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: _buildAccountPill(),
          ),
        const Spacer(flex: 2),
        if (_isLoading)
          const Padding(
            padding: EdgeInsets.only(bottom: 24),
            child: CircularProgressIndicator(color: Colors.white),
          )
        else
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 28),
            child: _buildDualLoginCard(),
          ),
        const Spacer(flex: 3),
        _buildFooter(),
        const SizedBox(height: 16),
      ],
    );
  }

  Widget _buildLogo() {
    return Column(
      children: [
        Container(
          width: 72,
          height: 72,
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: Colors.white,
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: AppTheme.accentCyan.withValues(alpha: 0.45),
                blurRadius: 32,
                offset: const Offset(0, 8),
              ),
              BoxShadow(
                color: AppTheme.primary.withValues(alpha: 0.25),
                blurRadius: 16,
                spreadRadius: 2,
              ),
            ],
          ),
          child: Image.asset(AppUi.ilinkLogoAsset, fit: BoxFit.contain),
        ),
        const SizedBox(height: 14),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'Vio',
              style: GoogleFonts.inter(
                fontSize: 28,
                fontWeight: FontWeight.w800,
                color: Colors.white,
                letterSpacing: -0.6,
              ),
            ),
            Text(
              'Track',
              style: GoogleFonts.inter(
                fontSize: 28,
                fontWeight: FontWeight.w800,
                color: AppTheme.accentCyan,
                letterSpacing: -0.6,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        Text(
          'DEAN PORTAL',
          style: GoogleFonts.inter(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: Colors.white.withValues(alpha: 0.55),
            letterSpacing: 2.2,
          ),
        ),
      ],
    ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.06, end: 0);
  }

  Widget _buildAccountPill() {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: _switchAccount,
        borderRadius: BorderRadius.circular(999),
        child: Ink(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          decoration: BoxDecoration(
            color: AppTheme.primary,
            borderRadius: BorderRadius.circular(999),
            boxShadow: [
              BoxShadow(
                color: AppTheme.primary.withValues(alpha: 0.4),
                blurRadius: 16,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Flexible(
                child: Text(
                  _maskEmail(_savedEmail!),
                  style: GoogleFonts.inter(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                    letterSpacing: 0.2,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 12),
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.swap_horiz_rounded,
                  color: Colors.white,
                  size: 18,
                ),
              ),
            ],
          ),
        ),
      ),
    ).animate().fadeIn(delay: 100.ms, duration: 350.ms);
  }

  Widget _buildDualLoginCard() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.25),
            blurRadius: 32,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: IntrinsicHeight(
        child: Row(
          children: [
            Expanded(
              child: _LoginOptionTile(
                icon: Icons.fingerprint_rounded,
                label: 'Biometrics\nLogin',
                onTap: _biometricLogin,
              ),
            ),
            Container(width: 1, color: const Color(0xFFE8ECF0)),
            Expanded(
              child: _LoginOptionTile(
                icon: Icons.dialpad_rounded,
                label: 'Password\nLogin',
                onTap: _openPasswordForm,
              ),
            ),
          ],
        ),
      ),
    ).animate().fadeIn(delay: 150.ms, duration: 400.ms).slideY(begin: 0.08, end: 0);
  }

  Widget _buildFooter() {
    return Column(
      children: [
        TextButton(
          onPressed: () {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(
                  'Contact your system administrator for account help.',
                  style: GoogleFonts.inter(),
                ),
                behavior: SnackBarBehavior.floating,
                backgroundColor: AppTheme.primaryNavy,
              ),
            );
          },
          child: Text(
            'Need help signing in?',
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.white.withValues(alpha: 0.85),
              decoration: TextDecoration.underline,
              decorationColor: Colors.white.withValues(alpha: 0.5),
            ),
          ),
        ),
        Text(
          '${AppTheme.appName} v${AppTheme.appVersion}',
          style: GoogleFonts.inter(
            fontSize: 11,
            color: Colors.white.withValues(alpha: 0.35),
          ),
        ),
      ],
    );
  }

  // ─── Password form (full screen overlay style) ───

  Widget _buildPasswordView() {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(24, 12, 24, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Align(
            alignment: Alignment.centerLeft,
            child: IconButton(
              onPressed: _isLoading ? null : _closePasswordForm,
              icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
              style: IconButton.styleFrom(
                backgroundColor: Colors.white.withValues(alpha: 0.1),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Password Login',
            style: GoogleFonts.inter(
              fontSize: 26,
              fontWeight: FontWeight.w700,
              color: Colors.white,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Enter your official dean account credentials',
            style: GoogleFonts.inter(
              fontSize: 14,
              color: Colors.white.withValues(alpha: 0.6),
            ),
          ),
          const SizedBox(height: 28),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.2),
                  blurRadius: 24,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  'Email Address',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textSub,
                  ),
                ),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  autocorrect: false,
                  style: GoogleFonts.inter(fontSize: 14),
                  decoration: _fieldDecoration(
                    hint: 'name@ilinkcollege.edu.ph',
                    prefixIcon: Icons.mail_outline_rounded,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Password',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textSub,
                  ),
                ),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  style: GoogleFonts.inter(fontSize: 14),
                  decoration: _fieldDecoration(
                    hint: 'Password',
                    prefixIcon: Icons.lock_outline_rounded,
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword
                            ? Icons.visibility_off_outlined
                            : Icons.visibility_outlined,
                        color: AppTheme.textMuted,
                        size: 20,
                      ),
                      onPressed: () =>
                          setState(() => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                  onFieldSubmitted: (_) => _login(),
                ),
                if (_errorMessage != null) ...[
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppTheme.accentRose.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: AppTheme.accentRose.withValues(alpha: 0.25),
                      ),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.error_outline,
                          color: AppTheme.accentRose,
                          size: 18,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            _errorMessage!,
                            style: GoogleFonts.inter(
                              color: AppTheme.accentRose,
                              fontSize: 13,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
                const SizedBox(height: 20),
                SizedBox(
                  height: 52,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _login,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      textStyle: GoogleFonts.inter(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    child: _isLoading
                        ? const SizedBox(
                            height: 22,
                            width: 22,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text('Sign in'),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              TextButton(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                        'Contact your system administrator to reset your password.',
                        style: GoogleFonts.inter(),
                      ),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                },
                child: Text(
                  'Forgot password?',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Colors.white.withValues(alpha: 0.85),
                  ),
                ),
              ),
              if (_canBiometric)
                TextButton.icon(
                  onPressed: _isLoading
                      ? null
                      : () {
                          _closePasswordForm();
                          _biometricLogin();
                        },
                  icon: const Icon(Icons.fingerprint_rounded, size: 18),
                  label: Text(_biometricLabel),
                  style: TextButton.styleFrom(
                    foregroundColor: AppTheme.accentCyan,
                    textStyle: GoogleFonts.inter(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    ).animate().fadeIn(duration: 300.ms).slideX(begin: 0.04, end: 0);
  }

  InputDecoration _fieldDecoration({
    required String hint,
    required IconData prefixIcon,
    Widget? suffixIcon,
  }) {
    return InputDecoration(
      hintText: hint,
      hintStyle: GoogleFonts.inter(color: AppTheme.textHint, fontSize: 14),
      filled: true,
      fillColor: AppTheme.bgLight,
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      prefixIcon: Icon(prefixIcon, color: AppTheme.primary, size: 20),
      suffixIcon: suffixIcon,
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: AppTheme.inputBorder.withValues(alpha: 0.9)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppTheme.primary, width: 1.5),
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: AppTheme.inputBorder.withValues(alpha: 0.9)),
      ),
    );
  }
}

/// Rich blue login backdrop — gradient, soft glow orbs, and bottom waves.
class _LoginBackground extends StatelessWidget {
  const _LoginBackground();

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.sizeOf(context);

    return Stack(
      fit: StackFit.expand,
      children: [
        // Base: deep navy → royal blue → midnight (friendly fintech feel)
        const DecoratedBox(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                Color(0xFF0C1929),
                Color(0xFF1E40AF),
                Color(0xFF2563EB),
                Color(0xFF0F172A),
              ],
              stops: [0.0, 0.38, 0.62, 1.0],
            ),
          ),
        ),

        // Cyan glow — top right
        Positioned(
          top: -size.height * 0.08,
          right: -size.width * 0.18,
          child: Container(
            width: size.width * 0.75,
            height: size.width * 0.75,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  const Color(0xFF38BDF8).withValues(alpha: 0.28),
                  const Color(0xFF2563EB).withValues(alpha: 0.08),
                  Colors.transparent,
                ],
                stops: const [0.0, 0.45, 1.0],
              ),
            ),
          ),
        ),

        // Indigo glow — center left
        Positioned(
          top: size.height * 0.22,
          left: -size.width * 0.35,
          child: Container(
            width: size.width * 0.85,
            height: size.width * 0.85,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  const Color(0xFF818CF8).withValues(alpha: 0.18),
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),

        // Soft highlight behind logo area
        Positioned(
          top: size.height * 0.06,
          left: size.width * 0.5 - 90,
          child: Container(
            width: 180,
            height: 180,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  Colors.white.withValues(alpha: 0.12),
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),

        // Bottom wave layers
        Positioned(
          left: 0,
          right: 0,
          bottom: 0,
          height: size.height * 0.38,
          child: const CustomPaint(painter: _LoginWavePainter()),
        ),

        // Subtle dot grid for depth (very faint)
        const CustomPaint(painter: _LoginDotGridPainter()),

        // Top vignette for status-bar legibility
        Positioned(
          top: 0,
          left: 0,
          right: 0,
          height: 120,
          child: DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.black.withValues(alpha: 0.22),
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}

/// Layered translucent waves at the bottom of the login screen.
class _LoginWavePainter extends CustomPainter {
  const _LoginWavePainter();

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    // Back wave — lighter blue
    final wave1 = Path()
      ..moveTo(0, h * 0.55)
      ..quadraticBezierTo(w * 0.25, h * 0.38, w * 0.5, h * 0.5)
      ..quadraticBezierTo(w * 0.78, h * 0.62, w, h * 0.45)
      ..lineTo(w, h)
      ..lineTo(0, h)
      ..close();
    canvas.drawPath(
      wave1,
      Paint()..color = const Color(0xFF3B82F6).withValues(alpha: 0.22),
    );

    // Mid wave
    final wave2 = Path()
      ..moveTo(0, h * 0.72)
      ..quadraticBezierTo(w * 0.35, h * 0.58, w * 0.65, h * 0.7)
      ..quadraticBezierTo(w * 0.88, h * 0.8, w, h * 0.65)
      ..lineTo(w, h)
      ..lineTo(0, h)
      ..close();
    canvas.drawPath(
      wave2,
      Paint()..color = const Color(0xFF1D4ED8).withValues(alpha: 0.35),
    );

    // Front wave — deepest
    final wave3 = Path()
      ..moveTo(0, h * 0.88)
      ..quadraticBezierTo(w * 0.4, h * 0.78, w * 0.7, h * 0.86)
      ..quadraticBezierTo(w * 0.92, h * 0.92, w, h * 0.82)
      ..lineTo(w, h)
      ..lineTo(0, h)
      ..close();
    canvas.drawPath(
      wave3,
      Paint()..color = const Color(0xFF0F172A).withValues(alpha: 0.55),
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

/// Faint dot pattern — adds texture without hurting readability.
class _LoginDotGridPainter extends CustomPainter {
  const _LoginDotGridPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withValues(alpha: 0.04)
      ..strokeWidth = 1.2
      ..strokeCap = StrokeCap.round;

    const spacing = 28.0;
    for (double x = spacing; x < size.width; x += spacing) {
      for (double y = spacing; y < size.height; y += spacing) {
        canvas.drawCircle(Offset(x, y), 1.0, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _LoginOptionTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _LoginOptionTile({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          HapticFeedback.mediumImpact();
          onTap();
        },
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 12),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 44, color: AppTheme.primary),
              const SizedBox(height: 14),
              Text(
                label,
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.primary,
                  height: 1.25,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
