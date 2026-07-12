import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';
import '../utils/case_status.dart';

class CaseTimelineWidget extends StatelessWidget {
  final String currentStatus;

  const CaseTimelineWidget({
    super.key,
    required this.currentStatus,
  });

  @override
  Widget build(BuildContext context) {
    final stages = CaseStatus.workflowSteps;
    final labels = CaseStatus.workflowShortLabels;
    final currentIdx = CaseStatus.workflowIndex(currentStatus);

    return Container(
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
        border: Border.all(color: AppTheme.primarySlate.withValues(alpha: 0.04)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final timeline = Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: List.generate(stages.length, (i) {
              final isDone = i <= currentIdx;
              final isLast = i == stages.length - 1;
              return Expanded(
                child: Row(
                  children: [
                    Column(
                      children: [
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 500),
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: isDone ? AppTheme.accentCyan : Colors.white,
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: isDone ? AppTheme.accentCyan : AppTheme.inputBorder,
                              width: isDone ? 0 : 2,
                            ),
                          ),
                          child: Icon(
                            isDone ? Icons.check_rounded : Icons.circle,
                            color: isDone ? Colors.white : Colors.transparent,
                            size: 20,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          labels[i],
                          textAlign: TextAlign.center,
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: isDone ? AppTheme.textMain : AppTheme.textMuted,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                    if (!isLast)
                      Expanded(
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 500),
                          height: 3,
                          color: isDone
                              ? AppTheme.accentCyan
                              : AppTheme.inputBorder.withValues(alpha: 0.5),
                          margin: const EdgeInsets.only(bottom: 26, left: 8, right: 8),
                        ),
                      ),
                  ],
                ),
              );
            }),
          );

          if (constraints.maxWidth < 380) {
            return SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: SizedBox(width: 380, child: timeline),
            );
          }
          return timeline;
        },
      ),
    );
  }
}
