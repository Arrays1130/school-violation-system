import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import 'package:flutter/services.dart';
import 'dart:ui';

class CaseDetailsScreen extends StatefulWidget {
  final int caseId;
  const CaseDetailsScreen({Key? key, required this.caseId}) : super(key: key);

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
    _fetchDetails();
  }

  Future<void> _fetchDetails() async {
    try {
      final result = await _apiService.getCaseDetails(widget.caseId);
      setState(() {
        _case = result;
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
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.accentPurple))
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
          const Icon(Icons.error_outline_rounded, size: 64, color: AppTheme.accentRose),
          const SizedBox(height: 16),
          Text("Failed to load case data", style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
          TextButton(onPressed: _fetchDetails, child: const Text("Try Again"))
        ],
      ),
    );
  }

  Widget _buildMainContent() {
    final student = _case?['student'] ?? {};
    final violation = _case?['violation'] ?? {};
    final severity = violation['severity'] ?? 'Minor';
    final status = _case?['status'] ?? 'Pending';
    final statusColor = _getStatusColor(status);

    return CustomScrollView(
      slivers: [
        // ── Sticky Header with Background Pattern ──
        SliverAppBar(
          expandedHeight: 240,
          pinned: true,
          elevation: 0,
          stretch: true,
          backgroundColor: AppTheme.primaryNavy,
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
                Container(decoration: const BoxDecoration(gradient: AppTheme.heroGradient)),
                _buildPattern(),
                // User Profile Header
                Positioned(
                  bottom: 40,
                  left: 24,
                  right: 24,
                  child: Row(
                    children: [
                      Hero(
                        tag: 'case_${widget.caseId}_avatar',
                        child: Container(
                          padding: const EdgeInsets.all(3),
                          decoration: BoxDecoration(color: Colors.white.withOpacity(0.2), shape: BoxShape.circle),
                          child: CircleAvatar(
                            radius: 38,
                            backgroundColor: Colors.white,
                            child: Icon(Icons.person_rounded, size: 40, color: AppTheme.primaryNavy),
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                Text(
                  student['full_name'] ?? 'Unknown Student',
                  style: GoogleFonts.outfit(fontSize: 22, fontWeight: FontWeight.w900, color: Colors.white),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), borderRadius: BorderRadius.circular(8)),
                  child: Text(
                    "SECTION ${student['section'] ?? 'N/A'}".toUpperCase(),
                    style: GoogleFonts.outfit(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: 1),
                  ),
                ),
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

        // ── Main Body ──
        SliverToBoxAdapter(
          child: Transform.translate(
            offset: const Offset(0, -20),
            child: Container(
              decoration: const BoxDecoration(
                color: AppTheme.bgLight,
                borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
              ),
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 32, 20, 100),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Status Badge Card
                    _buildStatusCard(status, statusColor, severity),
                    const SizedBox(height: 24),

                    // Violation Info
                    _buildSectionHeader("violation description", Icons.gavel_rounded),
                    const SizedBox(height: 12),
                    _buildInfoCard(
                      title: violation?['title'] ?? 'N/A',
                      content: _case!['description'] ?? 'No additional details recorded.',
                    ),
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
                      _buildHearingCard((_case!['hearings'] as List).last),
                      const SizedBox(height: 24),
                    ],

                    // Evidence
                    if (_case!['attachments'] != null && (_case!['attachments'] as List).isNotEmpty) ...[
                      _buildSectionHeader("Digital Evidence", Icons.collections_rounded),
                      const SizedBox(height: 12),
                      _buildEvidenceGallery(),
                      const SizedBox(height: 24),
                    ],

                    // Metadata Bento
                    _buildSectionHeader("Record Details", Icons.fingerprint_rounded),
                    const SizedBox(height: 12),
                    _buildMetadataGrid(),
                  ],
                ),
              ),
            ),
          ),
        ),
      ],
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
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: AppTheme.softShadow,
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: color.withOpacity(0.1), shape: BoxShape.circle),
            child: Icon(Icons.shield_rounded, color: color, size: 24),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text("Case Status", style: GoogleFonts.outfit(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                const SizedBox(height: 2),
                Text(status.toUpperCase(), style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.w900, color: color)),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: severity == 'Major' ? AppTheme.accentRose.withOpacity(0.1) : AppTheme.accentIndigo.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(severity.toUpperCase(), 
              style: GoogleFonts.outfit(fontSize: 10, fontWeight: FontWeight.w900, 
              color: severity == 'Major' ? AppTheme.accentRose : AppTheme.accentIndigo)),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 14, color: AppTheme.accentPurple),
        const SizedBox(width: 8),
        Text(title.toUpperCase(), style: GoogleFonts.outfit(fontSize: 10, fontWeight: FontWeight.w900, color: AppTheme.textMuted, letterSpacing: 1.5)),
      ],
    );
  }

  Widget _buildInfoCard({required String title, required String content}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: AppTheme.softShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.w800, color: AppTheme.textMain)),
          const SizedBox(height: 10),
          Text(content, style: GoogleFonts.outfit(fontSize: 13, color: AppTheme.textSub, height: 1.5)),
        ],
      ),
    );
  }

  Widget _buildTimeline(String currentStatus) {
    final stages = ["Pending", "Hearing Scheduled", "Resolved"];
    final currentIdx = stages.indexOf(currentStatus);
    
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(24), boxShadow: AppTheme.softShadow),
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
                    Container(
                      width: 32,
                      height: 32,
                      decoration: BoxDecoration(
                        color: isDone ? AppTheme.accentPurple : AppTheme.bgLight,
                        shape: BoxShape.circle,
                        border: Border.all(color: isDone ? AppTheme.accentPurple : AppTheme.inputBorder, width: 2),
                      ),
                      child: Icon(isDone ? Icons.check_rounded : Icons.radio_button_unchecked, color: isDone ? Colors.white : AppTheme.textHint, size: 16),
                    ),
                    const SizedBox(height: 8),
                    Text(stages[i].split(" ").first, style: GoogleFonts.outfit(fontSize: 9, fontWeight: FontWeight.bold, color: isDone ? AppTheme.textMain : AppTheme.textMuted)),
                  ],
                ),
                if (!isLast) Expanded(child: Container(height: 2, color: isDone ? AppTheme.accentPurple : AppTheme.inputBorder, margin: const EdgeInsets.only(bottom: 22))),
              ],
            ),
          );
        }),
      ),
    );
  }

  Widget _buildHearingCard(dynamic hearing) {
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
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(_formatDateTime(hearing['scheduled_at'] ?? ''), style: GoogleFonts.outfit(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w800)),
                const SizedBox(height: 4),
                Text(hearing['venue'] ?? 'Location TBA', style: GoogleFonts.outfit(color: Colors.white.withOpacity(0.7), fontSize: 12, fontWeight: FontWeight.w600)),
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

  Widget _buildMetadataGrid() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(24), boxShadow: AppTheme.softShadow),
      child: Column(
        children: [
          _buildMetaRow("Recorded By", _case!['creator']?['name'] ?? 'System', Icons.person_outline_rounded),
          const Divider(height: 24),
          _buildMetaRow("Case ID", "#00${widget.caseId}", Icons.tag_rounded),
          const Divider(height: 24),
          _buildMetaRow("Offense Lvl", _case!['offense_level'] ?? 'Minor', Icons.bar_chart_rounded),
        ],
      ),
    );
  }

  Widget _buildMetaRow(String label, String value, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppTheme.textMuted),
        const SizedBox(width: 12),
        Text(label, style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.textMuted)),
        const Spacer(),
        Text(value, style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.w800, color: AppTheme.textMain)),
      ],
    );
  }

  String _formatDateTime(String dateTimeStr) {
    try {
      final date = DateTime.parse(dateTimeStr).toLocal();
      final months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return "${months[date.month - 1]} ${date.day}, ${date.year}";
    } catch (e) { return dateTimeStr; }
  }

  Color _getStatusColor(String status) {
    if (status == 'Resolved' || status == 'Closed') return AppTheme.accentEmerald;
    if (status == 'Hearing Scheduled') return AppTheme.accentPurple;
    return AppTheme.accentAmber;
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
