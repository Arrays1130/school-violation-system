import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';
import '../api_service.dart';
import '../widgets/app_ui.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/empty_state_widget.dart';

class StudentProfileScreen extends StatefulWidget {
  final Map<String, dynamic> student;

  const StudentProfileScreen({super.key, required this.student});

  @override
  State<StudentProfileScreen> createState() => _StudentProfileScreenState();
}

class _StudentProfileScreenState extends State<StudentProfileScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<dynamic> _studentCases = [];
  int _majorCount = 0;
  int _minorCount = 0;

  @override
  void initState() {
    super.initState();
    _fetchStudentHistory();
  }

  Future<void> _fetchStudentHistory({bool forcedRefresh = false}) async {
    try {
      final data = await _apiService.getViolations(forcedRefresh: forcedRefresh);
      
      List<dynamic> allCases;
      if (data is Map) {
        allCases = (data['data'] ?? data['violations'] ?? []) as List<dynamic>;
      } else if (data is List) {
        allCases = data;
      } else {
        allCases = [];
      }
      
      // Robust ID matching using toString() to avoid int/string type mismatch
      final studentId = widget.student['id']?.toString() ?? '';
      final studentName = (widget.student['full_name'] ?? '').toString().toLowerCase().trim();
      
      final filteredCases = allCases.where((c) {
        final cStudent = c['student'] ?? {};
        final cStudentId = cStudent['id']?.toString() ?? '';
        final cStudentName = (cStudent['full_name'] ?? '').toString().toLowerCase().trim();
        
        // Match by ID first, fallback to name match if ID is empty
        if (studentId.isNotEmpty && cStudentId.isNotEmpty) {
          return cStudentId == studentId;
        }
        return studentName.isNotEmpty && cStudentName == studentName;
      }).toList();

      // Sort by newest first
      filteredCases.sort((a, b) {
        try {
          final dateA = DateTime.parse(a['created_at'] ?? '');
          final dateB = DateTime.parse(b['created_at'] ?? '');
          return dateB.compareTo(dateA);
        } catch (_) {
          return 0;
        }
      });

      int major = 0;
      int minor = 0;
      for (var c in filteredCases) {
        // severity can be at root level OR inside the nested 'violation' object
        final severity = (c['severity'] 
            ?? c['violation']?['severity'] 
            ?? '').toString().toLowerCase();
        if (severity == 'major') major++;
        if (severity == 'minor') minor++;
      }

      if (mounted) {
        setState(() {
        _studentCases = filteredCases;
        _majorCount = major;
        _minorCount = minor;
        _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
        _isLoading = false;
        });
      }
    }
  }

  Color _getSeverityColor(String severity) {
    switch (severity.toLowerCase()) {
      case 'major':
        return AppTheme.accentRose;
      case 'minor':
        return AppTheme.accentAmber;
      default:
        return AppTheme.primarySlate;
    }
  }

  String _initials(String name) {
    final parts = name.trim().split(' ').where((p) => p.isNotEmpty).toList();
    if (parts.isEmpty) return '?';
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts.last[0]}'.toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final fullName =
        widget.student['full_name']?.toString() ?? 'Student name unavailable';
    final studentNo = widget.student['student_number']?.toString();
    final course = widget.student['course']?.toString();
    final subtitleParts = <String>[
      if (studentNo != null && studentNo.isNotEmpty) studentNo,
      if (course != null && course.isNotEmpty) course,
    ];
    final subtitle =
        subtitleParts.isNotEmpty ? subtitleParts.join('  •  ') : '';

    if (_isLoading) {
      return _buildLoadingSkeleton();
    }

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: RefreshIndicator(
        color: AppTheme.primaryNavy,
        backgroundColor: Colors.white,
        onRefresh: () => _fetchStudentHistory(forcedRefresh: true),
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverAppBar(
              expandedHeight: 210,
              pinned: true,
              stretch: true,
              backgroundColor: AppTheme.primaryNavy,
              leading: IconButton(
                icon: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.arrow_back_ios_new_rounded,
                    size: 16,
                    color: Colors.white,
                  ),
                ),
                onPressed: () {
                  HapticFeedback.lightImpact();
                  Navigator.pop(context);
                },
              ),
              flexibleSpace: FlexibleSpaceBar(
                stretchModes: const [StretchMode.zoomBackground],
                background: Container(
                  decoration: const BoxDecoration(
                    gradient: AppTheme.heroGradient,
                  ),
                  child: Stack(
                    children: [
                      Positioned(
                        top: -40,
                        right: -30,
                        child: Container(
                          width: 160,
                          height: 160,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white.withValues(alpha: 0.08),
                          ),
                        ),
                      ),
                      Positioned(
                        bottom: 28,
                        left: 24,
                        right: 24,
                        child: Row(
                          children: [
                            Container(
                              width: 72,
                              height: 72,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 3),
                                boxShadow: AppTheme.softShadow,
                              ),
                              child: Center(
                                child: Text(
                                  _initials(fullName),
                                  style: GoogleFonts.inter(
                                    fontSize: 24,
                                    fontWeight: FontWeight.w800,
                                    color: AppTheme.primary,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    fullName,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.inter(
                                      fontSize: 20,
                                      fontWeight: FontWeight.w800,
                                      color: Colors.white,
                                      letterSpacing: -0.4,
                                    ),
                                  ),
                                  if (subtitle.isNotEmpty) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      subtitle,
                                      style: GoogleFonts.inter(
                                        fontSize: 12,
                                        color: Colors.white.withValues(alpha: 0.82),
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Transform.translate(
                offset: const Offset(0, -20),
                child: Container(
                  decoration: const BoxDecoration(
                    color: AppTheme.bgLight,
                    borderRadius: BorderRadius.vertical(top: Radius.circular(32)),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(20, 28, 20, 0),
                    child: _buildStatsRow(),
                  ),
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: AppUi.sectionHeader(
                'Violation history',
                subtitle: 'All recorded cases for this student.',
              ),
            ),
            if (_studentCases.isEmpty)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(horizontal: 24),
                  child: EmptyStateWidget(
                    icon: Icons.verified_user_outlined,
                    title: 'Clean record',
                    message:
                        'No violations recorded for this student in the current academic period.',
                  ),
                ),
              )
            else
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final c = _studentCases[index];
                    return AppUi.staggerIn(
                      _buildTimelineItem(
                        c,
                        index == _studentCases.length - 1,
                      ),
                      index,
                    );
                  },
                  childCount: _studentCases.length,
                ),
              ),
            const SliverToBoxAdapter(child: SizedBox(height: 50)),
          ],
        ),
      ),
    );
  }

  Widget _buildLoadingSkeleton() {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: Column(
        children: [
          Container(
            height: 240,
            decoration: const BoxDecoration(
              gradient: AppTheme.heroGradient,
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(32),
                bottomRight: Radius.circular(32),
              ),
            ),
            child: SafeArea(
              child: Align(
                alignment: Alignment.topLeft,
                child: IconButton(
                  icon: const Icon(
                    Icons.arrow_back_ios_new_rounded,
                    color: Colors.white,
                    size: 20,
                  ),
                  onPressed: () => Navigator.pop(context),
                ),
              ),
            ),
          ),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(20),
              children: [
                ShimmerLoader.buildStatGridSkeleton(),
                const SizedBox(height: 24),
                ShimmerLoader.buildListSkeleton(),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsRow() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Row(
        children: [
          Expanded(child: _buildStatCard('Total', _studentCases.length.toString(), AppTheme.primaryNavy)),
          const SizedBox(width: 12),
          Expanded(child: _buildStatCard('Major', _majorCount.toString(), AppTheme.accentRose)),
          const SizedBox(width: 12),
          Expanded(child: _buildStatCard('Minor', _minorCount.toString(), AppTheme.accentAmber)),
        ],
      ).animate().fadeIn(delay: 400.ms).slideY(begin: 0.2, end: 0),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return AppUi.surfaceCard(
      padding: const EdgeInsets.symmetric(vertical: 16),
      borderColor: color.withValues(alpha: 0.1),
      color: color.withValues(alpha: 0.05),
      child: Column(
        children: [
          Text(
            value,
            style: GoogleFonts.inter(
              fontSize: 24,
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: color.withValues(alpha: 0.75),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTimelineItem(Map<String, dynamic> caseData, bool isLast) {
    final severity = (caseData['severity'] ?? 'Minor').toString();
    // Check 'title' key first (API standard), fallback to 'name'
    final title = caseData['violation']?['title'] 
        ?? caseData['violation']?['name']
        ?? caseData['title']
        ?? caseData['name']
        ?? 'Violation title unavailable';
    final dateStr = caseData['created_at'] ?? '';
    final date = dateStr.isNotEmpty ? DateTime.tryParse(dateStr) : null;
    final months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    final formattedDate = date != null ? '${months[date.month - 1]} ${date.day}, ${date.year}' : 'Date not recorded';
    final color = _getSeverityColor(severity);

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Stack(
        children: [
          // Timeline Line
          if (!isLast)
            Positioned(
              left: 5,
              top: 36,
              bottom: 0,
              child: Container(
                width: 2,
                color: AppTheme.primarySlate.withValues(alpha: 0.1),
              ),
            ),
          // Timeline Dot
          Positioned(
            left: 0,
            top: 24,
            child: Container(
              width: 12,
              height: 12,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: color,
                border: Border.all(color: AppTheme.bgLight, width: 2),
              ),
            ),
          ),
          // Card Content
          Padding(
            padding: const EdgeInsets.only(left: 32, bottom: 16, top: 12),
            child: AppUi.surfaceCard(
              padding: const EdgeInsets.all(16),
              borderColor: color.withValues(alpha: 0.5),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(
                          title.isNotEmpty ? title : 'Violation title unavailable',
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.inter(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.textMain,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      AppUi.brandPill(
                        label: severity.toUpperCase(),
                        textColor: color,
                        backgroundColor: color.withValues(alpha: 0.1),
                        borderColor: color.withValues(alpha: 0.2),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Icon(Icons.calendar_today_rounded, size: 12, color: AppTheme.textHint),
                      const SizedBox(width: 4),
                      Text(
                        formattedDate,
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          color: AppTheme.textSub,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
