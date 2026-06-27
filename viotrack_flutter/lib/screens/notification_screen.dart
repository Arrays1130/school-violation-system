import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:lottie/lottie.dart';
import 'package:flutter/services.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/empty_state_widget.dart';
import 'case_details_screen.dart';

class NotificationScreen extends StatefulWidget {
  @override
  _NotificationScreenState createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  final ApiService _apiService = ApiService();
  List<dynamic> _notifications = [];
  bool _isLoading = true;
  Timer? _autoRefreshTimer;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
    _autoRefreshTimer = Timer.periodic(
        const Duration(seconds: 30), (_) => _fetchNotifications(showLoading: false));
  }

  Future<void> _loadInitialData() async {
    final cachedData = await _apiService.getPersistentCache('notifications');
    if (cachedData != null && mounted) {
      if (mounted) setState(() {
        if (cachedData is Map && cachedData.containsKey('data')) {
          _notifications = cachedData['data'] as List<dynamic>;
        } else if (cachedData is List) {
          _notifications = cachedData;
        }
        _isLoading = false;
      });
    }
    await _fetchNotifications(showLoading: _isLoading);
  }

  @override
  void dispose() {
    _autoRefreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchNotifications({bool showLoading = true}) async {
    if (showLoading && mounted) {
      if (mounted) setState(() => _isLoading = true);
    }
    try {
      final dynamic result = await _apiService.getNotifications(forcedRefresh: true);
      if (mounted) {
        if (mounted) setState(() {
          if (result is Map && result.containsKey('data')) {
            _notifications = result['data'] as List<dynamic>;
          } else if (result is List) {
            _notifications = result;
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _handleNotificationTap(dynamic notification) async {
    HapticFeedback.mediumImpact();
    final String id = notification['id'].toString();
    final Map<String, dynamic> data = notification['data'] is String
        ? Map<String, dynamic>.from(jsonDecode(notification['data']))
        : Map<String, dynamic>.from(notification['data']);
    if (notification['read_at'] == null) {
      await _apiService.markNotificationAsRead(id);
      _fetchNotifications();
    }
    if (data.containsKey('case_id')) {
      if (!mounted) return;
      Navigator.push(
        context,
        PageRouteBuilder(
          transitionDuration: const Duration(milliseconds: 500),
          reverseTransitionDuration: const Duration(milliseconds: 400),
          pageBuilder: (context, animation, secondaryAnimation) =>
              CaseDetailsScreen(caseId: int.parse(data['case_id'].toString())),
          transitionsBuilder: (context, animation, secondaryAnimation, child) {
            final fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
              CurvedAnimation(parent: animation, curve: Curves.easeOutCubic),
            );
            final scaleAnimation = Tween<double>(begin: 0.95, end: 1.0).animate(
              CurvedAnimation(parent: animation, curve: Curves.easeOutCubic),
            );
            return FadeTransition(
              opacity: fadeAnimation,
              child: ScaleTransition(scale: scaleAnimation, child: child),
            );
          },
        ),
      ).then((_) => _fetchNotifications());
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
        title: Text(title,
            style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(data['message'] ?? 'No additional details.',
                style: GoogleFonts.outfit()),
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
            child: Text("CLOSE",
                style: GoogleFonts.outfit(
                    color: AppTheme.accentCyan,
                    fontWeight: FontWeight.bold)),
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
          style: GoogleFonts.outfit(color: AppTheme.textMain),
          children: [
            TextSpan(
                text: "$label: ",
                style: const TextStyle(fontWeight: FontWeight.bold)),
            TextSpan(text: value),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = _notifications.where((n) => n['read_at'] == null).length;

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: Column(
        children: [
          _buildHeader(unreadCount),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _fetchNotifications,
              color: AppTheme.accentCyan,
              child: _isLoading
                  ? Padding(
                      padding: const EdgeInsets.only(top: 16),
                      child: ShimmerLoader.buildListSkeleton(),
                    )
                  : _notifications.isEmpty
                      ? SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          child: Container(
                            height: MediaQuery.of(context).size.height * 0.65,
                            alignment: Alignment.center,
                            child: _buildEmptyState(),
                          ),
                        )
                      : ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                          itemCount: _notifications.length,
                          itemBuilder: (context, index) =>
                              _buildNotificationItem(
                                  _notifications[index], index),
                        ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader(int unreadCount) {
    return Container(
      color: Colors.white,
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text("Notifications",
                        style: GoogleFonts.outfit(
                            fontSize: 22,
                            fontWeight: FontWeight.w900,
                            color: AppTheme.textMain)),
                    if (unreadCount > 0)
                      Text("$unreadCount unread",
                          style: GoogleFonts.outfit(
                              fontSize: 12,
                              color: AppTheme.accentCyan,
                              fontWeight: FontWeight.w600))
                    else
                      Text("All caught up",
                          style: GoogleFonts.outfit(
                              fontSize: 12, color: AppTheme.textMuted)),
                  ],
                ),
              ),
              if (unreadCount > 0)
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    gradient: AppTheme.accentGradient,
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                          color: AppTheme.accentCyan.withOpacity(0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 4)),
                    ],
                  ),
                  child: Text("$unreadCount NEW",
                      style: GoogleFonts.outfit(
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          color: Colors.white,
                          letterSpacing: 0.5)),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return const EmptyStateWidget(
      icon: Icons.notifications_off_rounded,
      title: "All caught up!",
      message: "You have no new notifications at the moment. We'll alert you when there's an update.",
    );
  }

  Widget _buildNotificationItem(dynamic notif, int index) {
    final isUnread = notif['read_at'] == null;
    final Map<String, dynamic> data = notif['data'] is String
        ? Map<String, dynamic>.from(jsonDecode(notif['data']))
        : Map<String, dynamic>.from(notif['data']);

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: isUnread ? AppTheme.softShadow : null,
        border: isUnread
            ? Border.all(color: AppTheme.accentCyan.withOpacity(0.15))
            : Border.all(color: AppTheme.inputBorder.withOpacity(0.5)),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(20),
          onTap: () {
            HapticFeedback.mediumImpact();
            _handleNotificationTap(notif);
          },
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Stack(
                  children: [
                    Container(
                      width: 46,
                      height: 46,
                      decoration: BoxDecoration(
                        gradient: isUnread
                            ? AppTheme.accentGradient
                            : const LinearGradient(
                                colors: [Color(0xFFCBD5E1), Color(0xFF94A3B8)]),
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: isUnread
                            ? [
                                BoxShadow(
                                    color: AppTheme.accentCyan.withOpacity(0.3),
                                    blurRadius: 10,
                                    offset: const Offset(0, 4)),
                              ]
                            : null,
                      ),
                      child: const Icon(Icons.gavel_rounded,
                          color: Colors.white, size: 20),
                    ),
                    if (isUnread)
                      Positioned(
                        right: -2,
                        top: -2,
                        child: Container(
                          width: 12,
                          height: 12,
                          decoration: BoxDecoration(
                            color: AppTheme.accentRose,
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2),
                            boxShadow: [
                              BoxShadow(
                                  color: AppTheme.accentRose.withOpacity(0.5),
                                  blurRadius: 4),
                            ],
                          ),
                        ),
                      ),
                  ],
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
                              notif['title'] ?? data['title'] ?? 'Record Update',
                              style: GoogleFonts.outfit(
                                  fontWeight: isUnread
                                      ? FontWeight.w800
                                      : FontWeight.w600,
                                  fontSize: 14,
                                  color: isUnread
                                      ? AppTheme.textMain
                                      : AppTheme.textMuted),
                            ),
                          ),
                          if (isUnread)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppTheme.accentCyan.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text("NEW",
                                  style: GoogleFonts.outfit(
                                      fontSize: 8,
                                      fontWeight: FontWeight.w900,
                                      color: AppTheme.accentCyan,
                                      letterSpacing: 0.5)),
                            ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        data['message'] ?? 'Action required on case record.',
                        style: GoogleFonts.outfit(
                            fontSize: 12,
                            color: isUnread
                                ? AppTheme.textSub
                                : AppTheme.textMuted,
                            height: 1.4),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Icon(Icons.access_time_rounded,
                              size: 10, color: AppTheme.textHint),
                          const SizedBox(width: 4),
                          Text(
                            _formatDate(notif['created_at']),
                            style: GoogleFonts.outfit(
                                fontSize: 10,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.textHint),
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
      ),
    ).animate()
        .fadeIn(delay: Duration(milliseconds: 50 * index))
        .slideX(begin: 0.04);
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
