import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';

class CaseStaleBanner extends StatelessWidget {
  final VoidCallback onRetry;

  const CaseStaleBanner({super.key, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppTheme.accentAmber.withValues(alpha: 0.12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        child: Row(
          children: [
            const Icon(Icons.cloud_off_rounded, size: 18, color: AppTheme.accentAmber),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                'Showing saved data. Details may be outdated.',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textMain,
                ),
              ),
            ),
            TextButton(onPressed: onRetry, child: const Text('Retry')),
          ],
        ),
      ),
    );
  }
}
