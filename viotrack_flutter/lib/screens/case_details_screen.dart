import 'package:flutter/material.dart';
import 'student_profile_screen.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import 'package:flutter/services.dart';
import 'dart:ui';
import '../widgets/empty_state_widget.dart';
import '../widgets/skeleton_loader.dart';

class CaseDetailsScreen extends StatefulWidget {
  final int caseId;
  final Map<String, dynamic>? initialData;
  const CaseDetailsScreen({Key? key, required this.caseId, this.initialData}) : super(key: key);

  @override
  _CaseDetailsScreenState createState() => _CaseDetailsScreenState();
}

class _CaseDetailsScreenState extends State<CaseDetailsScreen> {
  final ApiService _apiService = ApiService();
  Map<String, dynamic>? _case;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    if (widget.initialData != null) {
      _case = widget.initialData;
      _isLoading = false;
    }
    _fetchDetails();
  }

  Future<void> _fetchDetails({bool force = false}) async {
    try {
      final result = await _apiService.getCaseDetails(widget.caseId, forcedRefresh: force);
      if (mounted) {
        if (mounted) setState(() {
          _case = result;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted && _case == null) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: _isLoading
          ? SafeArea(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                child: ShimmerLoader.buildListSkeleton(),
              ),
            )
          : _case == null
              ? _buildError()
              : _buildMainContent(),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const EmptyStateWidget(
            icon: Icons.error_outline_rounded,
            title: "Data Unavailable",
            message: "We couldn't load the details for this case. Please check your connection or try again.",
          ),
          const SizedBox(height: 24),
          TextButton.icon(
            onPressed: _fetchDetails,
            icon: const Icon(Icons.refresh_rounded, color: AppTheme.accentCyan),
            label: Text("Try Again", style: GoogleFonts.outfit(color: AppTheme.accentCyan, fontWeight: FontWeight.bold)),
            style: TextButton.styleFrom(
              backgroundColor: AppTheme.accentCyan.withOpacity(0.1),
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          )
        ],
      ),
    );
  }

  Widget _buildMainContent() {
    final student = _case?['student'] ?? {};
    final violation = _case?['violation'] ?? {};
    final severity = violation['severity']?.toString() ?? 'Minor';
    final status = _case?['status']?.toString() ?? 'Pending';
    final statusColor = _getStatusColor(status);

    return RefreshIndicator(
      onRefresh: () => _fetchDetails(force: true),
      color: AppTheme.accentCyan,
      child: CustomScrollView(
        slivers: [
        // â”€â”€ Sticky Header with Background Pattern â”€â”€
        SliverAppBar(
          expandedHeight: 240,
          pinned: true,
          elevation: 0,
          stretch: true,
          backgroundColor: _getSeverityColor(severity),
          leading: IconButton(
            icon: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), shape: BoxShape.circle),
              child: const Icon(Icons.arrow_back_ios_new_rounded, size: 16, color: Colors.white),
            ),
            onPressed: () => Navigator.pop(context),
          ),
          flexibleSpace: FlexibleSpaceBar(
            stretchModes: const [StretchMode.zoomBackground],
            background: Stack(
              fit: StackFit.expand,
              children: [
                Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        _getSeverityColor(severity).withOpacity(0.85),
                        _getSeverityColor(severity),
                      ],
                    ),
                  ),
                ),
                _buildPattern(),
                // User Profile Header
                Positioned(
                  bottom: 40,
                  left: 24,
                  right: 24,
                  child: GestureDetector(
                    onTap: () {
                      HapticFeedback.lightImpact();
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => StudentProfileScreen(student: student),
                        ),
                      );
                    },
                    child: Row(
                      children: [
                        Hero(
                          tag: 'case_${widget.caseId}_avatar',
                          child: Material(
                            color: Colors.transparent,
                            child: Container(
                                width: 84,
                                height: 84,
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(24),
                                  boxShadow: [
                                    BoxShadow(
                                      color: _getSeverityColor(severity).withOpacity(0.5),
                                      blurRadius: 24,
                                      offset: const Offset(0, 12),
                                    ),
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.05),
                                      blurRadius: 8,
                                      offset: const Offset(0, 4),
                                    )
                                  ],
                                ),
                                child: Center(
                                  child: Icon(
                                    _getSeverityIcon(severity),
                                    size: 42,
                                    color: _getSeverityColor(severity),
                                  ),
                                ),
                              ),
                          ),
                        ),
                        const SizedBox(width: 20),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                  student['full_name'] ?? 'Unknown Student',
                                  style: GoogleFonts.outfit(
                                    fontSize: 26,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.white,
                                    letterSpacing: -0.5,
                                    height: 1.1,
                                    shadows: [
                                      Shadow(
                                        color: Colors.black.withOpacity(0.15),
                                        blurRadius: 10,
                                        offset: const Offset(0, 4),
                                      )
                                    ],
                                  ),
                                ),
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: _getSeverityColor(severity).withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(color: _getSeverityColor(severity).withOpacity(0.5)),
                                ),
                                child: Text(
                                  severity.toUpperCase(),
                                  style: GoogleFonts.outfit(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.white,
                                    letterSpacing: 1.0,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const Icon(
                          Icons.arrow_forward_ios_rounded,
                          color: Colors.white70,
                          size: 16,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),

        // â”€â”€ Main Body â”€â”€
        SliverToBoxAdapter(
          child: Transform.translate(
            offset: const Offset(0, -32),
            child: Container(
              decoration: const BoxDecoration(
                color: AppTheme.bgLight,
                borderRadius: BorderRadius.vertical(top: Radius.circular(40)),
              ),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 32, 20, 100),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Status Badge Card
                    _buildStatusCard(status, statusColor, severity),
                    const SizedBox(height: 24),

                    // Violation Info (Bento Grid)
                    _buildSectionHeader("CASE DETAILS", Icons.dashboard_rounded),
                    const SizedBox(height: 12),
                    _buildBentoGrid(violation, severity),
                    const SizedBox(height: 24),

                    // Schedule Timeline
                    _buildSectionHeader("Process Timeline", Icons.timeline_rounded),
                    const SizedBox(height: 16),
                    _buildTimeline(status),
                    const SizedBox(height: 24),

                    // Hearing Details
                    if (_case!['hearings'] != null && (_case!['hearings'] as List).isNotEmpty) ...[
                      _buildSectionHeader("Official Hearing", Icons.calendar_month_rounded),
                      const SizedBox(height: 12),
                      _buildHearingCard((_case!['hearings'] as List).first),
                      const SizedBox(height: 24),
                    ],

                    // Evidence
                    if (_case!['attachments'] != null && (_case!['attachments'] as List).isNotEmpty) ...[
                      _buildSectionHeader("Digital Evidence", Icons.collections_rounded),
                      const SizedBox(height: 12),
                      _buildEvidenceGallery(),
                      const SizedBox(height: 24),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ),
      ],
    ),
    );
  }

  Widget _buildPattern() {
    return Opacity(
      opacity: 0.05,
      child: CustomPaint(
        painter: GridPainter(),
      ),
    );
  }

  Widget _buildStatusCard(String status, Color color, String severity) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 30,
            offset: const Offset(0, 12),
          )
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(color: color.withOpacity(0.12), shape: BoxShape.circle),
            child: Icon(Icons.shield_rounded, color: color, size: 26),
          ),
          const SizedBox(width: 18),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text("Case Status", style: GoogleFonts.outfit(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.textMuted, letterSpacing: 0.5)),
                const SizedBox(height: 4),
                Text(status.toUpperCase(), style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.w900, color: color, letterSpacing: 0.5)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: severity == 'Major' ? AppTheme.accentRose.withOpacity(0.1) : AppTheme.primaryNavy.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: (severity == 'Major' ? AppTheme.accentRose : AppTheme.primaryNavy).withOpacity(0.15)),
            ),
            child: Text(severity.toUpperCase(), 
              style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 1.0,
              color: severity == 'Major' ? AppTheme.accentRose : AppTheme.primaryNavy)),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 4),
      child: Row(
        children: [
          Icon(icon, size: 16, color: AppTheme.textMuted.withOpacity(0.5)),
          const SizedBox(width: 10),
          Text(title.toUpperCase(), style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.w800, color: AppTheme.textMuted, letterSpacing: 2.0)),
        ],
      ),
    );
  }

  Widget _buildBentoGrid(Map<String, dynamic> violation, String severity) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              flex: 2,
              child: _buildBentoCard(
                title: "Offense Title",
                content: violation['title']?.toString() ?? 'N/A',
                icon: Icons.gavel_rounded,
                color: AppTheme.primaryNavy,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              flex: 1,
              child: _buildBentoCard(
                title: "Case ID",
                content: "#00${widget.caseId}",
                icon: Icons.tag_rounded,
                color: AppTheme.accentCyan,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildBentoCard(
                title: "Recorded By",
                content: _case!['creator']?['name']?.toString() ?? 'System',
                icon: Icons.person_outline_rounded,
                color: AppTheme.accentAmber,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildBentoCard(
                title: "Severity Level",
                content: _case!['offense_level']?.toString() ?? severity,
                icon: Icons.bar_chart_rounded,
                color: _getSeverityColor(severity),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _buildBentoCard(
          title: "Description Details",
          content: _case!['description']?.toString() ?? 'No additional details recorded.',
          icon: Icons.subject_rounded,
          color: AppTheme.primarySlate,
        ),
      ],
    );
  }

  Widget _buildBentoCard({required String title, required String content, required IconData icon, required Color color}) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: color.withOpacity(0.06), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: color.withOpacity(0.04),
            blurRadius: 24,
            offset: const Offset(0, 8),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 14, color: color),
              ),
              const SizedBox(width: 10),
              Expanded(child: Text(title.toUpperCase(), style: GoogleFonts.outfit(fontSize: 10, fontWeight: FontWeight.w800, color: AppTheme.textMuted, letterSpacing: 1.2), overflow: TextOverflow.ellipsis)),
            ],
          ),
          const SizedBox(height: 12),
          Text(content, style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.w800, color: AppTheme.textMain, height: 1.3)),
        ],
      ),
    );
  }

  Widget _buildTimeline(String currentStatus) {
    final stages = ["Pending", "Hearing Scheduled", "Resolved"];
    final currentIdx = stages.indexOf(currentStatus);
    
    return Container(
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: Colors.white, 
        borderRadius: BorderRadius.circular(28), 
        border: Border.all(color: AppTheme.primarySlate.withOpacity(0.04)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 20,
            offset: const Offset(0, 8),
          )
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: List.generate(stages.length, (i) {
          final isDone = i <= currentIdx;
          final isLast = i == stages.length - 1;
          return Expanded(
            child: Row(
              children: [
                Column(
                  children: [
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 500),
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: isDone ? AppTheme.accentCyan : Colors.white,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: isDone ? AppTheme.accentCyan : AppTheme.inputBorder, 
                          width: isDone ? 0 : 2
                        ),
                        boxShadow: isDone ? [
                          BoxShadow(color: AppTheme.accentCyan.withOpacity(0.4), blurRadius: 12, offset: const Offset(0, 4))
                        ] : [],
                      ),
                      child: Icon(isDone ? Icons.check_rounded : Icons.circle, color: isDone ? Colors.white : Colors.transparent, size: 20),
                    ),
                    const SizedBox(height: 12),
                    Text(stages[i].split(" ").first, style: GoogleFonts.outfit(fontSize: 10, fontWeight: FontWeight.w800, color: isDone ? AppTheme.textMain : AppTheme.textMuted, letterSpacing: 0.5)),
                  ],
                ),
                if (!isLast) Expanded(
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 500),
                    height: 3, 
                    color: isDone ? AppTheme.accentCyan : AppTheme.inputBorder.withOpacity(0.5), 
                    margin: const EdgeInsets.only(bottom: 26, left: 8, right: 8)
                  )
                ),
              ],
            ),
          );
        }),
      ),
    );
  }

  Widget _buildHearingCard(dynamic hearing) {
    final scheduledAt = hearing['scheduled_at']?.toString() ?? hearing['scheduledAt']?.toString() ?? '';
    final venue = hearing['venue']?.toString() ?? hearing['location']?.toString() ?? 'Location TBA';
    final notes = hearing['notes']?.toString() ?? 'No agenda details provided.';

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: AppTheme.heroGradient,
        borderRadius: BorderRadius.circular(24),
        boxShadow: AppTheme.glassShadow,
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), borderRadius: BorderRadius.circular(16)),
            child: const Icon(Icons.calendar_month_rounded, color: Colors.white, size: 24),
          ),
          const SizedBox(width: 16),
          Expanded(
            flex: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Scheduled Hearing', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.w800, color: Colors.white)),
                const SizedBox(height: 4),
                Text(notes, style: GoogleFonts.outfit(fontSize: 13, color: Colors.white.withOpacity(0.8))),
                const SizedBox(height: 8),
                Text(_formatDateTime(scheduledAt), style: GoogleFonts.outfit(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold)),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            flex: 2,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text("Venue", style: GoogleFonts.outfit(color: Colors.white.withOpacity(0.5), fontSize: 10, fontWeight: FontWeight.w600, letterSpacing: 1)),
                const SizedBox(height: 4),
                Text(venue, style: GoogleFonts.outfit(color: Colors.white.withOpacity(0.9), fontSize: 12, fontWeight: FontWeight.w600)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEvidenceGallery() {
    final attachments = _case!['attachments'] as List;
    return SizedBox(
      height: 140,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: attachments.length,
        itemBuilder: (context, index) {
          final att = attachments[index];
          final isImage = att['file_path'].toString().contains(RegExp(r'\.(jpg|jpeg|png)$'));
          return Container(
            width: 140,
            margin: const EdgeInsets.only(right: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: AppTheme.softShadow,
              image: isImage ? DecorationImage(image: NetworkImage(att['file_path']), fit: BoxFit.cover) : null,
            ),
            child: !isImage ? const Center(child: Icon(Icons.insert_drive_file_rounded, color: AppTheme.textMuted)) : null,
          );
        },
      ),
    );
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

  Color _getStatusColor(String status) {
    if (status == 'Resolved' || status == 'Closed') return AppTheme.accentEmerald;
    if (status == 'Hearing Scheduled') return AppTheme.accentCyan;
    return AppTheme.accentAmber;
  }

  IconData _getSeverityIcon(String severity) {
    if (severity == 'Major') return Icons.warning_rounded;
    if (severity == 'Moderate') return Icons.error_outline_rounded;
    return Icons.info_outline_rounded;
  }

  Color _getSeverityColor(String severity) {
    if (severity == 'Major') return AppTheme.accentRose;
    if (severity == 'Moderate') return AppTheme.accentAmber;
    return AppTheme.accentCyan;
  }
}

class GridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = Colors.white..strokeWidth = 1;
    for (double i = 0; i < size.width; i += 20) {
      canvas.drawLine(Offset(i, 0), Offset(i, size.height), paint);
    }
    for (double i = 0; i < size.height; i += 20) {
      canvas.drawLine(Offset(0, i), Offset(size.width, i), paint);
    }
  }
  @override
  bool shouldRepaint(CustomPainter oldDelegate) => false;
}
