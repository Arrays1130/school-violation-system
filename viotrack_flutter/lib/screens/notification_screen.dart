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
import '../widgets/vt_ui.dart';
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
  bool _unreadOnly = false;
  DateTime? _lastRefreshedAt;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadInitialData();
  }

  void refreshFromPoller() {
    if (!mounted) return;
    _fetchNotifications(showLoading: false, reset: true, forcedRefresh: true);
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
          _lastRefreshedAt = DateTime.now();
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
    if (ApiService.isOfflineNotifier.value) {
      AppUi.showSnack(
        context,
        'You are offline. Try again when connected.',
        kind: SnackKind.error,
      );
      return;
    }
    try {
      await _apiService.markAllNotificationsAsRead();
      if (!mounted) return;
      AppUi.showSnack(
        context,
        'All notifications marked as read',
        kind: SnackKind.success,
      );
      await _fetchNotifications(showLoading: false);
      await _syncBadge();
    } catch (e) {
      if (!mounted) return;
      AppUi.showSnack(
        context,
        'Failed to mark all as read',
        kind: SnackKind.error,
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
      if (ApiService.isOfflineNotifier.value) {
        if (mounted) {
          AppUi.showSnack(
            context,
            'You are offline. The alert was not marked read.',
            kind: SnackKind.error,
          );
        }
      } else {
      try {
        await _apiService.markNotificationAsRead(id);
        await _fetchNotifications(showLoading: false);
        await _syncBadge();
      } catch (e) {
        if (!mounted) return;
        AppUi.showSnack(
          context,
          'Failed to mark as read',
          kind: SnackKind.error,
        );
      }
      }
    }
    if (data.containsKey('case_id')) {
      if (!mounted) return;
      Navigator.push(
        context,
        AppPageTransitions.fadeSlide(
          CaseDetailsScreen(caseId: int.parse(data['case_id'].toString())),
        ),
      ).then((_) => _fetchNotifications(showLoading: false));
    } else {
      if (!mounted) return;
      _showDetailsSheet(
        notification['title'] ?? 'Notification details',
        data,
      );
    }
  }

  void _showDetailsSheet(String title, Map<String, dynamic> data) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          child: Container(
            decoration: BoxDecoration(
              color: AppTheme.bgCard,
              borderRadius: BorderRadius.circular(24),
              boxShadow: AppTheme.floatShadow,
            ),
            child: SafeArea(
              top: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Center(
                      child: Container(
                        width: 36,
                        height: 4,
                        decoration: BoxDecoration(
                          color: AppTheme.inputBorder,
                          borderRadius: BorderRadius.circular(100),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        AppUi.iconCircle(
                          icon: Icons.notifications_active_outlined,
                          color: AppTheme.primary,
                          size: 40,
                          iconSize: 18,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            title,
                            style: GoogleFonts.inter(
                              fontSize: 17,
                              fontWeight: FontWeight.w700,
                              color: AppTheme.textMain,
                              letterSpacing: -0.3,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Text(
                      data['message']?.toString() ?? 'No additional details.',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        height: 1.45,
                        color: AppTheme.textSub,
                      ),
                    ),
                    if (data.containsKey('student_name') ||
                        data.containsKey('department') ||
                        data.containsKey('violation') ||
                        data.containsKey('schedule') ||
                        data.containsKey('venue')) ...[
                      const SizedBox(height: 16),
                      AppUi.surfaceCard(
                        padding: const EdgeInsets.all(14),
                        color: AppTheme.bgLight,
                        borderColor: AppTheme.inputBorder.withValues(alpha: 0.6),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (data.containsKey('student_name'))
                              _buildDialogInfo(
                                'Student',
                                data['student_name'].toString(),
                              ),
                            if (data.containsKey('department'))
                              _buildDialogInfo(
                                'Department',
                                data['department'].toString(),
                              ),
                            if (data.containsKey('violation'))
                              _buildDialogInfo(
                                'Violation',
                                data['violation'].toString(),
                              ),
                            if (data.containsKey('schedule'))
                              _buildDialogInfo(
                                'Schedule',
                                data['schedule'].toString(),
                              ),
                            if (data.containsKey('venue'))
                              _buildDialogInfo(
                                'Venue',
                                data['venue'].toString(),
                              ),
                          ],
                        ),
                      ),
                    ],
                    const SizedBox(height: 18),
                    SizedBox(
                      height: 48,
                      child: FilledButton(
                        onPressed: () => Navigator.pop(context),
                        style: FilledButton.styleFrom(
                          backgroundColor: AppTheme.primary,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                        child: Text(
                          'Close',
                          style: GoogleFonts.inter(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildDialogInfo(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: RichText(
        text: TextSpan(
          style: GoogleFonts.inter(color: AppTheme.textMain, fontSize: 13),
          children: [
            TextSpan(
              text: '$label: ',
              style: const TextStyle(fontWeight: FontWeight.w700),
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
    final visible = _unreadOnly
        ? _notifications.where((n) => n['read_at'] == null).toList()
        : _notifications;
    final bottomPadding = 20 + MediaQuery.paddingOf(context).bottom;

    return Scaffold(
      primary: false,
      backgroundColor: AppTheme.bgLight,
      body: RefreshIndicator(
        onRefresh: () => _fetchNotifications(
          showLoading: false,
          reset: true,
          forcedRefresh: true,
        ),
        color: AppTheme.primary,
        child: CustomScrollView(
          controller: _scrollController,
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(child: _buildHeader(unreadCount)),
            if (_lastRefreshedAt != null && !_isLoading)
              SliverToBoxAdapter(
                child: AppUi.subtleMetaLine(
                  'Last updated ${AppUi.formatRelativeTime(_lastRefreshedAt)}',
                  icon: Icons.update_rounded,
                ),
              ),
            SliverToBoxAdapter(child: _buildAlertChips(unreadCount)),
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
            else if (visible.isEmpty)
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
                      if (index >= visible.length) {
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
                            visible[index],
                            index,
                          ),
                          index,
                        ),
                      );
                    },
                    childCount:
                        visible.length + (_isLoadingMore ? 1 : 0),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildAlertChips(int unreadCount) {
    Widget chip(String label, bool selected, VoidCallback onTap) {
      return Semantics(
        button: true,
        selected: selected,
        label: label,
        child: Padding(
          padding: const EdgeInsets.only(right: 8),
          child: VtPressable(
            onTap: onTap,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: selected ? AppTheme.primary : Colors.white,
                borderRadius: BorderRadius.circular(AppTheme.radiusPill),
                border: Border.all(
                  color: selected ? AppTheme.primary : AppTheme.inputBorder,
                ),
              ),
              child: Text(
                label,
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: selected ? Colors.white : AppTheme.textMuted,
                ),
              ),
            ),
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 4, 20, 8),
      child: Row(
        children: [
          chip('All', !_unreadOnly, () {
            HapticFeedback.selectionClick();
            setState(() => _unreadOnly = false);
          }),
          chip('Unread ($unreadCount)', _unreadOnly, () {
            HapticFeedback.selectionClick();
            setState(() => _unreadOnly = true);
          }),
        ],
      ),
    );
  }

  Widget _buildHeader(int unreadCount) {
    return AppUi.gradientHeader(
      greeting: unreadCount > 0
          ? '$unreadCount unread'
          : 'All caught up',
      title: 'Alerts',
      safeTop: false,
      subtitle: 'Hearings, endorsements, and case updates.',
      badge: AppUi.iconCircle(
        icon: Icons.notifications_active_outlined,
        color: AppTheme.primary,
        size: 32,
        iconSize: 16,
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
    return EmptyStateWidget(
      icon: Icons.notifications_off_rounded,
      title: _unreadOnly ? 'No unread alerts' : 'All caught up!',
      message: _unreadOnly
          ? 'You have no unread notifications. Switch to All to see earlier alerts.'
          : "You have no new notifications at the moment. We'll alert you when there's an update.",
    );
  }

  Widget _buildNotificationItem(dynamic notif, int index) {
    final isUnread = notif['read_at'] == null;
    final Map<String, dynamic> data = _parseNotificationData(notif['data']);

    return Semantics(
      button: true,
      label:
          '${isUnread ? 'Unread' : 'Read'} notification: ${notif['title'] ?? data['title'] ?? 'Record update'}',
      child: Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: VtPressable(
        child: AppUi.surfaceCard(
        padding: EdgeInsets.zero,
        clip: true,
        radius: 20,
        borderColor: isUnread
            ? AppTheme.primary.withValues(alpha: 0.22)
            : AppTheme.inputBorder.withValues(alpha: 0.5),
        color: isUnread
            ? AppTheme.primary.withValues(alpha: 0.06)
            : Colors.white,
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
                      decoration: const BoxDecoration(
                        color: AppTheme.primary,
                        borderRadius: BorderRadius.horizontal(
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
                                          fontWeight: FontWeight.w600,
                                          fontSize: 15,
                                          color: AppTheme.textMain,
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
                                        fontSize: 12,
                                        fontWeight: FontWeight.w500,
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
