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

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
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
    _loadUserName();
    _loadInitialData();
    _autoRefreshTimer = Timer.periodic(
      const Duration(seconds: 45),
      (_) => _refreshData(showLoading: false),
    );
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
    _autoRefreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _refreshData({bool showLoading = true}) async {
    if (showLoading && mounted) setState(() => _isLoading = true);

    try {
      final api = ref.read(apiServiceProvider);
      final vResult = await api.getViolations(forcedRefresh: true);
      final sResult = await api.getStats(forcedRefresh: true);
      final uCount = await api.getUnreadCount();

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
        _unreadCount = uCount;
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
              SliverToBoxAdapter(child: _buildSearchBar()),
              SliverToBoxAdapter(child: _buildStatsRow()),
              if (_alerts.isNotEmpty) ...[
                SliverToBoxAdapter(child: _sectionTitle('Upcoming Hearings')),
                SliverToBoxAdapter(child: _buildHearingsRow()),
              ],
              if (_topOffenses.isNotEmpty) ...[
                SliverToBoxAdapter(child: _sectionTitle('Top Offenses')),
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) => _buildOffenseItem(_topOffenses[index], index),
                      childCount: _topOffenses.length > 3 ? 3 : _topOffenses.length,
                    ),
                  ),
                ),
              ],
              SliverToBoxAdapter(
                child: _sectionTitle(
                  'Recent Cases',
                  action: 'View all',
                  onAction: () => MainLayout.of(context)?.navigateToTab(1),
                ),
              ),
              if (_isLoading)
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  sliver: SliverToBoxAdapter(child: ShimmerLoader.buildListSkeleton()),
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
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) => _buildCaseCard(_recentViolations[index]),
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
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 12, 12, 4),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Hello, $_userName',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    color: AppTheme.textMuted,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Dashboard',
                  style: GoogleFonts.inter(
                    fontSize: 26,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMain,
                    letterSpacing: -0.5,
                  ),
                ),
              ],
            ),
          ),
          IconButton(
            onPressed: () => MainLayout.of(context)?.navigateToTab(3),
            icon: Stack(
              clipBehavior: Clip.none,
              children: [
                const Icon(Icons.notifications_outlined, color: AppTheme.textMain),
                if (_unreadCount > 0)
                  Positioned(
                    top: 0,
                    right: 0,
                    child: Container(
                      width: 8,
                      height: 8,
                      decoration: const BoxDecoration(
                        color: AppTheme.accentRose,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        elevation: 0,
        child: InkWell(
          onTap: () {
            HapticFeedback.lightImpact();
            MainLayout.of(context)?.navigateToTab(1);
          },
          borderRadius: BorderRadius.circular(14),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppTheme.inputBorder),
            ),
            child: Row(
              children: [
                const Icon(Icons.search, size: 20, color: AppTheme.textMuted),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Search student or case...',
                    style: GoogleFonts.inter(color: AppTheme.textHint, fontSize: 14),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatsRow() {
    if (_isLoading) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
        child: ShimmerLoader.buildStatGridSkeleton(),
      );
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Row(
        children: [
          _statChip('Total', '${_stats['total'] ?? 0}', AppTheme.primary, Icons.folder_outlined,
              () => MainLayout.of(context)?.navigateToTab(1)),
          const SizedBox(width: 8),
          _statChip('Pending', '${_stats['pending'] ?? 0}', AppTheme.accentAmber, Icons.schedule,
              () => MainLayout.of(context)?.navigateToTab(1, status: 'Pending')),
          const SizedBox(width: 8),
          _statChip('Closed', '${_stats['resolved'] ?? 0}', AppTheme.accentEmerald, Icons.check_circle_outline,
              () => MainLayout.of(context)?.navigateToTab(1, status: 'Closed')),
        ],
      ),
    );
  }

  Widget _statChip(String label, String value, Color color, IconData icon, VoidCallback onTap) {
    return Expanded(
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: () {
            HapticFeedback.lightImpact();
            onTap();
          },
          borderRadius: BorderRadius.circular(14),
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppTheme.inputBorder),
            ),
            child: Column(
              children: [
                Icon(icon, size: 18, color: color),
                const SizedBox(height: 8),
                Text(
                  value,
                  style: GoogleFonts.inter(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMain,
                    height: 1,
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

  Widget _sectionTitle(String title, {String? action, VoidCallback? onAction}) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 16, 10),
      child: Row(
        children: [
          Text(
            title,
            style: GoogleFonts.inter(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: AppTheme.textMain,
            ),
          ),
          if (action != null && onAction != null) ...[
            const Spacer(),
            TextButton(
              onPressed: onAction,
              style: TextButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 8),
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              child: Text(
                action,
                style: GoogleFonts.inter(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.primary,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildHearingsRow() {
    return SizedBox(
      height: 130,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: _alerts.length > 5 ? 5 : _alerts.length,
        separatorBuilder: (_, __) => const SizedBox(width: 10),
        itemBuilder: (context, index) => _buildHearingCard(_alerts[index]),
      ),
    );
  }

  Widget _buildHearingCard(dynamic alert) {
    final caseId = alert['case_id'];
    final studentName = alert['case']?['student']?['full_name']?.toString() ?? 'Student';
    final violation = alert['case']?['violation']?['title']?.toString() ?? 'Hearing';
    final schedule = _formatDateTime(alert['scheduled_at']?.toString() ?? '');
    final venue = alert['venue']?.toString() ?? 'Guidance Office';

    return SizedBox(
      width: 220,
      child: Material(
        color: AppTheme.primaryLight,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: () {
            if (caseId == null) return;
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => CaseDetailsScreen(caseId: int.parse(caseId.toString())),
              ),
            );
          },
          borderRadius: BorderRadius.circular(14),
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppTheme.primary.withValues(alpha: 0.15)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppTheme.primary.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    'Hearing',
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.primary,
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  studentName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textMain,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  violation,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMuted),
                ),
                const Spacer(),
                Text(
                  schedule,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textSub),
                ),
                Text(
                  venue,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textHint),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildOffenseItem(dynamic offense, int index) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.inputBorder),
      ),
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
    final studentName = violation['student']?['full_name']?.toString() ?? 'Unknown';
    final violationTitle = violation['violation']?['title']?.toString() ?? 'N/A';

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppTheme.inputBorder),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
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
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    color: _severityColor(severity).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    severity == 'Major' ? Icons.warning_amber_rounded : Icons.info_outline,
                    color: _severityColor(severity),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        studentName,
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.w600,
                          fontSize: 14,
                          color: AppTheme.textMain,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        violationTitle,
                        style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMuted),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                _statusPill(status),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _statusPill(String status) {
    Color color = AppTheme.accentAmber;
    if (status == 'Closed' || status == 'Resolved') color = AppTheme.accentEmerald;
    if (status.contains('Hearing')) color = AppTheme.primary;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        status.length > 10 ? status.substring(0, 8) + '…' : status,
        style: GoogleFonts.inter(
          fontSize: 10,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }

  Color _severityColor(String severity) {
    return severity == 'Major' ? AppTheme.accentAmber : AppTheme.primary;
  }

  String _formatDateTime(String dateTimeStr) {
    if (dateTimeStr.isEmpty) return 'Schedule TBA';
    try {
      final date = DateTime.parse(dateTimeStr).toLocal();
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      final hour = date.hour > 12 ? date.hour - 12 : (date.hour == 0 ? 12 : date.hour);
      final minute = date.minute.toString().padLeft(2, '0');
      final ampm = date.hour >= 12 ? 'PM' : 'AM';
      return '${months[date.month - 1]} ${date.day} · $hour:$minute $ampm';
    } catch (_) {
      return dateTimeStr;
    }
  }
}
