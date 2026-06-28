import 'dart:async';
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart'; // Riverpod
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter/services.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';
import 'case_details_screen.dart';
import 'notification_screen.dart';
import 'analytics_screen.dart';
import 'main_layout.dart';
import '../providers/api_service_provider.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/empty_state_widget.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  // We'll use Riverpod provider for ApiService
  List<dynamic> _violations = [];
  Map<String, dynamic> _stats = {};
  List<dynamic> _topOffenses = [];
  List<dynamic> _alerts = [];
  int _unreadCount = 0;
  bool _isLoading = true;
  Timer? _autoRefreshTimer;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
    _autoRefreshTimer =
        Timer.periodic(const Duration(seconds: 30), (_) => _refreshData(showLoading: false));
  }

  Future<void> _loadInitialData() async {
    final api = ref.read(apiServiceProvider);
    final cachedViolations = await api.getPersistentCache('violations');
    final cachedStats = await api.getPersistentCache('stats');
    
    if (cachedViolations != null || cachedStats != null) {
      if (mounted) {
        if (mounted) setState(() {
          if (cachedViolations != null) {
            if (cachedViolations is Map) {
              _violations = (cachedViolations['data'] ?? []) as List<dynamic>;
            } else if (cachedViolations is List) {
              _violations = cachedViolations;
            }
          }
          if (cachedStats != null) {
            _stats = cachedStats['summary'] ?? {};
            _topOffenses = cachedStats['top_offenses'] ?? [];
            _alerts = cachedStats['upcoming_hearings'] ?? [];
          }
          _isLoading = false;
        });
      }
    }
    
    await _refreshData(showLoading: _isLoading);
  }

  @override
  void dispose() {
    _autoRefreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _refreshData({bool showLoading = true}) async {
    if (showLoading && mounted) {
      if (mounted) setState(() => _isLoading = true);
    }
    try {
      final api = ref.read(apiServiceProvider);
      final dynamic vResult =
          await api.getViolations(forcedRefresh: true);
      final dynamic sResult =
          await api.getStats(forcedRefresh: true);
      final int uCount = await api.getUnreadCount();
      if (mounted) {
        if (mounted) setState(() {
          if (vResult is Map) {
            _violations = (vResult['data'] ?? []) as List<dynamic>;
          } else if (vResult is List) {
            _violations = vResult;
          }
          _stats = sResult['summary'] ?? {};
          _topOffenses = sResult['top_offenses'] ?? [];
          _alerts = sResult['upcoming_hearings'] ?? [];
          _unreadCount = uCount;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        if (mounted) setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString().replaceAll('Exception: ', ''), style: GoogleFonts.outfit()),
            backgroundColor: Colors.redAccent,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _logout() async {
    await ref.read(apiServiceProvider).logout();
    Navigator.pushReplacement(
        context, MaterialPageRoute(builder: (context) => LoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: RefreshIndicator(
        onRefresh: _refreshData,
        color: AppTheme.accentCyan,
        child: CustomScrollView(
          slivers: [
            // â”€â”€ Hero App Bar â”€â”€
            SliverAppBar(
              expandedHeight: 220,
              floating: false,
              pinned: true,
              elevation: 0,
              scrolledUnderElevation: 0,
              backgroundColor: Colors.transparent,
              flexibleSpace: ClipRect(
                child: BackdropFilter(
                  filter: ImageFilter.blur(sigmaX: 30, sigmaY: 30),
                  child: FlexibleSpaceBar(
                    collapseMode: CollapseMode.pin,
                    background: Stack(
                      fit: StackFit.expand,
                      children: [
                        // Clean minimalist white background
                        Container(
                          color: AppTheme.bgLight,
                        ),
                        // Content
                        Padding(
                          padding: const EdgeInsets.fromLTRB(24, 64, 24, 24),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.end,
                            children: [
                              Text(
                                "Welcome back,",
                                style: GoogleFonts.outfit(
                                    fontSize: 16,
                                    color: AppTheme.textSub,
                                    letterSpacing: 0.5,
                                    fontWeight: FontWeight.w600),
                              ).animate().fadeIn().slideY(begin: 0.2, end: 0),
                              Text(
                                "Dean Dashboard",
                                style: GoogleFonts.outfit(
                                  fontSize: 34,
                                  fontWeight: FontWeight.w800, // Slightly less heavy, modern look
                                  color: AppTheme.primaryNavy,
                                  letterSpacing: -1.0,
                                ),
                              ).animate().fadeIn(delay: 100.ms).slideY(begin: 0.2, end: 0),

                            ],
                          ),
                        ),
                      ],
                    ),
                    title: null,
                  ),
                ),
              ),
              actions: [
                _buildNotificationBell(),
                IconButton(
                  icon: const Icon(Icons.logout_rounded,
                      color: AppTheme.primaryNavy, size: 20),
                  onPressed: () {
                    HapticFeedback.heavyImpact();
                    _logout();
                  },
                ),
                const SizedBox(width: 8),
              ],
            ),

            // â”€â”€ Search Bar â”€â”€
            SliverToBoxAdapter(
              child: Padding(
                padding:
                    const EdgeInsets.fromLTRB(20, 20, 20, 0),
                child: GestureDetector(
                  behavior: HitTestBehavior.opaque,
                  onTap: () {
                    HapticFeedback.mediumImpact();
                    MainLayout.of(context)?.navigateToTab(1);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 20, vertical: 16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: AppTheme.softShadow,
                      border: Border.all(
                          color:
                              Colors.white),
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: AppTheme.accentCyan.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(Icons.search_rounded,
                              color: AppTheme.accentCyan, size: 16),
                        ),
                        const SizedBox(width: 12),
                        Text("Search student or case...",
                            style: GoogleFonts.outfit(
                                color: AppTheme.textHint, fontSize: 15, fontWeight: FontWeight.w500)),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            gradient: AppTheme.accentGradient,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text("EXPLORE",
                              style: GoogleFonts.outfit(
                                  fontSize: 9,
                                  fontWeight: FontWeight.w900,
                                  color: Colors.white,
                                  letterSpacing: 0.8)),
                        ),
                      ],
                    ),
                  ),
                ),
              ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.1),
            ),

            // â”€â”€ Stat Cards â”€â”€
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
                child: _isLoading
                    ? ShimmerLoader.buildStatGridSkeleton()
                    : Row(
                        children: [
                          _buildStatCard(
                            "TOTAL",
                            "${_stats['total'] ?? 0}",
                            Icons.analytics_rounded,
                            AppTheme.accentGradient,
                            () => MainLayout.of(context)
                                ?.navigateToTab(1, status: 'All'),
                          ),
                          const SizedBox(width: 12),
                          _buildStatCard(
                            "PENDING",
                            "${_stats['pending'] ?? 0}",
                            Icons.pending_actions_rounded,
                            AppTheme.warmGradient,
                            () => MainLayout.of(context)
                                ?.navigateToTab(1, status: 'Pending'),
                          ),
                          const SizedBox(width: 12),
                          _buildStatCard(
                            "CLOSED",
                            "${_stats['resolved'] ?? 0}",
                            Icons.verified_rounded,
                            AppTheme.successGradient,
                            () => MainLayout.of(context)
                                ?.navigateToTab(1, status: 'Resolved'),
                          ),
                        ],
                      ),
              ).animate().fadeIn(duration: 600.ms).slideY(begin: 0.05),
            ),

            // â”€â”€ Upcoming Alerts â”€â”€
            if (_alerts.isNotEmpty) ...[
              SliverToBoxAdapter(
                  child: _buildSectionHeader(
                      "Upcoming Hearings",
                      Icons.event_note_rounded)),
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 190,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    padding:
                        const EdgeInsets.symmetric(horizontal: 20),
                    itemCount: _alerts.length,
                    itemBuilder: (context, index) =>
                        _buildAlertCard(_alerts[index]),
                  ),
                ).animate().fadeIn(delay: 200.ms),
              ),
            ],

            // â”€â”€ Top Offenses â”€â”€
            if (_topOffenses.isNotEmpty) ...[
              SliverToBoxAdapter(
                  child: _buildSectionHeader(
                      "Top Offenses",
                      Icons.bar_chart_rounded)),
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) =>
                        _buildTopOffenseItem(_topOffenses[index], index),
                    childCount: _topOffenses.length,
                  ),
                ),
              ),
            ],

            // â”€â”€ Recent Records â”€â”€
            SliverToBoxAdapter(
              child: _buildSectionHeader(
                "Recent Records",
                Icons.history_rounded,
                onTap: () {
                  HapticFeedback.mediumImpact();
                  Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (context) => AnalyticsScreen()));
                },
                actionLabel: "Analytics",
              ),
            ),
            _isLoading
                ? SliverPadding(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 20),
                    sliver: SliverToBoxAdapter(
                      child: ShimmerLoader.buildListSkeleton(),
                    ),
                  )
                : SliverPadding(
                    padding: const EdgeInsets.fromLTRB(20, 0, 20, 100),
                    sliver: _violations.isEmpty
                        ? const SliverFillRemaining(
                            child: Center(
                              child: EmptyStateWidget(
                                icon: Icons.check_circle_outline_rounded,
                                title: "All Clear!",
                                message: "There are no recent violations in your records.",
                              ),
                            ),
                          )
                        : SliverList(
                            delegate: SliverChildBuilderDelegate(
                              (context, index) => _buildViolationCard(
                                  _violations[index], index),
                              childCount: _violations.length,
                            ),
                          ),
                  ),
          ],
        ),
      ),
    );
  }

  // â”€â”€ Notification Bell â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  Widget _buildNotificationBell() {
    return Stack(
      alignment: Alignment.center,
      children: [
        IconButton(
          icon: const Icon(Icons.notifications_outlined, color: AppTheme.primaryNavy, size: 22),
          onPressed: () {
            HapticFeedback.lightImpact();
            Navigator.push(
              context,
              MaterialPageRoute(
                  builder: (context) => NotificationScreen()),
            ).then((_) => _refreshData());
          },
        ),
        if (_unreadCount > 0)
          Positioned(
            right: 10,
            top: 10,
            child: Container(
              padding: const EdgeInsets.all(3),
              decoration: BoxDecoration(
                gradient: AppTheme.warmGradient,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 1.5),
              ),
              constraints:
                  const BoxConstraints(minWidth: 16, minHeight: 16),
              child: Text(
                _unreadCount > 9 ? "!" : "$_unreadCount",
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 8,
                    fontWeight: FontWeight.w900),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }

  // â”€â”€ Section Header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  Widget _buildSectionHeader(String title, IconData icon,
      {VoidCallback? onTap, String? actionLabel}) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 28, 20, 14),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              gradient: AppTheme.accentGradient,
              borderRadius: BorderRadius.circular(10),
              boxShadow: [
                BoxShadow(
                    color: AppTheme.accentCyan.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 4)),
              ],
            ),
            child: Icon(icon, size: 14, color: Colors.white),
          ),
          const SizedBox(width: 12),
          Text(
            title.toUpperCase(),
            style: GoogleFonts.outfit(
                fontSize: 11,
                fontWeight: FontWeight.w900,
                color: AppTheme.textSub,
                letterSpacing: 1.2),
          ),
          if (onTap != null) ...[
            const Spacer(),
            GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: onTap,
              child: Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 12, vertical: 5),
                decoration: BoxDecoration(
                  color: AppTheme.accentCyan.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                      color:
                          AppTheme.accentCyan.withOpacity(0.2)),
                ),
                child: Text(
                  (actionLabel ?? "View All").toUpperCase(),
                  style: GoogleFonts.outfit(
                      fontSize: 10,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.accentCyan,
                      letterSpacing: 0.5),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // â”€â”€ Stat Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  Widget _buildStatCard(String label, String count, IconData icon,
      LinearGradient gradient, VoidCallback onTap) {
    Color baseColor = gradient.colors.first;
    return Expanded(
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: () {
          HapticFeedback.lightImpact();
          onTap();
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 22, horizontal: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: baseColor.withOpacity(0.3), width: 2),
            boxShadow: [
              BoxShadow(
                  color: AppTheme.primaryNavy.withOpacity(0.04),
                  blurRadius: 16,
                  offset: const Offset(0, 8)),
            ],
          ),
          child: Column(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: baseColor.withOpacity(0.1),
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: baseColor.withOpacity(0.15),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Center(
                  child: Icon(icon, color: baseColor, size: 22),
                ),
              ),
              const SizedBox(height: 16),
              Text(count,
                  style: GoogleFonts.outfit(
                      fontSize: 30,
                      height: 1.0,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -1.0,
                      color: AppTheme.textMain)),
              const SizedBox(height: 4),
              Text(label,
                  style: GoogleFonts.outfit(
                      color: AppTheme.textSub,
                      fontSize: 9,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.5)),
            ],
          ),
        ),
      ),
    );
  }

  // â”€â”€ Alert Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  Widget _buildAlertCard(dynamic alert) {
    final caseId = alert['case_id'];
    return GestureDetector(
      onTap: () {
        HapticFeedback.lightImpact();
        if (caseId != null) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => CaseDetailsScreen(caseId: int.parse(caseId.toString())),
            ),
          ).then((_) => _refreshData());
        }
      },
      child: Container(
        width: 250,
        margin: const EdgeInsets.only(right: 14),
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: AppTheme.heroGradient,
          borderRadius: BorderRadius.circular(24),
          boxShadow: AppTheme.glassShadow,
          border: Border.all(color: Colors.white.withOpacity(0.15), width: 1),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(8)),
                  child: Text("HEARING",
                      style: GoogleFonts.outfit(
                          fontSize: 8,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                          letterSpacing: 1.2)),
                ),
                const Spacer(),
                const Icon(Icons.arrow_forward_ios_rounded,
                    color: Colors.white, size: 12),
              ],
            ),
            const Spacer(),
            Text(
              alert['case']?['student']?['full_name'] ?? 'Student',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: GoogleFonts.outfit(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 15),
            ),
            const SizedBox(height: 2),
            Text(
              alert['case']?['violation']?['title'] ?? 'N/A',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: GoogleFonts.outfit(
                  color: Colors.white.withOpacity(0.8),
                  fontSize: 11,
                  fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                const Icon(Icons.calendar_month_rounded, color: Colors.white70, size: 12),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    _formatDateTime(alert['scheduled_at']?.toString() ?? ''),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.outfit(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            Row(
              children: [
                const Icon(Icons.place_rounded, color: Colors.white70, size: 12),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    alert['venue']?.toString() ?? 'Guidance Office',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.outfit(
                        color: Colors.white.withOpacity(0.85),
                        fontSize: 10,
                        fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // â”€â”€ Top Offense Item â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  Widget _buildTopOffenseItem(dynamic offense, int index) {
    final colors = [
      AppTheme.accentCyan,
      AppTheme.primaryNavy,
      AppTheme.accentCyan,
      AppTheme.accentEmerald,
      AppTheme.accentAmber,
    ];
    final color = colors[index % colors.length];

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: AppTheme.softShadow,
        border: Border.all(color: AppTheme.inputBorder.withOpacity(0.5)),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Center(
              child: Text("${index + 1}",
                  style: GoogleFonts.outfit(
                      fontWeight: FontWeight.w900,
                      color: color,
                      fontSize: 14)),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(offense['title'] ?? 'N/A',
                    style: GoogleFonts.outfit(
                        fontWeight: FontWeight.w700,
                        color: AppTheme.textMain,
                        fontSize: 13)),
                Text("Department Records",
                    style: GoogleFonts.outfit(
                        fontSize: 10, color: AppTheme.textMuted)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text("${offense['count'] ?? 0}",
                style: GoogleFonts.outfit(
                    color: color,
                    fontSize: 13,
                    fontWeight: FontWeight.w900)),
          ),
        ],
      ),
    ).animate()
        .fadeIn(delay: Duration(milliseconds: 80 * (index > 4 ? 4 : index)))
        .slideX(begin: 0.02);
  }

  // â”€â”€ Violation Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  Widget _buildViolationCard(dynamic violation, int index) {
    final status = violation['status'] ?? 'Pending';
    final severity = violation['violation']?['severity'] ?? 'Minor';

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
        boxShadow: AppTheme.softShadow,
        border: Border.all(color: AppTheme.inputBorder.withOpacity(0.3), width: 1),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(28),
          onTap: () {
            HapticFeedback.mediumImpact();
            Navigator.push(
                context,
                PageRouteBuilder(
                  transitionDuration: const Duration(milliseconds: 500),
                  reverseTransitionDuration: const Duration(milliseconds: 400),
                  transitionsBuilder:
                      (context, animation, secondaryAnimation, child) {
                    final fadeAnimation =
                        Tween<double>(begin: 0.0, end: 1.0).animate(
                      CurvedAnimation(
                        parent: animation,
                        curve: Curves.easeOutCubic,
                      ),
                    );
                    final scaleAnimation =
                        Tween<double>(begin: 0.95, end: 1.0).animate(
                      CurvedAnimation(
                        parent: animation,
                        curve: Curves.easeOutCubic,
                      ),
                    );
                    return FadeTransition(
                      opacity: fadeAnimation,
                      child: ScaleTransition(
                        scale: scaleAnimation,
                        child: child,
                      ),
                    );
                  },
                  pageBuilder: (context, animation, secondaryAnimation) =>
                      CaseDetailsScreen(
                        caseId: violation['id'],
                        initialData: {
                          'id': violation['id'],
                          'student': violation['student'],
                          'violation': violation['violation'],
                          'status': violation['status'],
                        },
                      ),
                ));
          },
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Hero(
                      tag: 'case_${violation['id']}_avatar',
                      child: Container(
                        width: 46,
                        height: 46,
                        decoration: BoxDecoration(
                          gradient: _getSeverityGradient(severity),
                          borderRadius: BorderRadius.circular(14),
                          boxShadow: [
                            BoxShadow(
                                color: _getSeverityColor(severity)
                                    .withOpacity(0.3),
                                blurRadius: 10,
                                offset: const Offset(0, 4)),
                          ],
                        ),
                        child: Icon(_getSeverityIcon(severity),
                            color: Colors.white, size: 20),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            violation['student']?['full_name'] ??
                                'Unknown',
                            style: GoogleFonts.outfit(
                                fontWeight: FontWeight.w800,
                                fontSize: 15,
                                color: AppTheme.textMain),
                          ),
                          Text(
                            "Case #${violation['id']}",
                            style: GoogleFonts.outfit(
                                fontSize: 10,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.textHint),
                          ),
                        ],
                      ),
                    ),
                    _buildStatusBadge(status),
                  ],
                ),
                const SizedBox(height: 14),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    color: AppTheme.bgLight,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.gavel_rounded,
                          size: 14, color: AppTheme.textMuted),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          violation['violation']?['title'] ?? 'N/A',
                          style: GoogleFonts.outfit(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: AppTheme.textSub),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const Icon(Icons.chevron_right_rounded,
                          size: 16, color: AppTheme.textHint),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    ).animate()
        .fadeIn(delay: Duration(milliseconds: 80 * (index > 4 ? 4 : index)))
        .slideY(begin: 0.05);
  }

  Widget _buildStatusBadge(String status) {
    Color color = AppTheme.accentAmber;
    if (status == 'Resolved' || status == 'Closed') {
      color = AppTheme.accentEmerald;
    }
    if (status == 'Hearing Scheduled') color = AppTheme.accentCyan;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
          color: color.withOpacity(0.1), borderRadius: BorderRadius.circular(10)),
      child: Text(status.toUpperCase(),
          style: GoogleFonts.outfit(
              fontSize: 8,
              fontWeight: FontWeight.w900,
              color: color,
              letterSpacing: 0.5)),
    );
  }

  Color _getSeverityColor(String severity) {
    if (severity == 'Major') return AppTheme.accentAmber;
    return AppTheme.primaryNavy;
  }

  LinearGradient _getSeverityGradient(String severity) {
    if (severity == 'Major') return AppTheme.warmGradient;
    return AppTheme.accentGradient;
  }

  IconData _getSeverityIcon(String severity) {
    if (severity == 'Major') return Icons.error_outline_rounded;
    return Icons.info_outline_rounded;
  }

  String _formatDateTime(String dateTimeStr) {
    if (dateTimeStr.isEmpty) return 'Date & Time TBA';
    try {
      final date = DateTime.parse(dateTimeStr).toLocal();
      final months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      final hour = date.hour > 12 ? date.hour - 12 : (date.hour == 0 ? 12 : date.hour);
      final minute = date.minute.toString().padLeft(2, '0');
      final ampm = date.hour >= 12 ? 'PM' : 'AM';
      return "${months[date.month - 1]} ${date.day}, ${date.year} at $hour:$minute $ampm";
    } catch (e) {
      return dateTimeStr;
    }
  }
}
