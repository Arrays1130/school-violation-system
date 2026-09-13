import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/empty_state_widget.dart';
import '../widgets/skeleton_loader.dart';
import 'gso_sanction_detail_screen.dart';
import 'profile_screen.dart';

class GsoMainLayout extends StatefulWidget {
  const GsoMainLayout({super.key});

  @override
  State<GsoMainLayout> createState() => _GsoMainLayoutState();
}

class _GsoMainLayoutState extends State<GsoMainLayout> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: IndexedStack(
        index: _index,
        children: const [
          GsoSanctionsScreen(),
          ProfileScreen(),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (i) {
          HapticFeedback.selectionClick();
          setState(() => _index = i);
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.schedule_outlined),
            selectedIcon: Icon(Icons.schedule_rounded),
            label: 'DTR Queue',
          ),
          NavigationDestination(
            icon: Icon(Icons.account_circle_outlined),
            selectedIcon: Icon(Icons.account_circle_rounded),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}

class GsoSanctionsScreen extends StatefulWidget {
  const GsoSanctionsScreen({super.key});

  @override
  State<GsoSanctionsScreen> createState() => _GsoSanctionsScreenState();
}

class _GsoSanctionsScreenState extends State<GsoSanctionsScreen> {
  final _api = ApiService();
  bool _loading = true;
  String? _error;
  List<dynamic> _items = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({bool forced = false}) async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await _api.getGsoSanctions(forcedRefresh: forced);
      final data = res['data'];
      if (!mounted) return;
      setState(() {
        _items = data is List ? data : <dynamic>[];
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      appBar: AppBar(
        title: Text(
          'GSO Sanctions',
          style: GoogleFonts.inter(fontWeight: FontWeight.w700),
        ),
        actions: [
          IconButton(
            onPressed: () => _load(forced: true),
            icon: const Icon(Icons.refresh_rounded),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => _load(forced: true),
        child: _loading
            ? ListView(
                padding: const EdgeInsets.all(16),
                children: const [
                  ShimmerLoader.rounded(height: 88, width: double.infinity),
                  SizedBox(height: 12),
                  ShimmerLoader.rounded(height: 88, width: double.infinity),
                  SizedBox(height: 12),
                  ShimmerLoader.rounded(height: 88, width: double.infinity),
                ],
              )
            : _error != null
                ? ListView(
                    children: [
                      const SizedBox(height: 80),
                      EmptyStateWidget(
                        icon: Icons.wifi_off_rounded,
                        title: 'Could not load queue',
                        message: _error!,
                        action: TextButton(
                          onPressed: () => _load(forced: true),
                          child: const Text('Retry'),
                        ),
                      ),
                    ],
                  )
                : _items.isEmpty
                    ? ListView(
                        children: const [
                          SizedBox(height: 80),
                          EmptyStateWidget(
                            icon: Icons.check_circle_outline,
                            title: 'No active assignments',
                            message:
                                'When OSA assigns community service hours, students will appear here for DTR monitoring.',
                          ),
                        ],
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                        itemCount: _items.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (context, index) {
                          final item = Map<String, dynamic>.from(_items[index] as Map);
                          final caseMap = Map<String, dynamic>.from(
                            (item['case'] as Map?) ?? {},
                          );
                          final student = Map<String, dynamic>.from(
                            (caseMap['student'] as Map?) ?? {},
                          );
                          final name = student['full_name']?.toString() ?? 'Student';
                          final hoursServed = (item['hours_served'] as num?)?.toDouble() ?? 0;
                          final required = (item['required_hours'] as num?)?.toDouble() ?? 0;
                          final progress = required <= 0 ? 0.0 : (hoursServed / required).clamp(0.0, 1.0);

                          return Material(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(16),
                              onTap: () async {
                                final id = item['id'] as int?;
                                if (id == null) return;
                                await Navigator.of(context).push(
                                  MaterialPageRoute(
                                    builder: (_) => GsoSanctionDetailScreen(assignmentId: id),
                                  ),
                                );
                                if (mounted) _load(forced: true);
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      name,
                                      style: GoogleFonts.inter(
                                        fontSize: 16,
                                        fontWeight: FontWeight.w700,
                                        color: AppTheme.textMain,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      '${caseMap['case_code'] ?? 'Case'} · ${caseMap['sanction'] ?? 'Community service'}',
                                      style: GoogleFonts.inter(
                                        fontSize: 13,
                                        color: AppTheme.textMuted,
                                      ),
                                    ),
                                    const SizedBox(height: 12),
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(999),
                                      child: LinearProgressIndicator(
                                        value: progress,
                                        minHeight: 8,
                                        backgroundColor: AppTheme.primaryLight,
                                        color: AppTheme.primary,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      '${hoursServed.toStringAsFixed(2)} / ${required.toStringAsFixed(2)} hrs',
                                      style: GoogleFonts.inter(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: AppTheme.textSub,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}
