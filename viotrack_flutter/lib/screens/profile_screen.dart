import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter/services.dart';
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

  // User data
  String _userName = '';
  String _userEmail = '';
  String _userRole = '';
  String _userInitials = '';

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final bool available = await SecurityService.isBiometricsSupported();
    final bool enabled = await SecurityService.isBiometricLockEnabled();

    // Load real user data from SharedPreferences
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString('user');
    if (userJson != null) {
      try {
        final user = jsonDecode(userJson) as Map<String, dynamic>;
        final name = (user['name'] ?? user['full_name'] ?? '').toString().trim();
        final email = (user['email'] ?? '').toString().trim();
        final role = (user['role'] ?? user['position'] ?? 'Dean of Discipline').toString().trim();

        // Build initials from name
        final parts = name.split(' ').where((p) => p.isNotEmpty).toList();
        final initials = parts.length >= 2
            ? '${parts.first[0]}${parts.last[0]}'.toUpperCase()
            : name.isNotEmpty
                ? name[0].toUpperCase()
                : 'D';

        setState(() {
          _userName = name.isNotEmpty ? name : 'Dean';
          _userEmail = email;
          _userRole = _formatRole(role);
          _userInitials = initials;
          _isHardwareAvailable = available;
          _isBiometricEnabled = enabled;
          _isLoading = false;
        });
        return;
      } catch (_) {}
    }

    setState(() {
      _userName = 'Dean';
      _userEmail = '';
      _userRole = 'Dean of Discipline';
      _userInitials = 'D';
      _isHardwareAvailable = available;
      _isBiometricEnabled = enabled;
      _isLoading = false;
    });
  }

  String _formatRole(String role) {
    if (role.toLowerCase().contains('dean')) return 'Dean of Discipline';
    if (role.toLowerCase().contains('admin')) return 'Administrator';
    if (role.toLowerCase().contains('registrar')) return 'Registrar';
    return role.isNotEmpty ? role : 'Dean of Discipline';
  }

  void _toggleBiometric(bool value) async {
    if (value) {
      bool authenticated = await SecurityService.authenticate();
      if (!authenticated) {
        _showSnackBar("Authentication failed. Cannot enable biometric lock.");
        return;
      }
    }
    if (!value) await SecurityService.clearCredentials();
    await SecurityService.setBiometricLock(value);
    setState(() => _isBiometricEnabled = value);
    _showSnackBar(
        value ? "Biometric login enabled ✓" : "Biometric login disabled");
  }

  void _showSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(message, style: GoogleFonts.outfit()),
      behavior: SnackBarBehavior.floating,
      backgroundColor: AppTheme.accentCyan,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      margin: const EdgeInsets.all(16),
    ));
  }

  void _logout() async {
    HapticFeedback.heavyImpact();
    await _apiService.logout();
    if (mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => LoginScreen()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.accentCyan))
          : CustomScrollView(
              slivers: [
                SliverToBoxAdapter(
                  child: _buildProfileHeader(),
                ),
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 100),
                  sliver: SliverList(
                    delegate: SliverChildListDelegate([
                      const SizedBox(height: 20),

                      // ── Account Info Section ──
                      _buildSectionLabel("ACCOUNT INFO"),
                      const SizedBox(height: 10),
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
                        ),
                        child: Column(
                          children: [
                            _buildInfoTile(
                              icon: Icons.person_outline_rounded,
                              iconColor: AppTheme.primaryNavy,
                              label: "Full Name",
                              value: _userName,
                              showBorder: false,
                            ),
                            if (_userEmail.isNotEmpty) ...[
                              Divider(height: 1, color: AppTheme.primarySlate.withOpacity(0.08)),
                              _buildInfoTile(
                                icon: Icons.email_outlined,
                                iconColor: AppTheme.accentCyan,
                                label: "Email Address",
                                value: _userEmail,
                                showBorder: false,
                              ),
                            ],
                            Divider(height: 1, color: AppTheme.primarySlate.withOpacity(0.08)),
                            _buildInfoTile(
                              icon: Icons.badge_outlined,
                              iconColor: AppTheme.accentAmber,
                              label: "Role / Position",
                              value: _userRole,
                              showBorder: false,
                            ),
                          ],
                        ),
                      ).animate().fadeIn().slideY(begin: 0.1),

                      const SizedBox(height: 28),

                      // ── Security Section ──
                      _buildSectionLabel("SECURITY & PRIVACY"),
                      const SizedBox(height: 10),
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
                        ),
                        child: _buildSettingTile(
                          icon: Icons.fingerprint_rounded,
                          iconColor: AppTheme.accentCyan,
                          title: "Biometric Login",
                          subtitle: _isHardwareAvailable
                              ? "Use Fingerprint or Face ID"
                              : "Not available on this device",
                          trailing: Switch(
                            value: _isBiometricEnabled,
                            onChanged:
                                _isHardwareAvailable ? _toggleBiometric : null,
                            activeColor: AppTheme.accentCyan,
                          ),
                          showBorder: false,
                        ),
                      ).animate().fadeIn(delay: 100.ms).slideY(begin: 0.1),

                      const SizedBox(height: 40),

                      // ── Sign Out Button ──
                      GestureDetector(
                        onTap: _showLogoutDialog,
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(vertical: 18),
                          decoration: BoxDecoration(
                            color: AppTheme.accentRose.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: AppTheme.accentRose.withOpacity(0.2)),
                          ),
                          child: Center(
                            child: Text(
                              "Sign Out",
                              style: GoogleFonts.outfit(
                                fontSize: 16,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.accentRose,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),
                        ),
                      ).animate().fadeIn(delay: 200.ms).slideY(begin: 0.1),

                      const SizedBox(height: 40),
                      Center(
                        child: Text(
                          "VioTrack Dean  •  v1.0.0\nSecure Academic Environment",
                          textAlign: TextAlign.center,
                          style: GoogleFonts.outfit(
                              fontSize: 11,
                              color: AppTheme.textHint,
                              height: 1.6),
                        ),
                      ),
                    ]),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildProfileHeader() {
    return Container(
      color: AppTheme.bgLight,
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 32, 24, 36),
          child: Column(
            children: [
              // Avatar with initials
              Stack(
                alignment: Alignment.center,
                children: [
                  Container(
                    width: 140,
                    height: 140,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: AppTheme.primaryNavy.withOpacity(0.1),
                    ),
                  ).animate(onPlay: (c) => c.repeat(reverse: true))
                   .scale(begin: const Offset(0.9, 0.9), end: const Offset(1.1, 1.1), duration: 2.seconds, curve: Curves.easeInOutSine)
                   .fade(begin: 0.3, end: 0.8),
                  Container(
                    width: 110,
                    height: 110,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: AppTheme.primaryNavy,
                      border: Border.all(color: Colors.white, width: 4),
                      boxShadow: [
                        BoxShadow(
                          color: AppTheme.primaryNavy.withOpacity(0.3),
                          blurRadius: 24,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Center(
                      child: Text(
                        _userInitials,
                        style: GoogleFonts.outfit(
                          fontSize: 40,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                          letterSpacing: -1,
                        ),
                      ),
                    ),
                  ),
                ],
              ).animate().scale(delay: 100.ms, duration: 600.ms, curve: Curves.easeOutBack),
              const SizedBox(height: 24),
              Text(
                _userName,
                textAlign: TextAlign.center,
                style: GoogleFonts.outfit(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.primaryNavy,
                    letterSpacing: -0.5),
              ).animate().fadeIn(delay: 200.ms).slideY(begin: 0.2, end: 0),
              const SizedBox(height: 4),
              Text(
                _userRole,
                style: GoogleFonts.outfit(
                    fontSize: 13,
                    color: AppTheme.textSub,
                    fontWeight: FontWeight.w500),
              ).animate().fadeIn(delay: 250.ms).slideY(begin: 0.2, end: 0),
              const SizedBox(height: 18),
              // Active session badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.accentEmerald.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(100),
                  border: Border.all(color: AppTheme.accentEmerald.withOpacity(0.2)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 7,
                      height: 7,
                      decoration: const BoxDecoration(
                        color: AppTheme.accentEmerald,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text("ACTIVE SESSION",
                        style: GoogleFonts.outfit(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.accentEmerald,
                            letterSpacing: 1.5)),
                  ],
                ),
              ).animate().fadeIn(delay: 300.ms),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoTile({
    required IconData icon,
    required Color iconColor,
    required String label,
    required String value,
    bool showBorder = true,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
      decoration: showBorder ? BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
      ) : null,
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label.toUpperCase(),
                  style: GoogleFonts.outfit(
                    fontSize: 9,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textMuted,
                    letterSpacing: 1.2,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  value,
                  style: GoogleFonts.outfit(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textMain,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    ).animate().fadeIn().slideX(begin: 0.05, end: 0);
  }

  Widget _buildSectionLabel(String label) {
    return Text(
      label,
      style: GoogleFonts.outfit(
          fontSize: 10,
          fontWeight: FontWeight.w900,
          color: AppTheme.textMuted,
          letterSpacing: 2),
    );
  }

  Widget _buildSettingTile({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
    Widget? trailing,
    VoidCallback? onTap,
    Color? titleColor,
    bool showBorder = true,
  }) {
    return Container(
      decoration: showBorder ? BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
      ) : null,
      child: ListTile(
        onTap: onTap != null
            ? () {
                HapticFeedback.lightImpact();
                onTap();
              }
            : null,
        contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 6),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        leading: Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, color: iconColor, size: 20),
        ),
        title: Text(
          title,
          style: GoogleFonts.outfit(
              fontWeight: FontWeight.w700,
              fontSize: 14,
              color: titleColor ?? AppTheme.textMain),
        ),
        subtitle: Text(subtitle,
            style:
                GoogleFonts.outfit(fontSize: 12, color: AppTheme.textMuted)),
        trailing: trailing ??
            const Icon(Icons.chevron_right_rounded,
                color: AppTheme.textHint, size: 20),
      ),
    );
  }

  void _showLogoutDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        backgroundColor: Colors.white,
        title: Text("Sign Out?",
            style:
                GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 18,
                color: AppTheme.primaryNavy)),
        content: Text("You'll need to authenticate again to access the system.",
            style: GoogleFonts.outfit(color: AppTheme.textMuted)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text("CANCEL",
                style: GoogleFonts.outfit(
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMuted)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _logout();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.accentRose,
              foregroundColor: Colors.white,
              minimumSize: Size.zero,
              padding:
                  const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              elevation: 0,
            ),
            child: Text("SIGN OUT",
                style: GoogleFonts.outfit(
                    fontWeight: FontWeight.w800, fontSize: 12)),
          ),
        ],
      ),
    );
  }
}
