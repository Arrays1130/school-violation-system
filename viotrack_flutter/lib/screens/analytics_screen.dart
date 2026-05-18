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

  @override
  void initState() {
    super.initState();
    _fetchStats();
  }

  Future<void> _fetchStats() async {
    try {
      final data = await _apiService.getStats(forcedRefresh: true);
      setState(() {
        _stats = data;
        _isLoading = false;
      });
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      appBar: AppBar(
        title: Text('Data Insights', 
          style: GoogleFonts.outfit(fontWeight: FontWeight.w900, fontSize: 22)),
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.info_outline_rounded, color: AppTheme.textMuted),
            onPressed: () {},
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _fetchStats,
        color: AppTheme.accentPurple,
        child: _isLoading 
          ? _buildSkeleton()
          : _stats == null 
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
          Text("Insight data unavailable", style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
          TextButton(onPressed: _fetchStats, child: const Text("Retry"))
        ],
      ),
    );
  }

  Widget _buildContent() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(20, 10, 20, 100),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Summary Cards (Bento Style) ──
          _buildBentoGrid(),
          const SizedBox(height: 24),

          // ── Pie Chart Card ──
          _buildPieCard(),
          const SizedBox(height: 24),

          // ── Trends Card ──
          _buildTrendsCard(),
          const SizedBox(height: 60),
        ],
      ),
    ).animate().fadeIn(duration: 400.ms).slideY(begin: 0.05);
  }

  Widget _buildBentoGrid() {
    final summary = _stats!['summary'];
    return Column(
      children: [
        Row(
          children: [
            _buildBentoItem(
              "Total Cases", 
              "${summary['total']}", 
              Icons.folder_shared_rounded, 
              AppTheme.accentPurple,
              "+12% vs last month"
            ),
            const SizedBox(width: 12),
            _buildBentoItem(
              "Pending", 
              "${summary['pending']}", 
              Icons.watch_later_rounded, 
              AppTheme.accentAmber,
              "Action Required"
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            _buildBentoItem(
              "Resolved", 
              "${summary['resolved']}", 
              Icons.verified_user_rounded, 
              AppTheme.accentEmerald,
              "Efficiency: 88%"
            ),
            const SizedBox(width: 12),
            _buildBentoItem(
              "Avg / Month", 
              "${((summary['total'] ?? 0) / 6).toStringAsFixed(1)}", 
              Icons.trending_up_rounded, 
              AppTheme.accentIndigo,
              "Stable trend"
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildBentoItem(String label, String value, IconData icon, Color color, String subtext) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(24),
          boxShadow: AppTheme.softShadow,
          border: Border.all(color: color.withOpacity(0.1), width: 1.5),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: color.withOpacity(0.1), shape: BoxShape.circle),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(height: 16),
            Text(value, style: GoogleFonts.outfit(fontSize: 28, fontWeight: FontWeight.w900, color: AppTheme.textMain)),
            Text(label, style: GoogleFonts.outfit(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.textSub)),
            const SizedBox(height: 8),
            Text(subtext, style: GoogleFonts.outfit(fontSize: 9, fontWeight: FontWeight.w600, color: AppTheme.textMuted)),
          ],
        ),
      ),
    );
  }

  Widget _buildPieCard() {
    final severityStats = _stats!['severity_stats'] as Map<String, dynamic>;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
        boxShadow: AppTheme.softShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text("Offense Mix", style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.w900, color: AppTheme.textMain)),
              const Spacer(),
              const Icon(Icons.more_vert_rounded, color: AppTheme.textMuted, size: 20),
            ],
          ),
          const SizedBox(height: 32),
          SizedBox(
            height: 200,
            child: PieChart(
              PieChartData(
                pieTouchData: PieTouchData(
                  touchCallback: (event, response) {
                    setState(() {
                      if (!event.isInterestedForInteractions || response == null || response.touchedSection == null) {
                        _touchedIndex = -1;
                        return;
                      }
                      _touchedIndex = response.touchedSection!.touchedSectionIndex;
                    });
                  },
                ),
                sectionsSpace: 6,
                centerSpaceRadius: 55,
                sections: _buildPieSections(severityStats),
              ),
            ),
          ),
          const SizedBox(height: 24),
          _buildPieLegend(),
        ],
      ),
    );
  }

  List<PieChartSectionData> _buildPieSections(Map<String, dynamic> stats) {
    final Map<String, Color> colorMap = {
      'Major': AppTheme.accentAmber,
      'Minor': AppTheme.accentIndigo,
    };
    
    int i = 0;
    return colorMap.keys.map((label) {
      final rawValue = stats[label];
      double value = 0;
      if (rawValue is num) {
        value = rawValue.toDouble();
      } else if (rawValue is String) {
        value = double.tryParse(rawValue) ?? 0;
      }
      final isTouched = i == _touchedIndex;
      final radius = isTouched ? 65.0 : 55.0;
      final idx = i++;
      
      return PieChartSectionData(
        color: colorMap[label],
        value: value,
        title: isTouched ? "${value.toInt()}" : "",
        radius: radius,
        titleStyle: GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.w900, color: Colors.white),
        badgeWidget: isTouched ? _buildPieBadge(label) : null,
        badgePositionPercentageOffset: 1.1,
      );
    }).toList();
  }

  Widget _buildPieBadge(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: AppTheme.primaryNavy,
        borderRadius: BorderRadius.circular(12),
        boxShadow: AppTheme.softShadow,
      ),
      child: Text(label.toUpperCase(), style: GoogleFonts.outfit(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold)),
    );
  }

  Widget _buildPieLegend() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        _buildLegendItem("Major", AppTheme.accentAmber),
        const SizedBox(width: 24),
        _buildLegendItem("Minor", AppTheme.accentIndigo),
      ],
    );
  }

  Widget _buildLegendItem(String label, Color color) {
    return Row(
      children: [
        Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 8),
        Text(label, style: GoogleFonts.outfit(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.textSub)),
      ],
    );
  }

  Widget _buildTrendsCard() {
    final trends = _stats!['monthly_trends'] as List<dynamic>;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
        boxShadow: AppTheme.softShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text("Monthly Trend", style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.w900, color: AppTheme.textMain)),
          const SizedBox(height: 32),
          SizedBox(
            height: 250,
            child: BarChart(
              BarChartData(
                gridData: FlGridData(
                  show: true,
                  drawVerticalLine: false,
                  getDrawingHorizontalLine: (value) => FlLine(color: AppTheme.bgLight, strokeWidth: 1),
                ),
                titlesData: FlTitlesData(
                  leftTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 30,
                      getTitlesWidget: (value, meta) => Text(value.toInt().toString(), style: GoogleFonts.outfit(fontSize: 10, color: AppTheme.textMuted)),
                    ),
                  ),
                  rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      getTitlesWidget: (value, meta) {
                        if (value.toInt() < 0 || value.toInt() >= trends.length) return const Text("");
                        return Padding(
                          padding: const EdgeInsets.only(top: 10),
                          child: Text(trends[value.toInt()]['month'].substring(0, 3), style: GoogleFonts.outfit(fontSize: 10, color: AppTheme.textMuted, fontWeight: FontWeight.bold)),
                        );
                      },
                    ),
                  ),
                ),
                borderData: FlBorderData(show: false),
                barGroups: List.generate(trends.length, (idx) {
                  return BarChartGroupData(
                    x: idx,
                    barRods: [
                      BarChartRodData(
                        toY: (trends[idx]['count'] as int).toDouble(),
                        gradient: AppTheme.accentGradient,
                        width: 14,
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(6)),
                        backDrawRodData: BackgroundBarChartRodData(show: true, toY: 15, color: AppTheme.bgLight),
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
}
