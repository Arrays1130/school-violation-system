import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter/services.dart';
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

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final bool available = await SecurityService.isBiometricsSupported();
    final bool enabled = await SecurityService.isBiometricLockEnabled();
    setState(() {
      _isHardwareAvailable = available;
      _isBiometricEnabled = enabled;
      _isLoading = false;
    });
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
      backgroundColor: AppTheme.accentPurple,
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
              child: CircularProgressIndicator(color: AppTheme.accentPurple))
          : CustomScrollView(
              slivers: [
                // ── Header ──
                SliverToBoxAdapter(
                  child: _buildProfileHeader(),
                ),
                // ── Settings ──
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 100),
                  sliver: SliverList(
                    delegate: SliverChildListDelegate([
                      const SizedBox(height: 24),
                      _buildSectionLabel("SECURITY & PRIVACY"),
                      const SizedBox(height: 10),
                      _buildSettingTile(
                        icon: Icons.fingerprint_rounded,
                        iconColor: AppTheme.accentPurple,
                        title: "Biometric Login",
                        subtitle: _isHardwareAvailable
                            ? "Use Fingerprint or Face ID"
                            : "Not available on this device",
                        trailing: Switch(
                          value: _isBiometricEnabled,
                          onChanged:
                              _isHardwareAvailable ? _toggleBiometric : null,
                          activeThumbColor: AppTheme.accentPurple,
                          activeTrackColor:
                              AppTheme.accentPurple.withOpacity(0.25),
                          inactiveThumbColor: AppTheme.textHint,
                          inactiveTrackColor:
                              AppTheme.textHint.withOpacity(0.2),
                        ),
                      ),
                      const SizedBox(height: 24),
                      _buildSectionLabel("ACCOUNT"),
                      const SizedBox(height: 10),
                      _buildSettingTile(
                        icon: Icons.logout_rounded,
                        iconColor: AppTheme.accentRose,
                        title: "Sign Out",
                        subtitle: "Ends your current session securely",
                        titleColor: AppTheme.accentRose,
                        onTap: _showLogoutDialog,
                      ),
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
            ).animate().fadeIn(duration: 400.ms),
    );
  }

  Widget _buildProfileHeader() {
    return Container(
      decoration: const BoxDecoration(gradient: AppTheme.heroGradient),
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 24, 24, 36),
          child: Column(
            children: [
              // Avatar
              Container(
                width: 88,
                height: 88,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: AppTheme.accentGradient,
                  boxShadow: [
                    BoxShadow(
                        color: AppTheme.accentPurple.withOpacity(0.4),
                        blurRadius: 24,
                        offset: const Offset(0, 8)),
                  ],
                  border: Border.all(color: Colors.white.withOpacity(0.3), width: 3),
                ),
                child: const Icon(Icons.person_rounded,
                    color: Colors.white, size: 44),
              ),
              const SizedBox(height: 16),
              Text(
                "Dean of Discipline",
                style: GoogleFonts.outfit(
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    color: Colors.white),
              ),
              const SizedBox(height: 4),
              Text(
                "Academic Integrity Office",
                style: GoogleFonts.outfit(
                    fontSize: 13,
                    color: Colors.white.withOpacity(0.6)),
              ),
              const SizedBox(height: 20),
              // Role badge
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.white.withOpacity(0.2)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 7,
                      height: 7,
                      decoration: BoxDecoration(
                        color: AppTheme.accentEmerald,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                              color: AppTheme.accentEmerald.withOpacity(0.8),
                              blurRadius: 4),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text("ACTIVE SESSION",
                        style: GoogleFonts.outfit(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                            letterSpacing: 1.5)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
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
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: AppTheme.softShadow,
      ),
      child: ListTile(
        onTap: onTap != null
            ? () {
                HapticFeedback.lightImpact();
                onTap();
              }
            : null,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 18, vertical: 6),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
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
        title: Text("Sign Out?",
            style:
                GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 18)),
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
              minimumSize: Size.zero,
              padding:
                  const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
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
