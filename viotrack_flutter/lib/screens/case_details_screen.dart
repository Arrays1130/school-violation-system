import 'package:flutter/material.dart';
import 'student_profile_screen.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../utils/page_transitions.dart';
import 'package:flutter/services.dart';
import '../widgets/app_ui.dart';
import '../widgets/empty_state_widget.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/case_stale_banner.dart';
import '../widgets/case_timeline_widget.dart';
import '../utils/case_status.dart';
import '../widgets/vt_ui.dart';
import 'package:flutter_animate/flutter_animate.dart';

class CaseDetailsScreen extends StatefulWidget {
  final int caseId;
  final Map<String, dynamic>? initialData;
  const CaseDetailsScreen({super.key, required this.caseId, this.initialData});

  @override
  State<CaseDetailsScreen> createState() => _CaseDetailsScreenState();
}

class _CaseDetailsScreenState extends State<CaseDetailsScreen> {
  final ApiService _apiService = ApiService();
  Map<String, dynamic>? _case;
  bool _isLoading = true;
  bool _acknowledging = false;
  bool _showingStaleData = false;
  Map<String, String>? _authHeaders;

  @override
  void initState() {
    super.initState();
    if (widget.initialData != null) {
      _case = widget.initialData;
      _isLoading = false;
    }
    _loadAuthHeaders();
    _fetchDetails();
  }

  Future<void> _loadAuthHeaders() async {
    final headers = await _apiService.authHeadersForImages();
    if (mounted) setState(() => _authHeaders = headers);
  }

  Future<void> _fetchDetails({bool force = false}) async {
    try {
      final result = await _apiService.getCaseDetails(
        widget.caseId,
        forcedRefresh: force,
      );
      if (mounted) {
        setState(() {
          _case = result;
          _isLoading = false;
          _showingStaleData = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _showingStaleData = _case != null;
        });
      }
    }
  }

  Future<void> _acknowledgeCase() async {
    if (_acknowledging) return;
    setState(() => _acknowledging = true);
    try {
      await _apiService.acknowledgeCase(widget.caseId);
      if (!mounted) return;
      AppUi.showSnack(
        context,
        'Case acknowledged.',
        kind: SnackKind.success,
      );
      await _fetchDetails(force: true);
    } catch (e) {
      if (!mounted) return;
      AppUi.showSnack(
        context,
        'Could not acknowledge case.',
        kind: SnackKind.error,
      );
    } finally {
      if (mounted) setState(() => _acknowledging = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final endorsed = CaseStatus.isEndorsed(
      _case == null ? null : Map<String, dynamic>.from(_case!),
    );
    final status = CaseStatus.normalize(_case?['status']?.toString());
    final showAcknowledge = endorsed && status != 'Closed';

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      floatingActionButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingActionButton: showAcknowledge ? _buildAcknowledgeBar() : null,
      body: _isLoading
          ? SafeArea(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 16,
                ),
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
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const EmptyStateWidget(
              icon: Icons.error_outline_rounded,
              title: 'Data unavailable',
              message:
                  "We couldn't load the details for this case. Check your connection and try again.",
            ),
            const SizedBox(height: 20),
            AppUi.retryButton(onPressed: () => _fetchDetails(force: true)),
          ],
        ),
      ),
    );
  }

  Widget _buildMainContent() {
    final student = _case?['student'] ?? {};
    final studentName = student['full_name']?.toString() ?? 'Student';
    final violation = _case?['violation'] ?? {};
    final severity = violation['severity']?.toString() ?? 'Minor';
    final status = CaseStatus.normalize(_case?['status']?.toString());
    final endorsed = CaseStatus.isEndorsed(Map<String, dynamic>.from(_case!));
    final statusColor = CaseStatus.colorFor(status, endorsed: endorsed);
    final showAcknowledge = endorsed && status != 'Closed';
    final bottomPad = showAcknowledge ? 140.0 : 40.0;

    return RefreshIndicator(
      onRefresh: () => _fetchDetails(force: true),
      color: AppTheme.accentCyan,
      child: CustomScrollView(
        slivers: [
          if (_showingStaleData)
            SliverToBoxAdapter(
              child: CaseStaleBanner(onRetry: () => _fetchDetails(force: true)),
            ),
          // Sticky header with refined gradient
          SliverAppBar(
            expandedHeight: 236,
            pinned: true,
            elevation: 0,
            stretch: true,
            backgroundColor: AppTheme.primaryNavy,
            leading: IconButton(
              tooltip: 'Go back',
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
                          AppTheme.primaryNavy,
                          AppTheme.primaryIndigo,
                          _getSeverityColor(severity).withValues(alpha: 0.9),
                        ],
                      ),
                    ),
                  ),
                  _buildPattern(),
                  // User Profile Header
                  Positioned(
                    bottom: 34,
                    left: 24,
                    right: 24,
                    child: GestureDetector(
                      onTap: () {
                        HapticFeedback.lightImpact();
                        Navigator.push(
                          context,
                          AppPageTransitions.fadeSlide(
                            StudentProfileScreen(student: student),
                          ),
                        );
                      },
                      child: Row(
                        children: [
                          Hero(
                            tag: 'case_${widget.caseId}_avatar',
                            child: Material(
                              color: Colors.transparent,
                              child: AppUi.initialsAvatar(
                                studentName,
                                size: 76,
                                radius: 22,
                              ),
                            ),
                          ),
                          const SizedBox(width: 20),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                AppUi.brandPill(
                                  label: 'Case overview',
                                  leading: Icon(
                                    Icons.shield_outlined,
                                    size: 14,
                                    color: Colors.white.withValues(alpha: 0.92),
                                  ),
                                ),
                                const SizedBox(height: 10),
                                Text(
                                  student['full_name'] ??
                                      'Student name unavailable',
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.inter(
                                    fontSize: 24,
                                    fontWeight: FontWeight.w900,
                                    color: Colors.white,
                                    letterSpacing: -0.5,
                                    height: 1.1,
                                    shadows: [
                                      Shadow(
                                        color: Colors.black.withValues(
                                          alpha: 0.15,
                                        ),
                                        blurRadius: 10,
                                        offset: const Offset(0, 4),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 12,
                                    vertical: 5,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.14),
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(
                                      color: Colors.white.withValues(
                                        alpha: 0.28,
                                      ),
                                    ),
                                  ),
                                  child: Text(
                                    severity.toUpperCase(),
                                    style: GoogleFonts.inter(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w900,
                                      color: Colors.white.withValues(
                                        alpha: 0.95,
                                      ),
                                      letterSpacing: 1.0,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          AppUi.iconCircle(
                            icon: Icons.arrow_outward_rounded,
                            color: Colors.white,
                            size: 46,
                            iconSize: 20,
                            backgroundColor: Colors.white12,
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
                  padding: EdgeInsets.fromLTRB(20, 32, 20, bottomPad),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      AppUi.staggerIn(
                        _buildStatusCard(status, statusColor, severity),
                        0,
                      ),
                      const SizedBox(height: 24),

                      AppUi.staggerIn(
                        AppUi.inlineSectionHeader(
                          'Case details',
                          icon: Icons.dashboard_rounded,
                        ),
                        1,
                      ),
                      const SizedBox(height: 12),
                      AppUi.staggerIn(
                        _buildBentoGrid(violation, severity, status),
                        2,
                      ),
                      const SizedBox(height: 24),

                      AppUi.staggerIn(
                        AppUi.inlineSectionHeader(
                          'Process timeline',
                          icon: Icons.timeline_rounded,
                          subtitle: 'Track how this case moved through review.',
                        ),
                        3,
                      ),
                      const SizedBox(height: 16),
                      AppUi.staggerIn(
                        CaseTimelineWidget(currentStatus: status),
                        4,
                      ),
                      const SizedBox(height: 24),

                      if (_case!['hearings'] != null &&
                          (_case!['hearings'] as List).isNotEmpty) ...[
                        AppUi.staggerIn(
                          AppUi.inlineSectionHeader(
                            'Official hearing',
                            icon: Icons.calendar_month_rounded,
                          ),
                          5,
                        ),
                        const SizedBox(height: 12),
                        AppUi.staggerIn(
                          _buildHearingCard(
                            (_case!['hearings'] as List).first,
                          ),
                          6,
                        ),
                        const SizedBox(height: 24),
                      ],

                      if (_case!['attachments'] != null &&
                          (_case!['attachments'] as List).isNotEmpty) ...[
                        AppUi.staggerIn(
                          AppUi.inlineSectionHeader(
                            'Digital evidence',
                            icon: Icons.collections_rounded,
                            subtitle: 'Photos and files attached to this case.',
                          ),
                          5,
                        ),
                        const SizedBox(height: 12),
                        AppUi.staggerIn(_buildEvidenceGallery(), 6),
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
    return Opacity(opacity: 0.035, child: CustomPaint(painter: GridPainter()));
  }

  Widget _buildStatusCard(String status, Color color, String severity) {
    return AppUi.surfaceCard(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
      radius: 24,
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(Icons.shield_rounded, color: color, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Case status',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textMuted,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 8),
                VtStatusChip.fromCase(Map<String, dynamic>.from(_case!)),
              ],
            ),
          ),
          AppUi.brandPill(
            label: severity,
            textColor: severity == 'Major'
                ? AppTheme.accentRose
                : AppTheme.primaryNavy,
            backgroundColor: (severity == 'Major'
                    ? AppTheme.accentRose
                    : AppTheme.primaryNavy)
                .withValues(alpha: 0.1),
            borderColor: (severity == 'Major'
                    ? AppTheme.accentRose
                    : AppTheme.primaryNavy)
                .withValues(alpha: 0.15),
          ),
        ],
      ),
    );
  }


  String _formatSeverityLevel() {
    final level = _case?['offense_level'];
    if (level == null) return 'Not specified';

    final raw = level.toString().trim();
    if (raw.isEmpty) return 'Not specified';

    if (RegExp(r'^\d+$').hasMatch(raw)) {
      return 'Level $raw';
    }

    if (RegExp(r'^[ivxlcdm]+$', caseSensitive: false).hasMatch(raw)) {
      return 'Level ${raw.toUpperCase()}';
    }

    return raw;
  }

  Widget _buildBentoGrid(
    Map<String, dynamic> violation,
    String severity,
    String status,
  ) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              flex: 2,
              child: _buildBentoCard(
                title: "Offense Title",
                content:
                    violation['title']?.toString() ??
                    'Violation title unavailable',
                icon: Icons.gavel_rounded,
                color: AppTheme.primaryNavy,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              flex: 1,
              child: _buildBentoCard(
                title: "Case Code",
                content: (_case?['case_code']?.toString().isNotEmpty == true)
                    ? _case!['case_code'].toString()
                    : "CASE-${widget.caseId.toString().padLeft(5, '0')}",
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
                content:
                    _case!['creator']?['name']?.toString() ??
                    'Dean office staff',
                icon: Icons.person_outline_rounded,
                color: AppTheme.accentAmber,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildBentoCard(
                title: "Offense Level",
                content: _formatSeverityLevel(),
                icon: Icons.bar_chart_rounded,
                color: _getSeverityColor(severity),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _buildBentoCard(
          title: "Description Details",
          content:
              _case!['description']?.toString() ??
              'No additional details recorded.',
          icon: Icons.subject_rounded,
          color: AppTheme.primarySlate,
        ),
        const SizedBox(height: 12),
        _buildSanctionCard(status),
      ],
    );
  }

  Widget _buildSanctionCard(String status) {
    final sanction = _case?['sanction']?.toString().trim();
    final display = (sanction != null && sanction.isNotEmpty)
        ? sanction
        : 'Sanction pending determination.';
    final isServed = status == 'Closed' && sanction != null && sanction.isNotEmpty;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFFCD34D).withValues(alpha: 0.6)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: const Color(0xFFFDE68A),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.balance_rounded,
                  size: 20,
                  color: Color(0xFFB45309),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'ASSIGNED SANCTION',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFFB45309),
                    letterSpacing: 1.4,
                  ),
                ),
              ),
              if (isServed)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.accentEmerald.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'SERVED',
                    style: GoogleFonts.inter(
                      fontSize: 9,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.accentEmerald,
                      letterSpacing: 0.8,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            display,
            style: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF78350F),
              height: 1.45,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBentoCard({
    required String title,
    required String content,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.08), width: 1.2),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.04),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
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
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 14, color: color),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  title.toUpperCase(),
                  style: GoogleFonts.inter(
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textMuted,
                    letterSpacing: 1.1,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            content,
            maxLines: 4,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.inter(
              fontSize: 15,
              fontWeight: FontWeight.w800,
              color: AppTheme.textMain,
              height: 1.3,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHearingCard(dynamic hearing) {
    final scheduledAt =
        hearing['scheduled_at']?.toString() ??
        hearing['scheduledAt']?.toString() ??
        '';
    final venue =
        hearing['venue']?.toString() ??
        hearing['location']?.toString() ??
        'Venue not yet assigned';
    final notes = hearing['notes']?.toString() ?? 'No agenda details provided.';

    return AppUi.surfaceCard(
      padding: const EdgeInsets.all(20),
      radius: 24,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              AppUi.iconCircle(
                icon: Icons.calendar_month_rounded,
                color: Colors.white,
                size: 48,
                iconSize: 22,
                backgroundColor: Colors.white.withValues(alpha: 0.15),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(
                  'Scheduled hearing',
                  style: GoogleFonts.inter(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textMain,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            notes,
            maxLines: 3,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.inter(
              fontSize: 13,
              color: AppTheme.textMuted,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 14),
          AppUi.brandPill(
            label: _formatDateTime(scheduledAt),
            textColor: AppTheme.primaryNavy,
            backgroundColor: AppTheme.primaryLight,
            borderColor: AppTheme.primary.withValues(alpha: 0.12),
            leading: Icon(
              Icons.schedule_rounded,
              size: 14,
              color: AppTheme.primaryNavy,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Venue',
            style: GoogleFonts.inter(
              color: AppTheme.textMuted,
              fontSize: 11,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.4,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            venue,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.inter(
              color: AppTheme.textMain,
              fontSize: 14,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAcknowledgeBar() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 420),
        child: Material(
          color: Colors.transparent,
          borderRadius: BorderRadius.circular(24),
          child: InkWell(
            onTap: _acknowledging ? null : _acknowledgeCase,
            borderRadius: BorderRadius.circular(24),
            child: Ink(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
              decoration: BoxDecoration(
                gradient: AppTheme.heroGradient,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: Colors.white.withValues(alpha: 0.08)),
                boxShadow: AppTheme.floatShadow,
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  AppUi.iconCircle(
                    icon: Icons.check_circle_outline_rounded,
                    color: Colors.white,
                    size: 40,
                    iconSize: 18,
                    backgroundColor: Colors.white12,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Acknowledge case',
                          style: GoogleFonts.inter(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Confirm that the student has seen the endorsed case.',
                          style: GoogleFonts.inter(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w500,
                            color: Colors.white.withValues(alpha: 0.76),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  _acknowledging
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Icon(
                          Icons.check_circle_outline_rounded,
                          color: Colors.white,
                        ),
                ],
              ),
            ),
          ),
        ),
      ),
    )
        .animate()
        .fadeIn(duration: 350.ms)
        .slideY(begin: 0.35, end: 0, curve: Curves.easeOutCubic);
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
          final url =
              (att['mobile_download_url'] ?? att['file_path'])?.toString() ??
              '';
          final isImage = url.contains(
            RegExp(r'\.(jpg|jpeg|png|webp)', caseSensitive: false),
          );
          final cacheSize = (140 * MediaQuery.devicePixelRatioOf(context)).round();
          return Container(
            width: 140,
            margin: const EdgeInsets.only(right: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: AppTheme.softShadow,
            ),
            clipBehavior: Clip.antiAlias,
            child: isImage && _authHeaders != null
                ? Image.network(
                    url,
                    headers: _authHeaders,
                    fit: BoxFit.cover,
                    cacheWidth: cacheSize,
                    cacheHeight: cacheSize,
                    filterQuality: FilterQuality.medium,
                    errorBuilder: (context, error, stackTrace) => const Center(
                      child: Icon(
                        Icons.broken_image_rounded,
                        color: AppTheme.textMuted,
                      ),
                    ),
                  )
                : const Center(
                    child: Icon(
                      Icons.insert_drive_file_rounded,
                      color: AppTheme.textMuted,
                    ),
                  ),
          );
        },
      ),
    );
  }

  String _formatDateTime(String dateTimeStr) {
    if (dateTimeStr.isEmpty) return 'Schedule not yet set';
    try {
      final date = DateTime.parse(dateTimeStr).toLocal();
      final months = [
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
      return "${months[date.month - 1]} ${date.day}, ${date.year} at $hour:$minute $ampm";
    } catch (e) {
      return dateTimeStr;
    }
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
    final paint = Paint()
      ..color = Colors.white
      ..strokeWidth = 1;
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
