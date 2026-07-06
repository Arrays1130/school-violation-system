import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';
import 'vt_ui.dart';

/// Shared VioTrack UI — clean cards, soft shadows, I-LINK dean portal styling.
class AppUi {
  AppUi._();

  static const String ilinkLogoAsset = 'assets/images/ilink_college_logo.png';
  static const String viotrackLogoAsset = 'assets/images/viotrack_logo.png';
  static const String appIconAsset = 'assets/images/viotrack_logo.png';

  static Widget ilinkWatermark({double size = 110, double opacity = 0.11}) {
    return Opacity(
      opacity: opacity,
      child: Image.asset(
        ilinkLogoAsset,
        width: size,
        height: size,
        fit: BoxFit.contain,
      ),
    );
  }

  static BoxDecoration cardDecoration({
    Color? borderColor,
    double radius = AppTheme.radiusLg,
  }) => BoxDecoration(
    color: AppTheme.bgCard,
    borderRadius: BorderRadius.circular(radius),
    border: Border.all(
      color: borderColor ?? AppTheme.inputBorder.withValues(alpha: 0.7),
    ),
    boxShadow: AppTheme.cardShadow,
  );

  static Widget brandPill({
    required String label,
    Widget? leading,
    Color? textColor,
    Color? backgroundColor,
    Color? borderColor,
    EdgeInsets padding = const EdgeInsets.symmetric(
      horizontal: 12,
      vertical: 8,
    ),
  }) {
    final resolvedTextColor = textColor ?? Colors.white;
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: backgroundColor ?? Colors.white.withValues(alpha: 0.16),
        borderRadius: BorderRadius.circular(AppTheme.radiusPill),
        border: Border.all(
          color: borderColor ?? resolvedTextColor.withValues(alpha: 0.12),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (leading != null) ...[leading, const SizedBox(width: 8)],
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: resolvedTextColor,
              letterSpacing: 0.2,
            ),
          ),
        ],
      ),
    );
  }

  static Widget inlineSectionHeader(
    String title, {
    IconData? icon,
    String? subtitle,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (icon != null) ...[
            iconCircle(
              icon: icon,
              color: AppTheme.primaryIndigo,
              size: 34,
              iconSize: 17,
            ),
            const SizedBox(width: 10),
          ],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: GoogleFonts.inter(
                    fontSize: 17,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMain,
                    letterSpacing: -0.35,
                  ),
                ),
                if (subtitle != null) ...[
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      color: AppTheme.textMuted,
                      height: 1.35,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  static Widget staggerIn(
    Widget child,
    int index, {
    int baseDelayMs = 45,
  }) {
    final delay = (index * baseDelayMs).ms;
    return child
        .animate()
        .fadeIn(duration: 320.ms, delay: delay)
        .slideY(
          begin: 0.06,
          end: 0,
          duration: 320.ms,
          delay: delay,
          curve: Curves.easeOutCubic,
        );
  }

  static Widget sectionHeader(
    String title, {
    String? subtitle,
    String? action,
    VoidCallback? onAction,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 16, 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: GoogleFonts.inter(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMain,
                    letterSpacing: -0.4,
                  ),
                ),
              ),
              if (action != null && onAction != null)
                TextButton(
                  onPressed: onAction,
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 10),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  child: Text(
                    action,
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.primary,
                    ),
                  ),
                ),
            ],
          ),
          if (subtitle != null) ...[
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: GoogleFonts.inter(
                fontSize: 13,
                color: AppTheme.textMuted,
                height: 1.35,
              ),
            ),
          ],
        ],
      ),
    );
  }

  static Widget statusBadge(String status, {bool endorsed = false}) {
    return VtStatusChip.fromStatus(status, endorsed: endorsed);
  }

  /// Hero metric card — one big number, clear label.
  static Widget heroMetricCard({
    required String label,
    required String value,
    required String subtitle,
    IconData icon = Icons.shield_outlined,
    String? eyebrow,
    Widget? badge,
    Widget? watermark,
    VoidCallback? onTap,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 4),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(AppTheme.radiusXl),
          child: Ink(
            decoration: BoxDecoration(
              gradient: AppTheme.heroGradient,
              borderRadius: BorderRadius.circular(AppTheme.radiusXl),
              border: Border.all(color: Colors.white.withValues(alpha: 0.08)),
              boxShadow: AppTheme.floatShadow,
            ),
            child: LayoutBuilder(
              builder: (context, constraints) {
                final isCompact = constraints.maxWidth < 320;
                final cardPadding = isCompact ? 18.0 : 22.0;
                final topGap = isCompact ? 14.0 : 18.0;
                final eyebrowGap = isCompact ? 10.0 : 14.0;
                final labelSize = isCompact ? 13.0 : 14.0;
                final valueSize = isCompact ? 34.0 : 40.0;
                final subtitleSize = isCompact ? 12.0 : 13.0;
                final iconContainerPadding = isCompact ? 8.0 : 10.0;
                final iconRadius = isCompact ? 12.0 : 14.0;
                final iconSizeValue = isCompact ? 20.0 : 22.0;

                return Padding(
                  padding: EdgeInsets.all(cardPadding),
                  child: Stack(
                    children: [
                      if (watermark != null)
                        Positioned(
                          top: 4,
                          right: -6,
                          child: IgnorePointer(
                            child: Opacity(
                              opacity: 0.12,
                              child: SizedBox(
                                width: 110,
                                height: 110,
                                child: FittedBox(child: watermark),
                              ),
                            ),
                          ),
                        ),
                      Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: EdgeInsets.all(iconContainerPadding),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.18),
                                  borderRadius: BorderRadius.circular(
                                    iconRadius,
                                  ),
                                ),
                                child: Icon(
                                  icon,
                                  color: Colors.white,
                                  size: iconSizeValue,
                                ),
                              ),
                              const Spacer(),
                              badge ??
                                  Icon(
                                    Icons.chevron_right_rounded,
                                    color: Colors.white.withValues(alpha: 0.7),
                                  ),
                            ],
                          ),
                          SizedBox(height: topGap),
                          if (eyebrow != null) ...[
                            brandPill(
                              label: eyebrow,
                              textColor: Colors.white,
                              backgroundColor: Colors.white.withValues(
                                alpha: 0.14,
                              ),
                              borderColor: Colors.white.withValues(alpha: 0.14),
                            ),
                            SizedBox(height: eyebrowGap),
                          ],
                          Text(
                            label,
                            style: GoogleFonts.inter(
                              fontSize: labelSize,
                              fontWeight: FontWeight.w500,
                              color: Colors.white.withValues(alpha: 0.85),
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            value,
                            style: GoogleFonts.inter(
                              fontSize: valueSize,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                              letterSpacing: -1.2,
                              height: 1.05,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            subtitle,
                            maxLines: isCompact ? 2 : 3,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                              fontSize: subtitleSize,
                              color: Colors.white.withValues(alpha: 0.8),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ),
      ),
    );
  }

  static Widget pageHeader({
    required String greeting,
    required String title,
    String? subtitle,
    Widget? badge,
    Widget? trailing,
    bool compact = false,
  }) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, compact ? 4 : 8, 12, compact ? 0 : 4),
      child: SafeArea(
        bottom: false,
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    greeting,
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      color: AppTheme.textMuted,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    title,
                    style: GoogleFonts.inter(
                      fontSize: compact ? 26 : 28,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.textMain,
                      letterSpacing: -0.8,
                      height: 1.1,
                    ),
                  ),
                  if (subtitle != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        color: AppTheme.textMuted,
                        fontWeight: FontWeight.w500,
                        height: 1.35,
                      ),
                    ),
                  ],
                  if (badge != null) ...[const SizedBox(height: 10), badge],
                ],
              ),
            ),
            ?trailing,
          ],
        ),
      ),
    );
  }

  static Widget gradientHeader({
    required String greeting,
    required String title,
    String? subtitle,
    Widget? badge,
    Widget? trailing,
    Widget? bottom,
    Widget? watermark,
  }) {
    return Container(
      decoration: BoxDecoration(
        gradient: AppTheme.heroGradient,
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(28),
          bottomRight: Radius.circular(28),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 8, 12, 20),
          child: Stack(
            children: [
              Positioned(
                top: -30,
                right: -24,
                child: IgnorePointer(
                  child: Container(
                    width: 148,
                    height: 148,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.white.withValues(alpha: 0.08),
                    ),
                  ),
                ),
              ),
              if (watermark != null)
                Positioned(
                  top: 12,
                  right: 6,
                  child: IgnorePointer(
                    child: Opacity(
                      opacity: 0.11,
                      child: SizedBox(
                        width: 110,
                        height: 110,
                        child: FittedBox(child: watermark),
                      ),
                    ),
                  ),
                ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                if (badge != null) ...[
                                  badge,
                                  const SizedBox(width: 10),
                                ],
                                Expanded(
                                  child: Text(
                                    greeting,
                                    style: GoogleFonts.inter(
                                      fontSize: 14,
                                      color: Colors.white.withValues(
                                        alpha: 0.85,
                                      ),
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Text(
                              title,
                              style: GoogleFonts.inter(
                                fontSize: 26,
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                                letterSpacing: -0.5,
                              ),
                            ),
                            if (subtitle != null) ...[
                              const SizedBox(height: 8),
                              Text(
                                subtitle,
                                style: GoogleFonts.inter(
                                  fontSize: 13,
                                  height: 1.35,
                                  color: Colors.white.withValues(alpha: 0.78),
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                      ?trailing,
                    ],
                  ),
                  if (bottom != null) ...[const SizedBox(height: 18), bottom],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  static Widget surfaceCard({
    required Widget child,
    EdgeInsets padding = const EdgeInsets.all(16),
    double radius = AppTheme.radiusLg,
    Color? color,
    Color? borderColor,
    bool clip = false,
  }) {
    return Container(
      padding: padding,
      clipBehavior: clip ? Clip.antiAlias : Clip.none,
      decoration: BoxDecoration(
        color: color ?? AppTheme.bgCard,
        borderRadius: BorderRadius.circular(radius),
        border: Border.all(
          color: borderColor ?? AppTheme.inputBorder.withValues(alpha: 0.7),
        ),
        boxShadow: AppTheme.cardShadow,
      ),
      child: child,
    );
  }

  static String formatRelativeTime(DateTime? time) {
    if (time == null) return 'just now';
    final diff = DateTime.now().difference(time);
    if (diff.inSeconds < 60) return 'just now';
    if (diff.inMinutes < 60) {
      return '${diff.inMinutes} min ago';
    }
    if (diff.inHours < 24) {
      return '${diff.inHours} hr ago';
    }
    return '${diff.inDays}d ago';
  }

  static Widget dataBanner({
    required IconData icon,
    required String message,
    required Color accent,
    String? actionLabel,
    VoidCallback? onAction,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 4),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: accent.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: accent.withValues(alpha: 0.16)),
        ),
        child: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: accent.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, size: 18, color: accent),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                message,
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textMain,
                  height: 1.35,
                ),
              ),
            ),
            if (actionLabel != null && onAction != null) ...[
              const SizedBox(width: 8),
              TextButton(
                onPressed: onAction,
                style: TextButton.styleFrom(
                  foregroundColor: accent,
                  padding: const EdgeInsets.symmetric(horizontal: 10),
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
                child: Text(
                  actionLabel,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  static Widget subtleMetaLine(String text, {IconData? icon}) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 2, 20, 6),
      child: Row(
        children: [
          if (icon != null) ...[
            Icon(icon, size: 13, color: AppTheme.textHint),
            const SizedBox(width: 6),
          ],
          Expanded(
            child: Text(
              text,
              style: GoogleFonts.inter(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: AppTheme.textHint,
              ),
            ),
          ),
        ],
      ),
    );
  }

  static Widget quickActionTile({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
    String? value,
  }) {
    return Material(
      color: AppTheme.bgCard,
      borderRadius: BorderRadius.circular(18),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Ink(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: color.withValues(alpha: 0.12)),
            boxShadow: AppTheme.softShadow,
          ),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
            child: Row(
              children: [
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: color, size: 21),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (value != null)
                        Text(
                          value,
                          style: GoogleFonts.inter(
                            fontSize: 20,
                            fontWeight: FontWeight.w800,
                            color: AppTheme.textMain,
                            letterSpacing: -0.5,
                            height: 1,
                          ),
                        ),
                      Text(
                        label,
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                Icon(
                  Icons.arrow_forward_ios_rounded,
                  size: 13,
                  color: AppTheme.textHint.withValues(alpha: 0.8),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  static Widget quickActionsGrid({required List<Widget> actions}) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 4, 20, 8),
      child: Column(
        children: [
          for (var i = 0; i < actions.length; i += 2)
            Padding(
              padding: EdgeInsets.only(bottom: i + 2 < actions.length ? 10 : 0),
              child: Row(
                children: [
                  Expanded(child: actions[i]),
                  if (i + 1 < actions.length) ...[
                    const SizedBox(width: 10),
                    Expanded(child: actions[i + 1]),
                  ],
                ],
              ),
            ),
        ],
      ),
    );
  }

  static Widget quickActionButton({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
    String? value,
  }) {
    return Material(
      color: AppTheme.bgCard,
      borderRadius: BorderRadius.circular(18),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Ink(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: color.withValues(alpha: 0.12),
            ),
            boxShadow: AppTheme.softShadow,
          ),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: color, size: 20),
                ),
                if (value != null) ...[
                  const SizedBox(height: 8),
                  Text(
                    value,
                    style: GoogleFonts.inter(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textMain,
                      letterSpacing: -0.5,
                      height: 1,
                    ),
                  ),
                ],
                const SizedBox(height: 6),
                Text(
                  label,
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textMuted,
                    height: 1.2,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  static Widget quickActionsRow({required List<Widget> actions}) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
      child: Row(
        children: [
          for (var i = 0; i < actions.length; i++) ...[
            if (i > 0) const SizedBox(width: 8),
            Expanded(child: actions[i]),
          ],
        ],
      ),
    );
  }

  static Widget expandTile({
    required String title,
    required String subtitle,
    required bool expanded,
    required VoidCallback onToggle,
    required Widget child,
    Widget? trailing,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
      child: AppUi.surfaceCard(
        padding: EdgeInsets.zero,
        clip: true,
        child: Column(
          children: [
            Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: onToggle,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 14, 12, 14),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              title,
                              style: GoogleFonts.inter(
                                fontSize: 15,
                                fontWeight: FontWeight.w700,
                                color: AppTheme.textMain,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              subtitle,
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                color: AppTheme.textMuted,
                              ),
                            ),
                          ],
                        ),
                      ),
                      if (trailing != null) ...[
                        trailing,
                        const SizedBox(width: 8),
                      ],
                      AnimatedRotation(
                        turns: expanded ? 0.5 : 0,
                        duration: const Duration(milliseconds: 200),
                        child: const Icon(
                          Icons.keyboard_arrow_down_rounded,
                          color: AppTheme.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            AnimatedCrossFade(
              firstChild: const SizedBox.shrink(),
              secondChild: Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                child: child,
              ),
              crossFadeState: expanded
                  ? CrossFadeState.showSecond
                  : CrossFadeState.showFirst,
              duration: const Duration(milliseconds: 200),
            ),
          ],
        ),
      ),
    );
  }

  static Widget retryButton({required VoidCallback onPressed}) {
    return FilledButton.icon(
      onPressed: onPressed,
      icon: const Icon(Icons.refresh_rounded, size: 18),
      label: const Text('Try again'),
      style: FilledButton.styleFrom(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
      ),
    );
  }

  static Widget iconCircle({
    required IconData icon,
    required Color color,
    double size = 40,
    double iconSize = 18,
    Color? backgroundColor,
  }) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: backgroundColor ?? color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(size * 0.32),
      ),
      child: Icon(icon, color: color, size: iconSize),
    );
  }

  static Widget statChip({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Container(
      width: 112,
      decoration: BoxDecoration(
        color: AppTheme.bgCard,
        borderRadius: BorderRadius.circular(AppTheme.radiusLg),
        boxShadow: AppTheme.softShadow,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(AppTheme.radiusLg),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(AppTheme.radiusLg),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(icon, size: 15, color: color),
                ),
                const SizedBox(height: 8),
                Text(
                  value,
                  style: GoogleFonts.inter(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMain,
                    letterSpacing: -0.5,
                    height: 1,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: AppTheme.textMuted,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  static Widget statCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: Material(
        color: AppTheme.bgCard,
        borderRadius: BorderRadius.circular(AppTheme.radiusLg),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(AppTheme.radiusLg),
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(AppTheme.radiusLg),
              boxShadow: AppTheme.softShadow,
            ),
            child: Column(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, size: 18, color: color),
                ),
                const SizedBox(height: 10),
                Text(
                  value,
                  style: GoogleFonts.inter(
                    fontSize: 24,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMain,
                    height: 1,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: AppTheme.textMuted,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  static Widget searchBar({
    required String hint,
    required VoidCallback onTap,
    String? actionLabel,
  }) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 4),
      child: Material(
        color: AppTheme.bgCard,
        borderRadius: BorderRadius.circular(18),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: Ink(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(18),
              border: Border.all(
                color: AppTheme.primary.withValues(alpha: 0.1),
              ),
              boxShadow: AppTheme.softShadow,
            ),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: AppTheme.primaryLight,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.search_rounded,
                      size: 20,
                      color: AppTheme.primary,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          hint,
                          style: GoogleFonts.inter(
                            color: AppTheme.textMain,
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          actionLabel ?? 'Tap to search cases',
                          style: GoogleFonts.inter(
                            color: AppTheme.textHint,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Icon(
                    Icons.arrow_forward_ios_rounded,
                    size: 14,
                    color: AppTheme.textHint.withValues(alpha: 0.8),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  static Widget listRow({
    required String title,
    required String subtitle,
    required Widget trailing,
    IconData? icon,
    Color? iconColor,
    VoidCallback? onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: cardDecoration(),
      clipBehavior: Clip.antiAlias,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                if (icon != null) ...[
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: (iconColor ?? AppTheme.primary).withValues(
                        alpha: 0.1,
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      icon,
                      color: iconColor ?? AppTheme.primary,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                ],
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.w600,
                          fontSize: 15,
                          color: AppTheme.textMain,
                          letterSpacing: -0.2,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          color: AppTheme.textMuted,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                trailing,
              ],
            ),
          ),
        ),
      ),
    );
  }
}
