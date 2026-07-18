import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../services/security_service.dart';
import '../widgets/app_ui.dart';
import '../widgets/skeleton_loader.dart';
import '../utils/page_transitions.dart';
import 'login_screen.dart';
import 'policy_lookup_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ApiService _apiService = ApiService();
  bool _isBiometricEnabled = false;
  bool _isHardwareAvailable = false;
  bool _isTogglingBiometric = false;
  String _biometricLabel = 'Biometrics';
  bool _isLoading = true;
  String _userName = '';
  String _userEmail = '';
  String _userRole = '';
  String _userDepartment = '';

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final available = await SecurityService.isBiometricsSupported();
    final enabled = await SecurityService.isBiometricLockEnabled();
    final label = await SecurityService.getBiometricLabel();
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString('user');

    if (userJson != null) {
      try {
        final user = jsonDecode(userJson) as Map<String, dynamic>;
        final name = (user['name'] ?? '').toString().trim();
        final email = (user['email'] ?? '').toString().trim();
        final role = (user['role'] ?? 'dean').toString();
        final dept = (user['department'] ?? '').toString().trim();

        if (mounted) {
          setState(() {
            _userName = name.isNotEmpty ? name : 'User';
            _userEmail = email;
            _userRole = role;
            _userDepartment = dept;
            _isHardwareAvailable = available;
            _isBiometricEnabled = enabled;
            _biometricLabel = label;
            _isLoading = false;
          });
        }
        return;
      } catch (_) {}
    }

    if (mounted) {
      setState(() {
        _isHardwareAvailable = available;
        _isBiometricEnabled = enabled;
        _biometricLabel = label;
        _isLoading = false;
      });
    }
  }

  Future<bool> _promptPasswordForBiometric() async {
    final controller = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Confirm your password',
          style: GoogleFonts.inter(fontWeight: FontWeight.w700),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Enter your password once to enable $_biometricLabel login.',
              style: GoogleFonts.inter(
                fontSize: 13,
                color: AppTheme.textMuted,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: controller,
              obscureText: true,
              autofocus: true,
              decoration: const InputDecoration(
                labelText: 'Password',
                prefixIcon: Icon(Icons.lock_outline_rounded),
              ),
              onSubmitted: (_) => Navigator.pop(ctx, true),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Continue'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return false;

    final password = controller.text;
    controller.dispose();
    if (password.isEmpty) {
      _showSnack('Please enter your password.', success: false);
      return false;
    }

    final email = _userEmail;
    if (email.isEmpty) {
      _showSnack('Account email not found. Sign in again.', success: false);
      return false;
    }

    await SecurityService.saveCredentials(email, password);
    return true;
  }

  void _showSnack(String message, {required bool success}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, style: GoogleFonts.inter()),
        backgroundColor: success ? AppTheme.accentEmerald : AppTheme.accentRose,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  Future<void> _testFingerprint() async {
    if (!_isBiometricEnabled) return;
    HapticFeedback.lightImpact();
    final ok = await SecurityService.authenticate(
      reason: 'Verify your $_biometricLabel is working.',
    );
    if (!mounted) return;
    _showSnack(
      ok ? '$_biometricLabel verified successfully.' : 'Verification cancelled.',
      success: ok,
    );
  }

  Future<void> _toggleBiometric(bool value) async {
    if (_isTogglingBiometric) return;

    setState(() => _isTogglingBiometric = true);
    try {
      if (value) {
        if (!await SecurityService.hasStoredCredentials()) {
          final saved = await _promptPasswordForBiometric();
          if (!saved) return;
        }

        final ok = await SecurityService.ensurePlatformAuthenticator(
          email: _userEmail,
          displayName: _userName,
        );
        if (!ok) {
          _showSnack(
            '$_biometricLabel setup failed. On iPhone, use Add to Home Screen and try again.',
            success: false,
          );
          return;
        }
      } else {
        await SecurityService.clearCredentials();
      }

      await SecurityService.setBiometricLock(value);
      if (mounted) {
        setState(() => _isBiometricEnabled = value);
        _showSnack(
          value
              ? '$_biometricLabel login enabled.'
              : '$_biometricLabel login disabled.',
          success: true,
        );
      }
    } finally {
      if (mounted) setState(() => _isTogglingBiometric = false);
    }
  }

  Future<void> _logout() async {
    HapticFeedback.mediumImpact();
    await _apiService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      AppPageTransitions.fadeScale(const LoginScreen()),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: AppTheme.bgLight,
        body: Column(
          children: [
            Container(
              height: 220,
              decoration: const BoxDecoration(
                gradient: AppTheme.heroGradient,
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  ShimmerLoader.rounded(height: 72, width: double.infinity),
                  const SizedBox(height: 12),
                  ShimmerLoader.rounded(height: 72, width: double.infinity),
                  const SizedBox(height: 12),
                  ShimmerLoader.rounded(height: 56, width: double.infinity),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: Column(
        children: [
          AppUi.gradientHeader(
            greeting: 'Your account',
            title: 'Profile',
            subtitle: 'Identity, security, and sign-out.',
            badge: AppUi.iconCircle(
              icon: Icons.person_outline_rounded,
              color: AppTheme.primaryNavy,
              size: 36,
              iconSize: 18,
              backgroundColor: Colors.white,
            ),
            bottom: Row(
              children: [
                AppUi.initialsAvatar(_userName, size: 64, radius: 20),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _userName,
                        style: GoogleFonts.inter(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                      if (_userEmail.isNotEmpty)
                        Text(
                          _userEmail,
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: Colors.white.withValues(alpha: 0.85),
                          ),
                        ),
                      const SizedBox(height: 8),
                      AppUi.brandPill(
                        label: _userRole.toUpperCase(),
                        leading: Icon(
                          Icons.verified_user_outlined,
                          size: 14,
                          color: Colors.white.withValues(alpha: 0.92),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            watermark: AppUi.ilinkWatermark(),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(
                20,
                20,
                20,
                AppTheme.bottomNavClearance,
              ),
              children: [
                AppUi.staggerIn(_section('Account'), 0),
                AppUi.staggerIn(
                  _tile(Icons.badge_outlined, 'Role', _userRole),
                  1,
                ),
                if (_userDepartment.isNotEmpty)
                  AppUi.staggerIn(
                    _tile(
                      Icons.apartment_outlined,
                      'Department',
                      _userDepartment,
                    ),
                    2,
                  ),
                const SizedBox(height: 16),
                AppUi.staggerIn(_section('Security'), 3),
                AppUi.staggerIn(
                  AppUi.surfaceCard(
                  padding: const EdgeInsets.all(16),
                  clip: true,
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Material(
                            color: Colors.transparent,
                            child: InkWell(
                              onTap: _isHardwareAvailable && _isBiometricEnabled
                                  ? _testFingerprint
                                  : null,
                              borderRadius: BorderRadius.circular(16),
                              child: Ink(
                                width: 56,
                                height: 56,
                                decoration: BoxDecoration(
                                  gradient: _isBiometricEnabled
                                      ? AppTheme.heroGradient
                                      : null,
                                  color: _isBiometricEnabled
                                      ? null
                                      : AppTheme.bgLight,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(
                                    color: _isBiometricEnabled
                                        ? Colors.transparent
                                        : AppTheme.inputBorder,
                                  ),
                                ),
                                child: Icon(
                                  SecurityService.iconForLabel(_biometricLabel),
                                  size: 30,
                                  color: _isBiometricEnabled
                                      ? Colors.white
                                      : AppTheme.textMuted,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  '$_biometricLabel login',
                                  style: GoogleFonts.inter(
                                    fontWeight: FontWeight.w700,
                                    fontSize: 15,
                                    color: AppTheme.textMain,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  _isHardwareAvailable
                                      ? (_isBiometricEnabled
                                          ? 'Tap icon to test · unlock app faster'
                                          : (kIsWeb
                                              ? 'Add to Home Screen, then enable $_biometricLabel'
                                              : 'Sign in and unlock with $_biometricLabel'))
                                      : (kIsWeb
                                          ? 'Use HTTPS + Add to Home Screen for Face ID'
                                          : 'Not available on this device'),
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    color: AppTheme.textMuted,
                                    height: 1.35,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (_isTogglingBiometric)
                            const SizedBox(
                              width: 24,
                              height: 24,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          else
                            Switch.adaptive(
                              value: _isBiometricEnabled,
                              onChanged:
                                  _isHardwareAvailable ? _toggleBiometric : null,
                              activeColor: AppTheme.primary,
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
                4,
                ),
                const SizedBox(height: 24),
                AppUi.staggerIn(
                  SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.push(
                        context,
                        AppPageTransitions.fadeScale(const PolicyLookupScreen()),
                      );
                    },
                    icon: const Icon(Icons.auto_awesome_outlined, size: 20),
                    label: Text('Policy Lookup (Nexus AI)', style: GoogleFonts.inter(fontWeight: FontWeight.w700)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.primary,
                      side: BorderSide(color: AppTheme.primary.withValues(alpha: 0.35)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                  ),
                  5,
                ),
                const SizedBox(height: 12),
                AppUi.staggerIn(
                  SizedBox(
                  height: 50,
                  child: OutlinedButton.icon(
                    onPressed: _logout,
                    icon: const Icon(
                      Icons.logout_rounded,
                      color: AppTheme.accentRose,
                      size: 20,
                    ),
                    label: Text(
                      'Sign out',
                      style: GoogleFonts.inter(
                        color: AppTheme.accentRose,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(
                        color: AppTheme.accentRose.withValues(alpha: 0.35),
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                  ),
                ),
                5,
                ),
                const SizedBox(height: 20),
                AppUi.staggerIn(
                  Text(
                  '${AppTheme.appName} v${AppTheme.appVersion} · I-LINK Dean Portal',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: AppTheme.textHint,
                  ),
                ),
                6,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _section(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10, top: 4),
      child: Text(
        title.toUpperCase(),
        style: GoogleFonts.inter(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: AppTheme.textHint,
          letterSpacing: 0.8,
        ),
      ),
    );
  }

  Widget _tile(IconData icon, String label, String value) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: AppUi.cardDecoration(),
      clipBehavior: Clip.antiAlias,
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: AppTheme.primaryLight,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: AppTheme.primary, size: 20),
        ),
        title: Text(
          label,
          style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMuted),
        ),
        subtitle: Text(
          value,
          style: GoogleFonts.inter(
            fontWeight: FontWeight.w600,
            color: AppTheme.textMain,
          ),
        ),
      ),
    );
  }
}
