import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../services/security_service.dart';
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
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Column(
              children: [
                CircleAvatar(
                  radius: 40,
                  backgroundColor: AppTheme.primary,
                  child: Text(
                    _userInitials,
                    style: GoogleFonts.inter(
                      fontSize: 28,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  _userName,
                  style: GoogleFonts.inter(fontSize: 20, fontWeight: FontWeight.w600, color: AppTheme.textMain),
                ),
                Text(
                  _userEmail,
                  style: GoogleFonts.inter(color: AppTheme.textMuted, fontSize: 14),
                ),
              ],
            ),
            const SizedBox(height: 28),
            _section('Account'),
            _tile(Icons.person_outline, 'Name', _userName),
            if (_userEmail.isNotEmpty) _tile(Icons.email_outlined, 'Email', _userEmail),
            _tile(Icons.badge_outlined, 'Role', _userRole),
            if (_userDepartment.isNotEmpty) _tile(Icons.apartment, 'Department', _userDepartment),
            const SizedBox(height: 20),
            _section('Security'),
            Card(
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: const BorderSide(color: AppTheme.inputBorder),
              ),
              child: SwitchListTile(
                title: Text('Biometric login', style: GoogleFonts.inter(fontWeight: FontWeight.w500)),
                subtitle: Text(
                  _isHardwareAvailable ? 'Fingerprint or Face ID' : 'Not available',
                  style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMuted),
                ),
                value: _isBiometricEnabled,
                onChanged: _isHardwareAvailable ? _toggleBiometric : null,
                activeColor: AppTheme.primary,
              ),
            ),
            const SizedBox(height: 24),
            OutlinedButton.icon(
              onPressed: _logout,
              icon: const Icon(Icons.logout, color: AppTheme.accentRose),
              label: Text('Sign out', style: GoogleFonts.inter(color: AppTheme.accentRose, fontWeight: FontWeight.w600)),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                side: BorderSide(color: AppTheme.accentRose.withValues(alpha: 0.4)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'VioTrack v2.0.0',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textHint),
            ),
          ],
        ),
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
    return Card(
      elevation: 0,
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: const BorderSide(color: AppTheme.inputBorder),
      ),
      child: ListTile(
        leading: Icon(icon, color: AppTheme.primary, size: 22),
        title: Text(label, style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMuted)),
        subtitle: Text(value, style: GoogleFonts.inter(fontWeight: FontWeight.w500, color: AppTheme.textMain)),
      ),
    );
  }
}
