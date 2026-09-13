import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/app_ui.dart';
import '../widgets/empty_state_widget.dart';
import '../widgets/skeleton_loader.dart';

class PolicyLookupScreen extends StatefulWidget {
  const PolicyLookupScreen({super.key});

  @override
  State<PolicyLookupScreen> createState() => _PolicyLookupScreenState();
}

class _PolicyLookupScreenState extends State<PolicyLookupScreen> {
  final _controller = TextEditingController();
  final _focusNode = FocusNode();
  final _api = ApiService();
  bool _loading = false;
  String? _reply;
  String? _error;

  static const _examples = [
    'What is the uniform policy?',
    'Sanctions for major offenses?',
    'How many absences are allowed?',
  ];

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  Future<void> _ask([String? preset]) async {
    final question = (preset ?? _controller.text).trim();
    if (question.isEmpty) return;

    if (preset != null) {
      _controller.text = question;
    }

    HapticFeedback.lightImpact();
    setState(() {
      _loading = true;
      _error = null;
      _reply = null;
    });

    try {
      final result = await _api.policyLookup(question);
      if (!mounted) return;
      setState(() => _reply = result['reply']?.toString());
    } catch (_) {
      if (!mounted) return;
      setState(
        () => _error =
            'Could not reach the policy assistant. Check your connection and try again.',
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.viewInsetsOf(context).bottom;

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      appBar: AppUi.innerAppBar(context: context, title: 'Policy lookup'),
      body: Column(
        children: [
          Expanded(
            child: ListView(
              padding: EdgeInsets.fromLTRB(20, 16, 20, 20 + bottomInset),
              children: [
                AppUi.surfaceCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        'Your question',
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textSub,
                        ),
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _controller,
                        focusNode: _focusNode,
                        maxLines: 3,
                        textInputAction: TextInputAction.send,
                        onSubmitted: (_) => _ask(),
                        style: GoogleFonts.inter(fontSize: 15),
                        decoration: InputDecoration(
                          hintText: 'e.g. What is the uniform policy?',
                          hintStyle: GoogleFonts.inter(
                            color: AppTheme.textHint,
                            fontSize: 14,
                          ),
                          filled: true,
                          fillColor: AppTheme.bgLight,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(14),
                            borderSide: BorderSide.none,
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(14),
                            borderSide: const BorderSide(
                              color: AppTheme.primary,
                              width: 1.5,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 14),
                      SizedBox(
                        height: 48,
                        child: FilledButton.icon(
                          onPressed: _loading ? null : () => _ask(),
                          style: FilledButton.styleFrom(
                            backgroundColor: AppTheme.primary,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14),
                            ),
                          ),
                          icon: _loading
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    color: Colors.white,
                                  ),
                                )
                              : const Icon(Icons.auto_awesome_rounded, size: 18),
                          label: Text(
                            _loading ? 'Asking…' : 'Ask Nexus AI',
                            style: GoogleFonts.inter(
                              fontWeight: FontWeight.w700,
                              fontSize: 15,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Try an example',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMuted,
                  ),
                ),
                const SizedBox(height: 10),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _examples.map((example) {
                    return ActionChip(
                      onPressed: _loading ? null : () => _ask(example),
                      backgroundColor: AppTheme.bgCard,
                      side: BorderSide(
                        color: AppTheme.inputBorder.withValues(alpha: 0.9),
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      label: Text(
                        example,
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.textSub,
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 20),
                if (_loading)
                  const ShimmerLoader.rounded(
                    height: 160,
                    width: double.infinity,
                  )
                else if (_error != null)
                  AppUi.surfaceCard(
                    borderColor: AppTheme.accentRose.withValues(alpha: 0.28),
                    color: AppTheme.accentRose.withValues(alpha: 0.05),
                    child: Column(
                      children: [
                        EmptyStateWidget(
                          icon: Icons.cloud_off_rounded,
                          title: 'Lookup failed',
                          message: _error!,
                          action: AppUi.retryButton(onPressed: () => _ask()),
                        ),
                      ],
                    ),
                  )
                else if (_reply != null)
                  AppUi.surfaceCard(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            AppUi.iconCircle(
                              icon: Icons.auto_awesome_rounded,
                              color: AppTheme.primary,
                              size: 34,
                              iconSize: 16,
                            ),
                            const SizedBox(width: 10),
                            Text(
                              'Answer',
                              style: GoogleFonts.inter(
                                fontSize: 15,
                                fontWeight: FontWeight.w700,
                                color: AppTheme.textMain,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        Text(
                          _reply!,
                          style: GoogleFonts.inter(
                            fontSize: 14,
                            height: 1.55,
                            color: AppTheme.textSub,
                          ),
                        ),
                      ],
                    ),
                  )
                else
                  const EmptyStateWidget(
                    icon: Icons.menu_book_outlined,
                    title: 'Ask a policy question',
                    message:
                        'Type a question above or tap an example to look up handbook guidance.',
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
