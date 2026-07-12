import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';
import '../utils/case_status.dart';

/// Wraps any tappable card with a subtle iOS-like press-down scale.
/// Uses a raw pointer Listener so it composes with an inner InkWell
/// (the inner widget keeps handling the tap; this only animates scale).
class VtPressable extends StatefulWidget {
  final Widget child;
  final VoidCallback? onTap;
  final double pressedScale;

  const VtPressable({
    super.key,
    required this.child,
    this.onTap,
    this.pressedScale = 0.97,
  });

  @override
  State<VtPressable> createState() => _VtPressableState();
}

class _VtPressableState extends State<VtPressable> {
  bool _pressed = false;

  void _setPressed(bool value) {
    if (_pressed != value && mounted) {
      setState(() => _pressed = value);
    }
  }

  @override
  Widget build(BuildContext context) {
    Widget scaled = AnimatedScale(
      scale: _pressed ? widget.pressedScale : 1.0,
      duration: const Duration(milliseconds: 110),
      curve: Curves.easeOut,
      child: widget.child,
    );

    if (widget.onTap != null) {
      scaled = GestureDetector(onTap: widget.onTap, child: scaled);
    }

    return Listener(
      onPointerDown: (_) => _setPressed(true),
      onPointerUp: (_) => _setPressed(false),
      onPointerCancel: (_) => _setPressed(false),
      child: scaled,
    );
  }
}

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
    final resolved = endorsed
        ? 'Endorsed to Grievance'
        : CaseStatus.normalize(status);

    return VtStatusChip(
      label: CaseStatus.displayLabel(resolved),
      color: CaseStatus.colorFor(resolved, endorsed: endorsed),
    );
  }

  factory VtStatusChip.fromCase(Map<String, dynamic> caseData) {
    final endorsed = CaseStatus.isEndorsed(caseData);
    final resolved = CaseStatus.resolve(caseData);

    return VtStatusChip(
      label: CaseStatus.displayLabel(resolved),
      color: CaseStatus.colorFor(resolved, endorsed: endorsed),
    );
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
