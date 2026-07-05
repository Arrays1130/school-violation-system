import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../theme/app_theme.dart';
import 'case_details_screen.dart';
import 'main_layout.dart';
import '../providers/api_service_provider.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/empty_state_widget.dart';
import '../widgets/app_ui.dart';
import '../services/notification_poller.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => DashboardScreenState();
}

class DashboardScreenState extends ConsumerState<DashboardScreen> {
  List<dynamic> _violations = [];
  Map<String, dynamic> _stats = {};
  List<dynamic> _topOffenses = [];
  List<dynamic> _alerts = [];
  int _unreadCount = 0;
  bool _isLoading = true;
  String _userName = 'Dean';
  Timer? _autoRefreshTimer;

  @override
  void initState() {
    super.initState();
    _unreadCount = NotificationPoller.instance.unreadCount.value;
    NotificationPoller.instance.unreadCount.addListener(_syncUnreadFromPoller);
    _loadUserName();
    _loadInitialData();
    _autoRefreshTimer = Timer.periodic(
      const Duration(seconds: 45),
      (_) => _refreshData(showLoading: false),
    );
  }

  void _syncUnreadFromPoller() {
    if (mounted) {
      setState(
        () => _unreadCount = NotificationPoller.instance.unreadCount.value,
      );
    }
  }

  void refreshFromPoller() {
    if (!mounted) return;
    _refreshData(showLoading: false);
  }

  Future<void> _loadUserName() async {
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString('user');
    if (userJson == null) return;
    try {
      final user = jsonDecode(userJson) as Map<String, dynamic>;
      final name = (user['name'] ?? '').toString().trim();
      if (name.isNotEmpty && mounted) {
        setState(() => _userName = name.split(' ').first);
      }
    } catch (_) {}
  }

  Future<void> _loadInitialData() async {
    final api = ref.read(apiServiceProvider);
    final cachedViolations = await api.getPersistentCache('violations');
    final cachedStats = await api.getPersistentCache('stats');

    if ((cachedViolations != null || cachedStats != null) && mounted) {
      setState(() {
        if (cachedViolations != null) {
          if (cachedViolations is Map) {
            _violations = (cachedViolations['data'] ?? []) as List<dynamic>;
          } else if (cachedViolations is List) {
            _violations = cachedViolations;
          }
        }
        if (cachedStats != null) {
          _stats = Map<String, dynamic>.from(cachedStats['summary'] ?? {});
          _topOffenses = List<dynamic>.from(cachedStats['top_offenses'] ?? []);
          _alerts = List<dynamic>.from(cachedStats['upcoming_hearings'] ?? []);
        }
        _isLoading = false;
      });
    }

    await _refreshData(showLoading: _isLoading);
  }

  @override
  void dispose() {
    NotificationPoller.instance.unreadCount.removeListener(
      _syncUnreadFromPoller,
    );
    _autoRefreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _refreshData({bool showLoading = true}) async {
    if (showLoading && mounted) setState(() => _isLoading = true);

    try {
      final api = ref.read(apiServiceProvider);
      final vResult = await api.getViolations(forcedRefresh: true);
      final sResult = await api.getStats(forcedRefresh: true);

      if (!mounted) return;
      setState(() {
        if (vResult is Map) {
          _violations = (vResult['data'] ?? []) as List<dynamic>;
        } else if (vResult is List) {
          _violations = vResult;
        }
        _stats = Map<String, dynamic>.from(sResult['summary'] ?? {});
        _topOffenses = List<dynamic>.from(sResult['top_offenses'] ?? []);
        _alerts = List<dynamic>.from(sResult['upcoming_hearings'] ?? []);
        _unreadCount = NotificationPoller.instance.unreadCount.value;
        _isLoading = false;
      });
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  List<dynamic> get _recentViolations => _violations.take(5).toList();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _refreshData,
          color: AppTheme.primary,
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            slivers: [
              SliverToBoxAdapter(child: _buildHeader()),
              SliverToBoxAdapter(child: _buildHeroCard()),
              SliverToBoxAdapter(child: _buildStatsRow()),
              SliverToBoxAdapter(
                child: AppUi.searchBar(
                  hint: 'Search student or case...',
                  onTap: () {
                    HapticFeedback.lightImpact();
                    MainLayout.of(context)?.navigateToTab(1);
                  },
                ),
              ),
              if (_alerts.isNotEmpty) ...[
                SliverToBoxAdapter(
                  child: AppUi.sectionHeader('Upcoming hearings'),
                ),
                SliverToBoxAdapter(child: _buildHearingsRow()),
              ],
              if (_topOffenses.isNotEmpty) ...[
                SliverToBoxAdapter(child: AppUi.sectionHeader('Top offenses')),
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 8),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) =>
                          _buildOffenseItem(_topOffenses[index], index),
                      childCount: _topOffenses.length > 3
                          ? 3
                          : _topOffenses.length,
                    ),
                  ),
                ),
              ],
              SliverToBoxAdapter(
                child: AppUi.sectionHeader(
                  'Recent cases',
                  action: 'View all',
                  onAction: () => MainLayout.of(context)?.navigateToTab(1),
                ),
              ),
              if (_isLoading)
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  sliver: SliverToBoxAdapter(
                    child: ShimmerLoader.buildListSkeleton(),
                  ),
                )
              else if (_recentViolations.isEmpty)
                const SliverToBoxAdapter(
                  child: Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16, vertical: 24),
                    child: EmptyStateWidget(
                      icon: Icons.check_circle_outline_rounded,
                      title: 'All clear',
                      message: 'No recent violation records.',
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(
                    20,
                    0,
                    20,
                    AppTheme.bottomNavClearance,
                  ),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) =>
                          _buildCaseCard(_recentViolations[index]),
                      childCount: _recentViolations.length,
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return AppUi.pageHeader(
      greeting: 'Hello, $_userName',
      title: 'Dashboard',
      subtitle:
          'Monitor case volume, hearings, alerts, and recent offense activity.',
      badge: AppUi.iconCircle(
        icon: Icons.dashboard_outlined,
        color: AppTheme.primaryNavy,
        size: 36,
        iconSize: 18,
        backgroundColor: Colors.white,
      ),
      trailing: IconButton(
        onPressed: () => MainLayout.of(context)?.navigateToTab(3),
        icon: Stack(
          clipBehavior: Clip.none,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppTheme.bgCard,
                borderRadius: BorderRadius.circular(14),
                boxShadow: AppTheme.softShadow,
              ),
              child: const Icon(
                Icons.notifications_outlined,
                color: AppTheme.textMain,
                size: 22,
              ),
            ),
            if (_unreadCount > 0)
              Positioned(
                top: 6,
                right: 6,
                child: Container(
                  width: 10,
                  height: 10,
                  decoration: BoxDecoration(
                    color: AppTheme.accentRose,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppTheme.bgCard, width: 1.5),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroCard() {
    final total = _isLoading ? '—' : '${_stats['total'] ?? 0}';
    final pending = _isLoading ? '—' : '${_stats['pending'] ?? 0}';
    final closed = _isLoading ? '—' : '${_stats['resolved'] ?? 0}';

    return AppUi.heroMetricCard(
      eyebrow: 'VioTrack command center',
      label: 'Total cases',
      value: total,
      subtitle: _isLoading
          ? 'Loading overview…'
          : '$pending pending · $closed closed',
      badge: AppUi.iconCircle(
        icon: Icons.arrow_outward_rounded,
        color: Colors.white,
        size: 46,
        iconSize: 22,
        backgroundColor: Colors.white.withValues(alpha: 0.14),
      ),
      onTap: () {
        HapticFeedback.lightImpact();
        MainLayout.of(context)?.navigateToTab(1);
      },
    );
  }

  Widget _buildStatsRow() {
    if (_isLoading) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 4),
        child: ShimmerLoader.buildStatGridSkeleton(),
      );
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 4),
      child: Row(
        children: [
          AppUi.statChip(
            label: 'Pending',
            value: '${_stats['pending'] ?? 0}',
            icon: Icons.schedule_rounded,
            color: AppTheme.accentAmber,
            onTap: () {
              HapticFeedback.lightImpact();
              MainLayout.of(context)?.navigateToTab(1, status: 'Pending');
            },
          ),
          const SizedBox(width: 10),
          AppUi.statChip(
            label: 'Closed',
            value: '${_stats['resolved'] ?? 0}',
            icon: Icons.check_circle_outline_rounded,
            color: AppTheme.accentEmerald,
            onTap: () {
              HapticFeedback.lightImpact();
              MainLayout.of(context)?.navigateToTab(1, status: 'Closed');
            },
          ),
          const SizedBox(width: 10),
          AppUi.statChip(
            label: 'Alerts',
            value: '$_unreadCount',
            icon: Icons.notifications_outlined,
            color: AppTheme.accentRose,
            onTap: () {
              HapticFeedback.lightImpact();
              MainLayout.of(context)?.navigateToTab(3);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildHearingsRow() {
    return SizedBox(
      height: 194,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20),
        itemCount: _alerts.length > 5 ? 5 : _alerts.length,
        separatorBuilder: (context, index) => const SizedBox(width: 12),
        itemBuilder: (context, index) => _buildHearingCard(_alerts[index]),
      ),
    );
  }

  Widget _buildHearingCard(dynamic alert) {
    final caseId = alert['case_id'];
    final studentName =
        alert['case']?['student']?['full_name']?.toString() ?? 'Student';
    final violation =
        alert['case']?['violation']?['title']?.toString() ?? 'Hearing';
    final schedule = _formatDateTime(alert['scheduled_at']?.toString() ?? '');
    final venue = alert['venue']?.toString() ?? 'Guidance Office';

    return SizedBox(
      width: 276,
      child: Material(
        borderRadius: BorderRadius.circular(22),
        child: InkWell(
          onTap: () {
            if (caseId == null) return;
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) =>
                    CaseDetailsScreen(caseId: int.parse(caseId.toString())),
              ),
            );
          },
          borderRadius: BorderRadius.circular(22),
          child: Ink(
            decoration: BoxDecoration(
              gradient: AppTheme.heroGradient,
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
              boxShadow: AppTheme.floatShadow,
            ),
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        AppUi.brandPill(
                          label: 'Scheduled hearing',
                          leading: Icon(
                            Icons.gavel_rounded,
                            size: 14,
                            color: Colors.white.withValues(alpha: 0.92),
                          ),
                        ),
                        const Spacer(),
                        AppUi.iconCircle(
                          icon: Icons.arrow_outward_rounded,
                          color: Colors.white,
                          size: 36,
                          iconSize: 16,
                          backgroundColor: Colors.white.withValues(alpha: 0.12),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      studentName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.inter(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                        letterSpacing: -0.4,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      violation,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        height: 1.35,
                        color: Colors.white.withValues(alpha: 0.82),
                      ),
                    ),
                  ],
                ),
                Row(
                  children: [
                    Expanded(
                      child: _buildHearingMetaTile(
                        icon: Icons.calendar_today_rounded,
                        label: 'Schedule',
                        value: schedule,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: _buildHearingMetaTile(
                        icon: Icons.place_outlined,
                        label: 'Venue',
                        value: venue,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHearingMetaTile({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 12, color: Colors.white.withValues(alpha: 0.8)),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: Colors.white.withValues(alpha: 0.72),
                    letterSpacing: 0.2,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              height: 1.3,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOffenseItem(dynamic offense, int index) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: AppUi.cardDecoration(radius: AppTheme.radiusMd),
      child: Row(
        children: [
          Container(
            width: 28,
            height: 28,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: AppTheme.primaryLight,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              '${index + 1}',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w700,
                color: AppTheme.primary,
                fontSize: 12,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              offense['title']?.toString() ?? 'N/A',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w500,
                fontSize: 13,
                color: AppTheme.textMain,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          Text(
            '${offense['count'] ?? 0}',
            style: GoogleFonts.inter(
              fontWeight: FontWeight.w700,
              fontSize: 14,
              color: AppTheme.primary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCaseCard(dynamic violation) {
    final status = violation['status']?.toString() ?? 'Pending';
    final severity = violation['violation']?['severity']?.toString() ?? 'Minor';
    final studentName =
        violation['student']?['full_name']?.toString() ?? 'Unknown';
    final violationTitle =
        violation['violation']?['title']?.toString() ?? 'N/A';

    return AppUi.listRow(
      title: studentName,
      subtitle: violationTitle,
      icon: severity == 'Major'
          ? Icons.warning_amber_rounded
          : Icons.gavel_outlined,
      iconColor: _severityColor(severity),
      trailing: AppUi.statusBadge(status),
      onTap: () {
        HapticFeedback.lightImpact();
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => CaseDetailsScreen(
              caseId: violation['id'],
              initialData: {
                'id': violation['id'],
                'student': violation['student'],
                'violation': violation['violation'],
                'status': violation['status'],
              },
            ),
          ),
        );
      },
    );
  }

  Color _severityColor(String severity) {
    return severity == 'Major' ? AppTheme.accentAmber : AppTheme.primary;
  }

  String _formatDateTime(String dateTimeStr) {
    if (dateTimeStr.isEmpty) return 'Schedule TBA';
    try {
      final date = DateTime.parse(dateTimeStr).toLocal();
      const months = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
      ];
      final hour = date.hour > 12
          ? date.hour - 12
          : (date.hour == 0 ? 12 : date.hour);
      final minute = date.minute.toString().padLeft(2, '0');
      final ampm = date.hour >= 12 ? 'PM' : 'AM';
      return '${months[date.month - 1]} ${date.day} · $hour:$minute $ampm';
    } catch (_) {
      return dateTimeStr;
    }
  }
}
