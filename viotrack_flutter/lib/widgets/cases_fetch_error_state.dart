import 'package:flutter/material.dart';
import '../widgets/app_ui.dart';
import '../widgets/empty_state_widget.dart';

class CasesFetchErrorState extends StatelessWidget {
  final VoidCallback onRetry;

  const CasesFetchErrorState({super.key, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const EmptyStateWidget(
              icon: Icons.wifi_off_rounded,
              title: 'Unable to load cases',
              message: 'Check your internet connection and pull down to refresh.',
            ),
            const SizedBox(height: 16),
            AppUi.retryButton(onPressed: onRetry),
          ],
        ),
      ),
    );
  }
}
