import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../services/security_service.dart';
import '../widgets/app_ui.dart';
import 'login_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ApiService _apiService = ApiService();
  bool _isBiometricEnabled = false;
  bool _isHardwareAvailable = false;
  bool _isLoading = true;
  String _userName = '';
  String _userEmail = '';
  String _userRole = '';
  String _userDepartment = '';
  String _userInitials = 'D';

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final available = await SecurityService.isBiometricsSupported();
    final enabled = await SecurityService.isBiometricLockEnabled();
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString('user');

    if (userJson != null) {
      try {
        final user = jsonDecode(userJson) as Map<String, dynamic>;
        final name = (user['name'] ?? '').toString().trim();
        final email = (user['email'] ?? '').toString().trim();
        final role = (user['role'] ?? 'dean').toString();
        final dept = (user['department'] ?? '').toString().trim();
        final parts = name.split(' ').where((p) => p.isNotEmpty).toList();
        final initials = parts.length >= 2
            ? '${parts.first[0]}${parts.last[0]}'.toUpperCase()
            : (name.isNotEmpty ? name[0].toUpperCase() : 'D');

        if (mounted) {
          setState(() {
            _userName = name.isNotEmpty ? name : 'User';
            _userEmail = email;
            _userRole = role;
            _userDepartment = dept;
            _userInitials = initials;
            _isHardwareAvailable = available;
            _isBiometricEnabled = enabled;
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
        _isLoading = false;
      });
    }
  }

  Future<void> _toggleBiometric(bool value) async {
    if (value) {
      final ok = await SecurityService.authenticate();
      if (!ok) return;
    } else {
      await SecurityService.clearCredentials();
    }
    await SecurityService.setBiometricLock(value);
    if (mounted) setState(() => _isBiometricEnabled = value);
  }

  Future<void> _logout() async {
    HapticFeedback.mediumImpact();
    await _apiService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: AppTheme.bgLight,
        body: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
      );
    }

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: Column(
        children: [
          AppUi.gradientHeader(
            greeting: 'Your account',
            title: 'Profile',
            bottom: Row(
              children: [
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 3),
                    boxShadow: AppTheme.softShadow,
                  ),
                  child: Center(
                    child: Text(
                      _userInitials,
                      style: GoogleFonts.inter(
                        fontSize: 24,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.primary,
                      ),
                    ),
                  ),
                ),
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
                    ],
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(20),
              children: [
                _section('Account'),
                _tile(Icons.badge_outlined, 'Role', _userRole),
                if (_userDepartment.isNotEmpty)
                  _tile(Icons.apartment_outlined, 'Department', _userDepartment),
                const SizedBox(height: 16),
                _section('Security'),
                Container(
                  decoration: AppUi.cardDecoration(),
                  child: SwitchListTile(
                    title: Text('Biometric login', style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
                    subtitle: Text(
                      _isHardwareAvailable ? 'Fingerprint or Face ID' : 'Not available on this device',
                      style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMuted),
                    ),
                    value: _isBiometricEnabled,
                    onChanged: _isHardwareAvailable ? _toggleBiometric : null,
                    activeColor: AppTheme.primary,
                  ),
                ),
                const SizedBox(height: 24),
                SizedBox(
                  height: 50,
                  child: OutlinedButton.icon(
                    onPressed: _logout,
                    icon: const Icon(Icons.logout_rounded, color: AppTheme.accentRose, size: 20),
                    label: Text(
                      'Sign out',
                      style: GoogleFonts.inter(color: AppTheme.accentRose, fontWeight: FontWeight.w600),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(color: AppTheme.accentRose.withValues(alpha: 0.35)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  'VioTrack v2.1',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textHint),
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
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        title,
        style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.textMuted),
      ),
    );
  }

  Widget _tile(IconData icon, String label, String value) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: AppUi.cardDecoration(),
      child: ListTile(
        leading: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: AppTheme.primaryLight,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: AppTheme.primary, size: 20),
        ),
        title: Text(label, style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMuted)),
        subtitle: Text(value, style: GoogleFonts.inter(fontWeight: FontWeight.w600, color: AppTheme.textMain)),
      ),
    );
  }
}
