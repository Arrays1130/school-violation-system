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
import '../widgets/skeleton_loader.dart';

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
    _refreshData();
    _autoRefreshTimer =
        Timer.periodic(const Duration(seconds: 30), (_) => _refreshData());
  }

  @override
  void dispose() {
    _autoRefreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _refreshData() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final dynamic vResult =
          await api.getViolations(forcedRefresh: true);
      final dynamic sResult =
          await api.getStats(forcedRefresh: true);
      final int uCount = await ref.read(apiServiceProvider).getUnreadCount();
      setState(() {
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
    } catch (e) {
      setState(() => _isLoading = false);
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
        color: AppTheme.accentPurple,
        child: CustomScrollView(
          slivers: [
            // ── Hero App Bar ──
            SliverAppBar(
              expandedHeight: 200,
              floating: false,
              pinned: true,
              elevation: 0,
              scrolledUnderElevation: 0,
              backgroundColor: Colors.transparent,
              flexibleSpace: ClipRect(
                child: BackdropFilter(
                  filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
                  child: FlexibleSpaceBar(
                    collapseMode: CollapseMode.pin,
                    background: Stack(
                      fit: StackFit.expand,
                      children: [
                        // Gradient base
                        Container(
                          decoration: const BoxDecoration(
                              gradient: AppTheme.heroGradient),
                        ),
                        // Glow orb top-right
                        Positioned(
                          top: -60,
                          right: -40,
                          child: Container(
                            width: 220,
                            height: 220,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: RadialGradient(colors: [
                                AppTheme.accentPurple.withOpacity(0.6),
                                Colors.transparent
                              ]),
                            ),
                          ),
                        ),
                        // Glow orb bottom-left
                        Positioned(
                          bottom: -30,
                          left: -20,
                          child: Container(
                            width: 160,
                            height: 160,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: RadialGradient(colors: [
                                AppTheme.accentCyan.withOpacity(0.4),
                                Colors.transparent
                              ]),
                            ),
                          ),
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
                                    fontSize: 13,
                                    color: Colors.white.withOpacity(0.65),
                                    fontWeight: FontWeight.w500),
                              ),
                              Text(
                                "Dean Dashboard",
                                style: GoogleFonts.outfit(
                                    fontSize: 26,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.white,
                                    letterSpacing: 0.3),
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color:
                                          Colors.white.withOpacity(0.15),
                                      borderRadius:
                                          BorderRadius.circular(6),
                                    ),
                                    child: Row(
                                      children: [
                                        Container(
                                          width: 6,
                                          height: 6,
                                          decoration: BoxDecoration(
                                            color: AppTheme.accentEmerald,
                                            shape: BoxShape.circle,
                                            boxShadow: [
                                              BoxShadow(
                                                  color: AppTheme
                                                      .accentEmerald
                                                      .withOpacity(0.8),
                                                  blurRadius: 4)
                                            ],
                                          ),
                                        ),
                                        const SizedBox(width: 5),
                                        Text("LIVE",
                                            style: GoogleFonts.outfit(
                                                fontSize: 9,
                                                fontWeight:
                                                    FontWeight.w900,
                                                color: Colors.white,
                                                letterSpacing: 1.5)),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
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
                      color: Colors.white, size: 20),
                  onPressed: () {
                    HapticFeedback.heavyImpact();
                    _logout();
                  },
                ),
                const SizedBox(width: 8),
              ],
            ),

            // ── Search Bar ──
            SliverToBoxAdapter(
              child: Padding(
                padding:
                    const EdgeInsets.fromLTRB(20, 20, 20, 0),
                child: GestureDetector(
                  onTap: () {
                    HapticFeedback.mediumImpact();
                    MainLayout.of(context)?.navigateToTab(1);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 13),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: AppTheme.softShadow,
                      border: Border.all(
                          color:
                              AppTheme.inputBorder.withOpacity(0.6)),
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: AppTheme.accentPurple.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(Icons.search_rounded,
                              color: AppTheme.accentPurple, size: 16),
                        ),
                        const SizedBox(width: 12),
                        Text("Search student or case...",
                            style: GoogleFonts.outfit(
                                color: AppTheme.textHint, fontSize: 14)),
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

            // ── Stat Cards ──
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

            // ── Upcoming Alerts ──
            if (_alerts.isNotEmpty) ...[
              SliverToBoxAdapter(
                  child: _buildSectionHeader(
                      "Upcoming Hearings",
                      Icons.event_note_rounded)),
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 150,
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

            // ── Top Offenses ──
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

            // ── Recent Records ──
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
                        ? SliverFillRemaining(
                            child: Center(
                              child: Column(
                                mainAxisAlignment:
                                    MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.description_outlined,
                                      size: 72,
                                      color: AppTheme.textHint
                                          .withOpacity(0.5)),
                                  const SizedBox(height: 12),
                                  Text("No records found",
                                      style: GoogleFonts.outfit(
                                          color: AppTheme.textMuted,
                                          fontSize: 14)),
                                ],
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

  // ── Notification Bell ───────────────────────────────────────────────────

  Widget _buildNotificationBell() {
    return Stack(
      alignment: Alignment.center,
      children: [
        IconButton(
          icon: const Icon(Icons.notifications_rounded,
              color: Colors.white, size: 22),
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

  // ── Section Header ─────────────────────────────────────────────────────

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
                    color: AppTheme.accentPurple.withOpacity(0.3),
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
              onTap: onTap,
              child: Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 12, vertical: 5),
                decoration: BoxDecoration(
                  color: AppTheme.accentPurple.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                      color:
                          AppTheme.accentPurple.withOpacity(0.2)),
                ),
                child: Text(
                  (actionLabel ?? "View All").toUpperCase(),
                  style: GoogleFonts.outfit(
                      fontSize: 10,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.accentPurple,
                      letterSpacing: 0.5),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ── Stat Card ──────────────────────────────────────────────────────────

  Widget _buildStatCard(String label, String count, IconData icon,
      LinearGradient gradient, VoidCallback onTap) {
    return Expanded(
      child: GestureDetector(
        onTap: () {
          HapticFeedback.mediumImpact();
          onTap();
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 12),
          decoration: BoxDecoration(
            gradient: gradient,
            borderRadius: BorderRadius.circular(22),
            boxShadow: [
              BoxShadow(
                  color: gradient.colors.first.withOpacity(0.3),
                  blurRadius: 16,
                  offset: const Offset(0, 8)),
            ],
          ),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: Colors.white, size: 18),
              ),
              const SizedBox(height: 10),
              Text(count,
                  style: GoogleFonts.outfit(
                      fontSize: 24,
                      fontWeight: FontWeight.w900,
                      color: Colors.white)),
              Text(label,
                  style: GoogleFonts.outfit(
                      color: Colors.white.withOpacity(0.75),
                      fontSize: 9,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 0.6)),
            ],
          ),
        ),
      ),
    );
  }

  // ── Alert Card ─────────────────────────────────────────────────────────

  Widget _buildAlertCard(dynamic alert) {
    return Container(
      width: 240,
      margin: const EdgeInsets.only(right: 14),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: AppTheme.accentGradient,
        borderRadius: BorderRadius.circular(22),
        boxShadow: AppTheme.glassShadow,
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
              const Icon(Icons.arrow_forward_rounded,
                  color: Colors.white, size: 14),
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
                fontSize: 16),
          ),
          const SizedBox(height: 3),
          Text(
            alert['case']?['violation']?['title'] ?? 'N/A',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.outfit(
                color: Colors.white.withOpacity(0.7),
                fontSize: 11,
                fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }

  // ── Top Offense Item ───────────────────────────────────────────────────

  Widget _buildTopOffenseItem(dynamic offense, int index) {
    final colors = [
      AppTheme.accentPurple,
      AppTheme.accentIndigo,
      AppTheme.accentCyan,
      AppTheme.accentEmerald,
      AppTheme.accentAmber,
    ];
    final color = colors[index % colors.length];

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: AppTheme.softShadow,
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
        .fadeIn(delay: Duration(milliseconds: 80 * index))
        .slideX(begin: 0.02);
  }

  // ── Violation Card ─────────────────────────────────────────────────────

  Widget _buildViolationCard(dynamic violation, int index) {
    final status = violation['status'] ?? 'Pending';
    final severity = violation['violation']?['severity'] ?? 'Minor';

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: AppTheme.softShadow,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(24),
          onTap: () {
            HapticFeedback.mediumImpact();
            Navigator.push(
                context,
                MaterialPageRoute(
                    builder: (context) =>
                        CaseDetailsScreen(caseId: violation['id'])));
          },
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
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
        .fadeIn(delay: Duration(milliseconds: 80 * index))
        .slideY(begin: 0.05);
  }

  Widget _buildStatusBadge(String status) {
    Color color = AppTheme.accentAmber;
    if (status == 'Resolved' || status == 'Closed') {
      color = AppTheme.accentEmerald;
    }
    if (status == 'Hearing Scheduled') color = AppTheme.accentPurple;

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
    return AppTheme.accentIndigo;
  }

  LinearGradient _getSeverityGradient(String severity) {
    if (severity == 'Major') return AppTheme.warmGradient;
    return AppTheme.accentGradient;
  }

  IconData _getSeverityIcon(String severity) {
    if (severity == 'Major') return Icons.error_outline_rounded;
    return Icons.info_outline_rounded;
  }
}
