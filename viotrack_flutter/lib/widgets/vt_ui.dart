import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';

class VtCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  final VoidCallback? onTap;

  const VtCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(18),
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.radiusLg),
        child: Ink(
          decoration: BoxDecoration(
            color: AppTheme.bgCard,
            borderRadius: BorderRadius.circular(AppTheme.radiusLg),
            boxShadow: AppTheme.softShadow,
          ),
          padding: padding,
          child: child,
        ),
      ),
    );
  }
}

class VtStatusChip extends StatelessWidget {
  final String label;
  final Color color;

  const VtStatusChip({super.key, required this.label, required this.color});

  factory VtStatusChip.fromStatus(String status, {bool endorsed = false}) {
    if (endorsed) {
      return VtStatusChip(label: 'Endorsed', color: AppTheme.accentRose);
    }
    switch (status) {
      case 'Hearing Scheduled':
        return VtStatusChip(label: status, color: AppTheme.accentCyan);
      case 'Hearing':
        return VtStatusChip(label: status, color: AppTheme.primaryIndigo);
      case 'Closed':
      case 'Resolved':
        return VtStatusChip(label: status, color: AppTheme.accentEmerald);
      default:
        return VtStatusChip(label: status, color: AppTheme.accentAmber);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(AppTheme.radiusPill),
      ),
      child: Text(
        label,
        style: GoogleFonts.inter(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }
}

class VtSectionTitle extends StatelessWidget {
  final String title;
  final IconData icon;

  const VtSectionTitle({super.key, required this.title, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppTheme.textMuted),
        const SizedBox(width: 8),
        Text(
          title,
          style: GoogleFonts.inter(
            fontSize: 15,
            fontWeight: FontWeight.w600,
            color: AppTheme.textMain,
            letterSpacing: -0.2,
          ),
        ),
      ],
    );
  }
}
