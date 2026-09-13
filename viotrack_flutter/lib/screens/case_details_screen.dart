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
  bool _acknowledgedLocally = false;
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
    if (_acknowledging || _acknowledgedLocally) return;
    if (ApiService.isOfflineNotifier.value) {
      AppUi.showSnack(
        context,
        'You are offline. Try again when connected.',
        kind: SnackKind.error,
      );
      return;
    }
    setState(() {
      _acknowledging = true;
      _acknowledgedLocally = true;
    });
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
      setState(() => _acknowledgedLocally = false);
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
    final showAcknowledge = endorsed && status != 'Closed' && !_acknowledgedLocally;

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
    final showAcknowledge = endorsed && status != 'Closed' && !_acknowledgedLocally;
    final bottomPad = showAcknowledge ? 140.0 : 40.0;

    return RefreshIndicator(
      onRefresh: () => _fetchDetails(force: true),
      color: AppTheme.primary,
      child: CustomScrollView(
        slivers: [
          if (_showingStaleData)
            SliverToBoxAdapter(
              child: CaseStaleBanner(onRetry: () => _fetchDetails(force: true)),
            ),
          // Sticky white header
          SliverAppBar(
            pinned: true,
            elevation: 0,
            scrolledUnderElevation: 0,
            backgroundColor: Colors.white,
            surfaceTintColor: Colors.white,
            leading: IconButton(
              tooltip: 'Go back',
              icon: const Icon(
                Icons.arrow_back_ios_new_rounded,
                size: 18,
                color: AppTheme.textMain,
              ),
              onPressed: () => Navigator.pop(context),
            ),
            title: Text(
              'Case details',
              style: GoogleFonts.inter(
                fontSize: 17,
                fontWeight: FontWeight.w600,
                color: AppTheme.textMain,
                letterSpacing: -0.3,
              ),
            ),
            bottom: PreferredSize(
              preferredSize: const Size.fromHeight(0.5),
              child: Divider(
                height: 0.5,
                thickness: 0.5,
                color: AppTheme.inputBorder.withValues(alpha: 0.95),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
              child: AppUi.surfaceCard(
                padding: const EdgeInsets.all(16),
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
                            size: 56,
                            radius: 18,
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              student['full_name'] ??
                                  'Student name unavailable',
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 18,
                                fontWeight: FontWeight.w700,
                                color: AppTheme.textMain,
                                letterSpacing: -0.3,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Row(
                              children: [
                                AppUi.statusBadgeForCase(
                                  Map<String, dynamic>.from(_case!),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  severity,
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: AppTheme.textMuted,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const Icon(
                        Icons.chevron_right_rounded,
                        color: AppTheme.textMuted,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),

          SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.fromLTRB(20, 16, 20, bottomPad),
              child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      AppUi.staggerIn(
                        _buildStatusCard(status, statusColor, severity),
                        0,
                      ),
                      const SizedBox(height: 16),

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
                      const SizedBox(height: 16),

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
                      const SizedBox(height: 16),

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
                        const SizedBox(height: 16),
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
                        const SizedBox(height: 16),
                      ],
                    ],
                  ),
                ),
              ),
          ],
        ),
      );
  }

  Widget _buildStatusCard(String status, Color color, String severity) {
    return AppUi.surfaceCard(
      padding: const EdgeInsets.all(16),
      radius: 20,
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
                    : "000-${widget.caseId}",
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
    return AppUi.surfaceCard(
      padding: const EdgeInsets.all(16),
      radius: 20,
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
      padding: const EdgeInsets.all(16),
      radius: 20,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              AppUi.iconCircle(
                icon: Icons.calendar_month_rounded,
                color: AppTheme.primary,
                size: 48,
                iconSize: 22,
                backgroundColor: AppTheme.primaryLight,
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
    return AppUi.surfaceCard(
      padding: const EdgeInsets.all(16),
      radius: 20,
      child: SizedBox(
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
