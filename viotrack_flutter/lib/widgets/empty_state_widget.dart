import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';

class EmptyStateWidget extends StatelessWidget {
  final IconData icon;
  final String title;
  final String message;
  final Widget? action;

  const EmptyStateWidget({
    super.key,
    required this.icon,
    required this.title,
    required this.message,
    this.action,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 32),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Icon area
          SizedBox(
            width: 140,
            height: 140,
            child: Stack(
              alignment: Alignment.center,
              children: [
                // Outer pulse ring
                Container(
                  width: 130,
                  height: 130,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: AppTheme.primaryNavy.withOpacity(0.06),
                      width: 1,
                    ),
                  ),
                ).animate(onPlay: (c) => c.repeat())
                  .scale(begin: const Offset(0.85, 0.85), end: const Offset(1.1, 1.1), duration: 2400.ms, curve: Curves.easeInOut)
                  .fade(begin: 0.8, end: 0.0, duration: 2400.ms),
                // Middle ring
                Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: AppTheme.primaryNavy.withOpacity(0.08),
                      width: 1.5,
                    ),
                  ),
                ).animate(onPlay: (c) => c.repeat())
                  .scale(begin: const Offset(0.9, 0.9), end: const Offset(1.05, 1.05), duration: 2000.ms, curve: Curves.easeInOut)
                  .fade(begin: 1.0, end: 0.3, duration: 2000.ms),
                // Icon container
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white,
                    border: Border.all(
                      color: AppTheme.primaryNavy.withOpacity(0.1),
                      width: 1.5,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: AppTheme.primaryNavy.withOpacity(0.08),
                        blurRadius: 24,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: Icon(
                    icon,
                    size: 36,
                    color: AppTheme.primaryNavy.withOpacity(0.5),
                  ),
                ).animate(onPlay: (c) => c.repeat(reverse: true))
                  .slideY(begin: 0, end: -0.08, duration: 2000.ms, curve: Curves.easeInOut),
              ],
            ),
          ).animate().scale(duration: 600.ms, curve: Curves.easeOutBack),

          const SizedBox(height: 28),

          Text(
            title,
            textAlign: TextAlign.center,
            style: GoogleFonts.outfit(
              fontSize: 20,
              fontWeight: FontWeight.w800,
              color: AppTheme.primaryNavy,
              letterSpacing: -0.3,
            ),
          ).animate().fadeIn(delay: 200.ms).slideY(begin: 0.2, end: 0),

          const SizedBox(height: 10),

          Text(
            message,
            textAlign: TextAlign.center,
            style: GoogleFonts.outfit(
              fontSize: 13,
              color: AppTheme.textSub,
              height: 1.6,
            ),
          ).animate().fadeIn(delay: 300.ms).slideY(begin: 0.2, end: 0),

          if (action != null) ...[
            const SizedBox(height: 24),
            action!.animate().fadeIn(delay: 400.ms).slideY(begin: 0.2, end: 0),
          ],
        ],
      ),
    );
  }
}
