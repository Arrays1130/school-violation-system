import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter/services.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../utils/page_transitions.dart';
import '../widgets/cases_fetch_error_state.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/empty_state_widget.dart';
import 'case_details_screen.dart';
import 'main_layout.dart';
import '../widgets/app_ui.dart';
import '../utils/notification_pagination.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  NotificationScreenState createState() => NotificationScreenState();
}

class NotificationScreenState extends State<NotificationScreen> {
  final ApiService _apiService = ApiService();
  final ScrollController _scrollController = ScrollController();
  List<dynamic> _notifications = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  bool _fetchError = false;
  int _currentPage = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadInitialData();
  }

  void refreshFromPoller() {
    if (!mounted) return;
    _fetchNotifications(showLoading: false, reset: true, forcedRefresh: false);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
            _scrollController.position.maxScrollExtent - 200 &&
        !_isLoadingMore &&
        _currentPage < _lastPage) {
      _loadMore();
    }
  }

  Future<void> _loadInitialData() async {
    final cachedData = await _apiService.getPersistentCache('notifications');
    if (cachedData != null && mounted) {
      setState(() {
        _applyPageResult(cachedData, reset: true);
        _isLoading = false;
      });
    }
    final hadCache = cachedData != null;
    await _fetchNotifications(
      showLoading: _isLoading,
      reset: true,
      forcedRefresh: !hadCache,
    );
  }

  void _applyPageResult(dynamic result, {required bool reset}) {
    final applied = NotificationPagination.applyPage(
      existing: _notifications,
      result: result,
      reset: reset,
      currentPage: _currentPage,
      lastPage: _lastPage,
    );
    _notifications = applied['items'] as List<dynamic>;
    _currentPage = applied['currentPage'] as int;
    _lastPage = applied['lastPage'] as int;
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchNotifications({
    bool showLoading = true,
    bool reset = true,
    bool forcedRefresh = false,
  }) async {
    if (showLoading && mounted) {
      setState(() => _isLoading = true);
    }
    try {
      final dynamic result = await _apiService.getNotifications(
        forcedRefresh: forcedRefresh,
        page: 1,
      );
      if (mounted) {
        setState(() {
          _applyPageResult(result, reset: reset);
          _isLoading = false;
          _fetchError = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _fetchError = _notifications.isEmpty;
        });
      }
    }
  }

  Future<void> _loadMore() async {
    if (_isLoadingMore || _currentPage >= _lastPage) return;
    setState(() => _isLoadingMore = true);
    final nextPage = _currentPage + 1;
    try {
      final result = await _apiService.getNotifications(
        forcedRefresh: true,
        page: nextPage,
      );
      if (mounted) {
        setState(() {
          _applyPageResult(result, reset: false);
          _isLoadingMore = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoadingMore = false);
    }
  }

  Future<void> _syncBadge() async {
    MainLayout.of(context)?.refreshUnreadCount();
  }

  Future<void> _markAllAsRead() async {
    try {
      await _apiService.markAllNotificationsAsRead();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'All notifications marked as read',
            style: GoogleFonts.inter(),
          ),
          backgroundColor: AppTheme.accentCyan,
          behavior: SnackBarBehavior.floating,
        ),
      );
      await _fetchNotifications(showLoading: false);
      await _syncBadge();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Failed to mark all as read',
            style: GoogleFonts.inter(),
          ),
          backgroundColor: AppTheme.accentRose,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _handleNotificationTap(dynamic notification) async {
    HapticFeedback.mediumImpact();
    final String id = notification['id'].toString();
    final Map<String, dynamic> data = notification['data'] is String
        ? _parseNotificationData(notification['data'])
        : _parseNotificationData(notification['data']);
    if (notification['read_at'] == null) {
      try {
        await _apiService.markNotificationAsRead(id);
        await _fetchNotifications(showLoading: false);
        await _syncBadge();
      } catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Failed to mark as read',
              style: GoogleFonts.inter(),
            ),
            backgroundColor: AppTheme.accentRose,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
    if (data.containsKey('case_id')) {
      if (!mounted) return;
      Navigator.push(
        context,
        AppPageTransitions.fadeScale(
          CaseDetailsScreen(caseId: int.parse(data['case_id'].toString())),
        ),
      ).then((_) => _fetchNotifications(showLoading: false));
    } else {
      if (!mounted) return;
      _showDetailsDialog(notification['title'] ?? 'Notification Details', data);
    }
  }

  void _showDetailsDialog(String title, Map<String, dynamic> data) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text(
          title,
          style: GoogleFonts.inter(fontWeight: FontWeight.bold),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              data['message'] ?? 'No additional details.',
              style: GoogleFonts.inter(),
            ),
            const SizedBox(height: 16),
            if (data.containsKey('student_name'))
              _buildDialogInfo("Student", data['student_name']),
            if (data.containsKey('department'))
              _buildDialogInfo("Department", data['department']),
            if (data.containsKey('violation'))
              _buildDialogInfo("Violation", data['violation']),
            if (data.containsKey('schedule'))
              _buildDialogInfo("Schedule", data['schedule']),
            if (data.containsKey('venue'))
              _buildDialogInfo("Venue", data['venue']),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              "CLOSE",
              style: GoogleFonts.inter(
                color: AppTheme.accentCyan,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDialogInfo(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: RichText(
        text: TextSpan(
          style: GoogleFonts.inter(color: AppTheme.textMain),
          children: [
            TextSpan(
              text: "$label: ",
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            TextSpan(text: value),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = _notifications
        .where((n) => n['read_at'] == null)
        .length;
    final bottomPadding =
        AppTheme.bottomNavClearance + MediaQuery.paddingOf(context).bottom;

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: RefreshIndicator(
        onRefresh: () => _fetchNotifications(
          showLoading: false,
          reset: true,
          forcedRefresh: true,
        ),
        color: AppTheme.accentCyan,
        child: CustomScrollView(
          controller: _scrollController,
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(child: _buildHeader(unreadCount)),
            if (_isLoading)
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                sliver: SliverToBoxAdapter(
                  child: ShimmerLoader.buildListSkeleton(),
                ),
              )
            else if (_fetchError && _notifications.isEmpty)
              SliverFillRemaining(
                hasScrollBody: false,
                child: CasesFetchErrorState(
                  onRetry: () => _fetchNotifications(forcedRefresh: true),
                ),
              )
            else if (_notifications.isEmpty)
              SliverFillRemaining(
                hasScrollBody: false,
                child: _buildEmptyState(),
              )
            else
              SliverPadding(
                padding: EdgeInsets.fromLTRB(16, 8, 16, bottomPadding),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      if (index >= _notifications.length) {
                        return const Padding(
                          padding: EdgeInsets.all(16),
                          child: Center(
                            child: CircularProgressIndicator(
                              color: AppTheme.primary,
                              strokeWidth: 2,
                            ),
                          ),
                        );
                      }
                      return RepaintBoundary(
                        child: AppUi.staggerIn(
                          _buildNotificationItem(
                            _notifications[index],
                            index,
                          ),
                          index,
                        ),
                      );
                    },
                    childCount:
                        _notifications.length + (_isLoadingMore ? 1 : 0),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader(int unreadCount) {
    return AppUi.gradientHeader(
      greeting: unreadCount > 0
          ? '$unreadCount unread'
          : 'All caught up',
      title: 'Alerts',
      subtitle: 'Hearings, endorsements, and case updates land here.',
      badge: AppUi.iconCircle(
        icon: Icons.notifications_active_outlined,
        color: AppTheme.primaryNavy,
        size: 36,
        iconSize: 18,
        backgroundColor: Colors.white,
      ),
      trailing: unreadCount > 0
          ? TextButton(
              onPressed: _markAllAsRead,
              style: TextButton.styleFrom(
                foregroundColor: Colors.white,
                backgroundColor: Colors.white.withValues(alpha: 0.12),
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 8,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Text(
                'Mark all read',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                ),
              ),
            )
          : null,
      bottom: AppUi.brandPill(
        label: unreadCount > 0 ? 'Review recommended' : 'No urgent alerts',
        leading: Icon(
          unreadCount > 0
              ? Icons.notifications_active_outlined
              : Icons.check_circle_outline_rounded,
          size: 14,
          color: Colors.white.withValues(alpha: 0.92),
        ),
      ),
      watermark: AppUi.ilinkWatermark(),
    );
  }

  Map<String, dynamic> _parseNotificationData(dynamic raw) {
    if (raw == null) return {};
    if (raw is Map) return Map<String, dynamic>.from(raw);
    if (raw is String) {
      try {
        final decoded = jsonDecode(raw);
        if (decoded is Map) return Map<String, dynamic>.from(decoded);
      } catch (_) {}
    }
    return {};
  }

  Widget _buildEmptyState() {
    return const EmptyStateWidget(
      icon: Icons.notifications_off_rounded,
      title: "All caught up!",
      message:
          "You have no new notifications at the moment. We'll alert you when there's an update.",
    );
  }

  Widget _buildNotificationItem(dynamic notif, int index) {
    final isUnread = notif['read_at'] == null;
    final Map<String, dynamic> data = _parseNotificationData(notif['data']);

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: AppUi.surfaceCard(
        padding: EdgeInsets.zero,
        clip: true,
        borderColor: isUnread
            ? AppTheme.accentCyan.withValues(alpha: 0.22)
            : AppTheme.inputBorder.withValues(alpha: 0.5),
        color: isUnread
            ? AppTheme.accentCyan.withValues(alpha: 0.03)
            : AppTheme.bgCard,
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: () {
              HapticFeedback.mediumImpact();
              _handleNotificationTap(notif);
            },
            child: IntrinsicHeight(
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (isUnread)
                    Container(
                      width: 4,
                      decoration: BoxDecoration(
                        gradient: AppTheme.accentGradient,
                        borderRadius: const BorderRadius.horizontal(
                          left: Radius.circular(20),
                        ),
                      ),
                    ),
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 46,
                            height: 46,
                            decoration: BoxDecoration(
                              gradient: isUnread
                                  ? AppTheme.accentGradient
                                  : const LinearGradient(
                                      colors: [
                                        Color(0xFFCBD5E1),
                                        Color(0xFF94A3B8),
                                      ],
                                    ),
                              borderRadius: BorderRadius.circular(14),
                            ),
                            child: const Icon(
                              Icons.gavel_rounded,
                              color: Colors.white,
                              size: 20,
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        notif['title'] ??
                                            data['title'] ??
                                            'Record Update',
                                        style: GoogleFonts.inter(
                                          fontWeight: isUnread
                                              ? FontWeight.w800
                                              : FontWeight.w600,
                                          fontSize: 14,
                                          color: isUnread
                                              ? AppTheme.textMain
                                              : AppTheme.textMuted,
                                        ),
                                      ),
                                    ),
                                    if (isUnread)
                                      AppUi.brandPill(
                                        label: 'NEW',
                                        textColor: AppTheme.accentCyan,
                                        backgroundColor: AppTheme.accentCyan
                                            .withValues(alpha: 0.1),
                                        borderColor: AppTheme.accentCyan
                                            .withValues(alpha: 0.16),
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 7,
                                          vertical: 4,
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  data['message'] ??
                                      'Action required on case record.',
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    color: isUnread
                                        ? AppTheme.textSub
                                        : AppTheme.textMuted,
                                    height: 1.4,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 10),
                                Row(
                                  children: [
                                    const Icon(
                                      Icons.access_time_rounded,
                                      size: 10,
                                      color: AppTheme.textHint,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      _formatDate(notif['created_at']),
                                      style: GoogleFonts.inter(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w600,
                                        color: AppTheme.textHint,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr).toLocal();
      final now = DateTime.now();
      final diff = now.difference(date);
      if (diff.inMinutes < 60) return "${diff.inMinutes}m ago";
      if (diff.inHours < 24) return "${diff.inHours}h ago";
      return "${date.day}/${date.month}/${date.year}";
    } catch (e) {
      return dateStr;
    }
  }
}
