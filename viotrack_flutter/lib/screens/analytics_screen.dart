import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/skeleton_loader.dart';

class AnalyticsScreen extends StatefulWidget {
  @override
  _AnalyticsScreenState createState() => _AnalyticsScreenState();
}

class _AnalyticsScreenState extends State<AnalyticsScreen> {
  final ApiService _apiService = ApiService();

  Map<String, dynamic>? _stats;
  bool _isLoading = true;
  int? _touchedIndex;

  // Computed from violations
  int _totalCases = 0;
  int _pendingCases = 0;
  int _resolvedCases = 0;
  int _majorCases = 0;
  int _minorCases = 0;
  List<MapEntry<String, int>> _topViolations = [];
  List<MapEntry<String, int>> _repeatOffenders = [];
  List<Map<String, dynamic>> _monthlyTrend = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData({bool forcedRefresh = false}) async {
    if (mounted) setState(() => _isLoading = true);
    try {
      final analytics = await _apiService.getAnalytics(forcedRefresh: forcedRefresh);
      _applyAnalytics(analytics);
      if (mounted) setState(() => _isLoading = false);
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _applyAnalytics(dynamic analytics) {
    if (analytics is! Map) return;

    final summary = Map<String, dynamic>.from(analytics['summary'] ?? {});
    _totalCases = (summary['total'] as num?)?.toInt() ?? 0;
    _pendingCases = (summary['pending'] as num?)?.toInt() ?? 0;
    _resolvedCases = (summary['resolved'] as num?)?.toInt() ?? 0;
    _majorCases = (summary['major'] as num?)?.toInt() ?? 0;
    _minorCases = (summary['minor'] as num?)?.toInt() ?? 0;

    final topRaw = analytics['top_violations'] as List<dynamic>? ?? [];
    _topViolations = topRaw
        .map((e) => MapEntry(
              (e['title'] ?? 'Unknown').toString(),
              (e['count'] as num?)?.toInt() ?? 0,
            ))
        .toList();

    final repeatRaw = analytics['repeat_offenders'] as List<dynamic>? ?? [];
    _repeatOffenders = repeatRaw
        .map((e) => MapEntry(
              (e['name'] ?? 'Unknown').toString(),
              (e['count'] as num?)?.toInt() ?? 0,
            ))
        .toList();

    final trendRaw = analytics['monthly_trends'] as List<dynamic>? ?? [];
    _monthlyTrend = trendRaw
        .map((e) => {
              'month': e['month']?.toString() ?? '',
              'count': (e['count'] as num?)?.toInt() ?? 0,
            })
        .toList();

    _stats = Map<String, dynamic>.from(analytics);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      appBar: AppBar(
        title: Text('Analytics',
            style: GoogleFonts.outfit(
                fontWeight: FontWeight.w900,
                fontSize: 22,
                color: AppTheme.primaryNavy)),
        backgroundColor: AppTheme.bgLight,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: AppTheme.primaryNavy),
            onPressed: () => _loadData(forcedRefresh: true),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => _loadData(forcedRefresh: true),
        color: AppTheme.primaryNavy,
        child: _isLoading
            ? _buildSkeleton()
            : _totalCases == 0
                ? _buildErrorState()
                : _buildContent(),
      ),
    );
  }

  Widget _buildSkeleton() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          Row(
            children: [
              Expanded(child: ShimmerLoader.rounded(height: 120, width: double.infinity)),
              const SizedBox(width: 12),
              Expanded(child: ShimmerLoader.rounded(height: 120, width: double.infinity)),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(child: ShimmerLoader.rounded(height: 120, width: double.infinity)),
              const SizedBox(width: 12),
              Expanded(child: ShimmerLoader.rounded(height: 120, width: double.infinity)),
            ],
          ),
          const SizedBox(height: 20),
          ShimmerLoader.buildChartSkeleton(),
          const SizedBox(height: 20),
          ShimmerLoader.rounded(height: 300, width: double.infinity),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.cloud_off_rounded, size: 64, color: AppTheme.textHint),
          const SizedBox(height: 16),
          Text("No data available", style: GoogleFonts.outfit(fontWeight: FontWeight.bold, color: AppTheme.textMain)),
          const SizedBox(height: 8),
          TextButton(
            onPressed: () => _loadData(forcedRefresh: true),
            child: Text("Retry", style: GoogleFonts.outfit(color: AppTheme.primaryNavy, fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Ã¢â€â‚¬Ã¢â€â‚¬ Summary Cards Ã¢â€â‚¬Ã¢â€â‚¬
          _buildSectionLabel("OVERVIEW"),
          const SizedBox(height: 12),
          _buildStatCards(),
          const SizedBox(height: 28),

          // Ã¢â€â‚¬Ã¢â€â‚¬ Monthly Trend Bar Chart Ã¢â€â‚¬Ã¢â€â‚¬
          _buildSectionLabel("MONTHLY TREND"),
          const SizedBox(height: 12),
          _buildTrendCard(),
          const SizedBox(height: 28),

          // Ã¢â€â‚¬Ã¢â€â‚¬ Top Violations Ã¢â€â‚¬Ã¢â€â‚¬
          _buildSectionLabel("TOP VIOLATIONS"),
          const SizedBox(height: 12),
          _buildTopViolationsCard(),
          const SizedBox(height: 28),

          // Ã¢â€â‚¬Ã¢â€â‚¬ Severity Pie Chart Ã¢â€â‚¬Ã¢â€â‚¬
          _buildSectionLabel("SEVERITY BREAKDOWN"),
          const SizedBox(height: 12),
          _buildSeverityCard(),
          const SizedBox(height: 28),

          // Ã¢â€â‚¬Ã¢â€â‚¬ Repeat Offenders Ã¢â€â‚¬Ã¢â€â‚¬
          if (_repeatOffenders.isNotEmpty) ...[
            _buildSectionLabel("REPEAT OFFENDERS"),
            const SizedBox(height: 12),
            _buildRepeatOffendersCard(),
            const SizedBox(height: 28),
          ],
        ],
      ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.04, end: 0),
    );
  }

  Widget _buildSectionLabel(String label) {
    return Text(
      label,
      style: GoogleFonts.outfit(
        fontSize: 11,
        fontWeight: FontWeight.w900,
        color: AppTheme.textMuted,
        letterSpacing: 1.8,
      ),
    );
  }

  Widget _buildStatCards() {
    final resolutionRate = _totalCases > 0
        ? ((_resolvedCases / _totalCases) * 100).toStringAsFixed(0)
        : '0';

    return Column(
      children: [
        Row(
          children: [
            _statCard("Total Cases", "$_totalCases", Icons.folder_open_rounded, AppTheme.primaryNavy),
            const SizedBox(width: 12),
            _statCard("Pending", "$_pendingCases", Icons.pending_actions_rounded, AppTheme.accentAmber),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            _statCard("Closed", "$_resolvedCases", Icons.check_circle_outline_rounded, AppTheme.accentEmerald),
            const SizedBox(width: 12),
            _statCard("Resolution %", "$resolutionRate%", Icons.trending_up_rounded, AppTheme.accentCyan),
          ],
        ),
      ],
    );
  }

  Widget _statCard(String label, String value, IconData icon, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: color.withOpacity(0.12)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(height: 14),
            Text(
              value,
              style: GoogleFonts.outfit(
                fontSize: 26,
                fontWeight: FontWeight.w900,
                color: AppTheme.textMain,
              ),
            ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.2, end: 0),
            const SizedBox(height: 2),
            Text(
              label,
              style: GoogleFonts.outfit(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppTheme.textSub,
              ),
            ),
          ],
        ),
      ).animate().fadeIn().scale(begin: const Offset(0.95, 0.95), end: const Offset(1, 1)),
    );
  }

  Widget _buildTrendCard() {
    final maxY = _monthlyTrend.map((e) => (e['count'] as int).toDouble()).fold(0.0, (a, b) => a > b ? a : b);
    final chartMax = (maxY < 5 ? 5 : maxY + 2).ceilToDouble();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 24, 16, 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text("Last 6 Months",
                  style: GoogleFonts.outfit(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMain,
                  )),
              Text("${_monthlyTrend.map((e) => e['count'] as int).fold(0, (a, b) => a + b)} total",
                  style: GoogleFonts.outfit(
                    fontSize: 12,
                    color: AppTheme.textMuted,
                    fontWeight: FontWeight.w600,
                  )),
            ],
          ),
          const SizedBox(height: 24),
          SizedBox(
            height: 180,
            child: BarChart(
              swapAnimationDuration: const Duration(milliseconds: 800),
              swapAnimationCurve: Curves.easeOutCubic,
              BarChartData(
                maxY: chartMax,
                gridData: FlGridData(
                  show: true,
                  drawVerticalLine: false,
                  getDrawingHorizontalLine: (value) =>
                      FlLine(color: AppTheme.bgLight, strokeWidth: 1.5),
                ),
                titlesData: FlTitlesData(
                  leftTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 28,
                      getTitlesWidget: (value, meta) => Text(
                        value.toInt().toString(),
                        style: GoogleFonts.outfit(
                            fontSize: 10, color: AppTheme.textMuted),
                      ),
                    ),
                  ),
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      getTitlesWidget: (value, meta) {
                        final i = value.toInt();
                        if (i < 0 || i >= _monthlyTrend.length) return const Text('');
                        return Padding(
                          padding: const EdgeInsets.only(top: 8),
                          child: Text(
                            _monthlyTrend[i]['month'],
                            style: GoogleFonts.outfit(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: AppTheme.textMuted,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ),
                borderData: FlBorderData(show: false),
                barGroups: List.generate(_monthlyTrend.length, (i) {
                  final count = (_monthlyTrend[i]['count'] as int).toDouble();
                  return BarChartGroupData(
                    x: i,
                    barRods: [
                      BarChartRodData(
                        toY: count,
                        color: AppTheme.primaryNavy,
                        width: 18,
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(8)),
                        backDrawRodData: BackgroundBarChartRodData(
                          show: true,
                          toY: chartMax,
                          color: AppTheme.bgLight,
                        ),
                      ),
                    ],
                  );
                }),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTopViolationsCard() {
    if (_topViolations.isEmpty) {
      return _emptyCard("No violation data yet");
    }

    final maxCount = _topViolations.first.value;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
      ),
      child: Column(
        children: List.generate(_topViolations.length, (i) {
          final entry = _topViolations[i];
          final pct = maxCount > 0 ? entry.value / maxCount : 0.0;
          final colors = [
            AppTheme.accentRose,
            AppTheme.accentAmber,
            AppTheme.primaryNavy,
            AppTheme.accentCyan,
            AppTheme.accentEmerald,
          ];
          final color = colors[i % colors.length];

          return Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 22,
                      height: 22,
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.12),
                        shape: BoxShape.circle,
                      ),
                      child: Center(
                        child: Text(
                          '${i + 1}',
                          style: GoogleFonts.outfit(
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            color: color,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        entry.key,
                        style: GoogleFonts.outfit(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textMain,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Text(
                      '${entry.value}x',
                      style: GoogleFonts.outfit(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: color,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(100),
                  child: LinearProgressIndicator(
                    value: pct,
                    minHeight: 6,
                    backgroundColor: color.withOpacity(0.1),
                    valueColor: AlwaysStoppedAnimation<Color>(color),
                  ),
                ),
              ],
            ),
          ).animate().fadeIn(delay: Duration(milliseconds: i * 80)).slideX(begin: 0.05, end: 0);
        }),
      ),
    );
  }

  Widget _buildSeverityCard() {
    final total = _majorCases + _minorCases;
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
      ),
      child: Column(
        children: [
          SizedBox(
            height: 180,
            child: total == 0
                ? Center(
                    child: Text("No data",
                        style: GoogleFonts.outfit(color: AppTheme.textHint)))
                : PieChart(
                    PieChartData(
                      pieTouchData: PieTouchData(
                        touchCallback: (event, response) {
                          if (mounted) setState(() {
                            if (!event.isInterestedForInteractions ||
                                response == null ||
                                response.touchedSection == null) {
                              _touchedIndex = -1;
                              return;
                            }
                            _touchedIndex = response
                                .touchedSection!.touchedSectionIndex;
                          });
                        },
                      ),
                      startDegreeOffset: -90,
                      sectionsSpace: 4,
                      centerSpaceRadius: 52,
                      sections: [
                        PieChartSectionData(
                          value: _majorCases.toDouble(),
                          color: AppTheme.accentRose,
                          title: _touchedIndex == 0 ? '$_majorCases' : '',
                          radius: _touchedIndex == 0 ? 62 : 54,
                          titleStyle: GoogleFonts.outfit(
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              fontSize: 14),
                        ),
                        PieChartSectionData(
                          value: _minorCases.toDouble(),
                          color: AppTheme.accentAmber,
                          title: _touchedIndex == 1 ? '$_minorCases' : '',
                          radius: _touchedIndex == 1 ? 62 : 54,
                          titleStyle: GoogleFonts.outfit(
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              fontSize: 14),
                        ),
                      ],
                    ),
                  ).animate().scale(begin: const Offset(0.7, 0.7), end: const Offset(1, 1), duration: 600.ms, curve: Curves.easeOutBack),
          ),
          const SizedBox(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _legendDot("Major", AppTheme.accentRose, _majorCases, total),
              const SizedBox(width: 32),
              _legendDot("Minor", AppTheme.accentAmber, _minorCases, total),
            ],
          ),
        ],
      ),
    );
  }

  Widget _legendDot(String label, Color color, int count, int total) {
    final pct = total > 0 ? ((count / total) * 100).toStringAsFixed(0) : '0';
    return Column(
      children: [
        Row(
          children: [
            Container(
                width: 10,
                height: 10,
                decoration:
                    BoxDecoration(color: color, shape: BoxShape.circle)),
            const SizedBox(width: 8),
            Text(label,
                style: GoogleFonts.outfit(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textSub)),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          "$count cases ($pct%)",
          style: GoogleFonts.outfit(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: AppTheme.textMuted),
        ),
      ],
    );
  }

  Widget _buildRepeatOffendersCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppTheme.accentRose.withOpacity(0.15)),
      ),
      child: Column(
        children: List.generate(_repeatOffenders.length, (i) {
          final entry = _repeatOffenders[i];
          return Padding(
            padding: const EdgeInsets.only(bottom: 14),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: AppTheme.accentRose.withOpacity(0.08),
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Text(
                      entry.key.isNotEmpty ? entry.key[0].toUpperCase() : '?',
                      style: GoogleFonts.outfit(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        color: AppTheme.accentRose,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        entry.key,
                        style: GoogleFonts.outfit(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textMain,
                        ),
                      ),
                      Text(
                        "${entry.value} violations",
                        style: GoogleFonts.outfit(
                          fontSize: 12,
                          color: AppTheme.textMuted,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.accentRose.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '${entry.value}x',
                    style: GoogleFonts.outfit(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.accentRose,
                    ),
                  ),
                ),
              ],
            ).animate().fadeIn(delay: Duration(milliseconds: i * 80)).slideX(begin: 0.05, end: 0),
          );
        }),
      ),
    );
  }

  Widget _emptyCard(String message) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 32),
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppTheme.primarySlate.withOpacity(0.08)),
      ),
      child: Center(
        child: Text(message,
            style: GoogleFonts.outfit(color: AppTheme.textHint)),
      ),
    );
  }
}
