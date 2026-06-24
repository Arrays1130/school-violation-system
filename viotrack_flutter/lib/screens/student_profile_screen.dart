import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../theme/app_theme.dart';
import '../api_service.dart';

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

      setState(() {
        _studentCases = filteredCases;
        _majorCount = major;
        _minorCount = minor;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
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

  @override
  Widget build(BuildContext context) {
    final String fullName = widget.student['full_name'] ?? 'Unknown Student';
    final String? studentNo = widget.student['student_number']?.toString();
    final String? course = widget.student['course']?.toString();
    // Build subtitle only from available fields
    final List<String> subtitleParts = [
      if (studentNo != null && studentNo.isNotEmpty) studentNo,
      if (course != null && course.isNotEmpty) course,
    ];
    final String subtitle = subtitleParts.isNotEmpty ? subtitleParts.join('  •  ') : '';

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      appBar: AppBar(
        backgroundColor: AppTheme.bgLight,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppTheme.primaryNavy, size: 20),
          onPressed: () {
            HapticFeedback.lightImpact();
            Navigator.pop(context);
          },
        ),
        title: Text(
          "Student Profile",
          style: GoogleFonts.outfit(
            color: AppTheme.primaryNavy,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        centerTitle: true,
      ),
      body: _isLoading 
        ? const Center(child: CircularProgressIndicator(color: AppTheme.primaryNavy))
        : RefreshIndicator(
            color: AppTheme.primaryNavy,
            backgroundColor: Colors.white,
            onRefresh: () => _fetchStudentHistory(forcedRefresh: true),
            child: CustomScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              slivers: [
                SliverToBoxAdapter(
                  child: _buildProfileHeader(fullName, subtitle),
                ),
                SliverToBoxAdapter(
                  child: _buildStatsRow(),
                ),
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(24, 32, 24, 16),
                    child: Text(
                      "VIOLATION HISTORY",
                      style: GoogleFonts.outfit(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textMuted,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ),
                ),
                if (_studentCases.isEmpty)
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.all(40.0),
                      child: Center(
                        child: Text(
                          "No violations recorded.",
                          style: GoogleFonts.outfit(color: AppTheme.textHint),
                        ),
                      ),
                    ),
                  )
                else
                  SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context, index) {
                        final c = _studentCases[index];
                        return _buildTimelineItem(c, index == _studentCases.length - 1);
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

  Widget _buildProfileHeader(String name, String subtitle) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
      child: Column(
        children: [
          Container(
            width: 90,
            height: 90,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: AppTheme.bgCard,
              border: Border.all(color: AppTheme.primarySlate.withOpacity(0.2), width: 1.5),
            ),
            child: const Icon(Icons.person_outline_rounded, size: 40, color: AppTheme.primaryNavy),
          ),
          const SizedBox(height: 16),
          Text(
            name,
            textAlign: TextAlign.center,
            style: GoogleFonts.outfit(
              fontSize: 26,
              fontWeight: FontWeight.w800,
              color: AppTheme.primaryNavy,
              letterSpacing: -0.5,
            ),
          ).animate().fadeIn(delay: 200.ms).slideY(begin: 0.2, end: 0),
          if (subtitle.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              subtitle,
              style: GoogleFonts.outfit(
                fontSize: 14,
                color: AppTheme.textSub,
                fontWeight: FontWeight.w500,
              ),
            ).animate().fadeIn(delay: 300.ms).slideY(begin: 0.2, end: 0),
          ],
        ],
      ),
    );
  }

  Widget _buildStatsRow() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Row(
        children: [
          Expanded(child: _buildStatCard("Total", _studentCases.length.toString(), AppTheme.primaryNavy)),
          const SizedBox(width: 12),
          Expanded(child: _buildStatCard("Major", _majorCount.toString(), AppTheme.accentRose)),
          const SizedBox(width: 12),
          Expanded(child: _buildStatCard("Minor", _minorCount.toString(), AppTheme.accentAmber)),
        ],
      ).animate().fadeIn(delay: 400.ms).slideY(begin: 0.2, end: 0),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 16),
      decoration: BoxDecoration(
        color: color.withOpacity(0.05),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withOpacity(0.1)),
      ),
      child: Column(
        children: [
          Text(
            value,
            style: GoogleFonts.outfit(
              fontSize: 24,
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label.toUpperCase(),
            style: GoogleFonts.outfit(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              color: color.withOpacity(0.7),
              letterSpacing: 1.0,
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
        ?? 'Unspecified Violation';
    final dateStr = caseData['created_at'] ?? '';
    final date = dateStr.isNotEmpty ? DateTime.tryParse(dateStr) : null;
    final months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    final formattedDate = date != null ? '${months[date.month - 1]} ${date.day}, ${date.year}' : 'Unknown Date';
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
                color: AppTheme.primarySlate.withOpacity(0.1),
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
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.bgCard,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: color.withOpacity(0.5), width: 1.5),
              ),
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
                          title.isNotEmpty ? title : 'Unspecified Violation',
                          style: GoogleFonts.outfit(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.textMain,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: color.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          severity.toUpperCase(),
                          style: GoogleFonts.outfit(
                            fontSize: 9,
                            fontWeight: FontWeight.w800,
                            color: color,
                            letterSpacing: 0.5,
                          ),
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
                        style: GoogleFonts.outfit(
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
