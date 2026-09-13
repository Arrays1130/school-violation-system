import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/app_ui.dart';
import '../widgets/empty_state_widget.dart';
import '../widgets/skeleton_loader.dart';

class GsoSanctionDetailScreen extends StatefulWidget {
  final int assignmentId;

  const GsoSanctionDetailScreen({super.key, required this.assignmentId});

  @override
  State<GsoSanctionDetailScreen> createState() => _GsoSanctionDetailScreenState();
}

class _GsoSanctionDetailScreenState extends State<GsoSanctionDetailScreen> {
  final _api = ApiService();
  bool _loading = true;
  bool _busy = false;
  String? _error;
  Map<String, dynamic>? _assignment;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({bool forced = true}) async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await _api.getGsoSanction(widget.assignmentId, forcedRefresh: forced);
      if (!mounted) return;
      setState(() {
        _assignment = data;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString().replaceFirst('Exception: ', '');
        _loading = false;
      });
    }
  }

  Future<void> _run(Future<Map<String, dynamic>> Function() action, String success) async {
    if (_busy) return;
    setState(() => _busy = true);
    try {
      final res = await action();
      if (!mounted) return;
      final assignment = res['assignment'];
      if (assignment is Map) {
        setState(() => _assignment = Map<String, dynamic>.from(assignment));
      } else {
        await _load();
      }
      AppUi.showSnack(context, success, kind: SnackKind.success);
    } catch (e) {
      if (!mounted) return;
      AppUi.showSnack(
        context,
        e.toString().replaceFirst('Exception: ', ''),
        kind: SnackKind.error,
      );
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _confirmComplete() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Mark Complete?'),
        content: const Text(
          'This forwards the DTR record to OSA for the next step. Continue?',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Complete')),
        ],
      ),
    );
    if (ok != true) return;
    await _run(
      () => _api.gsoComplete(widget.assignmentId),
      'Forwarded to OSA.',
    );
  }

  @override
  Widget build(BuildContext context) {
    final a = _assignment;
    final caseMap = Map<String, dynamic>.from((a?['case'] as Map?) ?? {});
    final student = Map<String, dynamic>.from((caseMap['student'] as Map?) ?? {});
    final entries = (a?['dtr_entries'] as List?) ?? const [];
    final hoursServed = (a?['hours_served'] as num?)?.toDouble() ?? 0;
    final required = (a?['required_hours'] as num?)?.toDouble() ?? 0;
    final met = a?['requirements_met'] == true;
    final active = a?['status'] == 'in_progress';
    final hasOpen = a?['has_open_dtr'] == true;

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      appBar: AppBar(
        title: Text('DTR Monitor', style: GoogleFonts.inter(fontWeight: FontWeight.w700)),
      ),
      body: _loading
          ? const Padding(
              padding: EdgeInsets.all(16),
              child: ShimmerLoader.rounded(height: 220, width: double.infinity),
            )
          : _error != null
              ? EmptyStateWidget(
                  icon: Icons.error_outline,
                  title: 'Unable to load',
                  message: _error!,
                  action: TextButton(onPressed: _load, child: const Text('Retry')),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(16, 12, 16, 120),
                    children: [
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppTheme.inputBorder.withValues(alpha: 0.7)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              student['full_name']?.toString() ?? 'Student',
                              style: GoogleFonts.inter(
                                fontSize: 18,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${student['id_number'] ?? ''} · ${student['department'] ?? ''}',
                              style: GoogleFonts.inter(color: AppTheme.textMuted, fontSize: 13),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              caseMap['sanction']?.toString() ?? 'Community service',
                              style: GoogleFonts.inter(
                                fontWeight: FontWeight.w600,
                                color: AppTheme.textSub,
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              '${hoursServed.toStringAsFixed(2)} / ${required.toStringAsFixed(2)} hours',
                              style: GoogleFonts.inter(
                                fontSize: 22,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.primary,
                              ),
                            ),
                            const SizedBox(height: 8),
                            LinearProgressIndicator(
                              value: required <= 0 ? 0 : (hoursServed / required).clamp(0.0, 1.0),
                              minHeight: 10,
                              borderRadius: BorderRadius.circular(999),
                              backgroundColor: AppTheme.primaryLight,
                              color: AppTheme.primary,
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'DTR entries',
                        style: GoogleFonts.inter(fontWeight: FontWeight.w700, fontSize: 15),
                      ),
                      const SizedBox(height: 8),
                      if (entries.isEmpty)
                        Padding(
                          padding: const EdgeInsets.symmetric(vertical: 24),
                          child: Text(
                            'No time logs yet. Tap Time In when the student starts.',
                            style: GoogleFonts.inter(color: AppTheme.textMuted),
                          ),
                        )
                      else
                        ...entries.map((raw) {
                          final e = Map<String, dynamic>.from(raw as Map);
                          return Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(14),
                            ),
                            child: Row(
                              children: [
                                Icon(
                                  e['is_open'] == true
                                      ? Icons.timelapse_rounded
                                      : Icons.check_circle_outline,
                                  color: e['is_open'] == true
                                      ? AppTheme.accentAmber
                                      : AppTheme.primary,
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        e['is_open'] == true
                                            ? 'In progress'
                                            : '${(e['hours_served'] as num?)?.toStringAsFixed(2) ?? '0'} hrs',
                                        style: GoogleFonts.inter(fontWeight: FontWeight.w700),
                                      ),
                                      Text(
                                        '${e['time_in'] ?? ''}${e['time_out'] != null ? ' → ${e['time_out']}' : ''}',
                                        style: GoogleFonts.inter(
                                          fontSize: 12,
                                          color: AppTheme.textMuted,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          );
                        }),
                    ],
                  ),
                ),
      bottomNavigationBar: active
          ? SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _busy
                            ? null
                            : () => hasOpen
                                ? _run(
                                    () => _api.gsoTimeOut(widget.assignmentId),
                                    'Time out saved.',
                                  )
                                : _run(
                                    () => _api.gsoTimeIn(widget.assignmentId),
                                    'Time in saved.',
                                  ),
                        child: Text(hasOpen ? 'Time Out' : 'Time In'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: FilledButton(
                        onPressed: (_busy || !met)
                            ? null
                            : () {
                                HapticFeedback.mediumImpact();
                                _confirmComplete();
                              },
                        child: const Text('Complete'),
                      ),
                    ),
                  ],
                ),
              ),
            )
          : null,
    );
  }
}
