import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../services/notification_poller.dart';
import 'dashboard_screen.dart';
import 'cases_screen.dart';
import 'analytics_screen.dart';
import 'notification_screen.dart';
import 'profile_screen.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  static MainLayoutState? of(BuildContext context) =>
      context.findAncestorStateOfType<MainLayoutState>();

  @override
  State<MainLayout> createState() => MainLayoutState();
}

class MainLayoutState extends State<MainLayout> with WidgetsBindingObserver {
  final GlobalKey<DashboardScreenState> _dashboardScreenKey =
      GlobalKey<DashboardScreenState>();
  final GlobalKey<CasesScreenState> _casesScreenKey = GlobalKey<CasesScreenState>();
  final GlobalKey<NotificationScreenState> _notificationScreenKey =
      GlobalKey<NotificationScreenState>();
  final Set<int> _loadedTabs = {0};
  int _selectedIndex = 0;
  String? _pendingSearch;
  String? _pendingStatus;
  int _unreadCount = 0;
  StreamSubscription<void>? _listRefreshSub;

  static const _labels = ['Home', 'Cases', 'Insights', 'Alerts', 'Profile'];
  static const _icons = [
    Icons.home_outlined,
    Icons.folder_outlined,
    Icons.bar_chart_outlined,
    Icons.notifications_outlined,
    Icons.person_outline,
  ];
  static const _iconsActive = [
    Icons.home_rounded,
    Icons.folder_rounded,
    Icons.bar_chart_rounded,
    Icons.notifications_rounded,
    Icons.person_rounded,
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    NotificationPoller.instance.unreadCount.addListener(_onUnreadCountChanged);
    _listRefreshSub = NotificationPoller.instance.listRefresh.stream.listen((_) {
      _notificationScreenKey.currentState?.refreshFromPoller();
      _dashboardScreenKey.currentState?.refreshFromPoller();
      if (_selectedIndex == 1) {
        _casesScreenKey.currentState?.refreshFromPoller();
      }
    });
    NotificationPoller.instance.start();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    NotificationPoller.instance.unreadCount.removeListener(_onUnreadCountChanged);
    _listRefreshSub?.cancel();
    NotificationPoller.instance.stop();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final foreground = state == AppLifecycleState.resumed;
    NotificationPoller.instance.setForeground(foreground);
  }

  void _onUnreadCountChanged() {
    if (!mounted) return;
    final newCount = NotificationPoller.instance.unreadCount.value;
    final previous = _unreadCount;
    final increased = newCount > previous && previous >= 0 && NotificationPoller.instance.hasBaseline;

    setState(() => _unreadCount = newCount);

    if (increased && _selectedIndex != 3 && mounted) {
      HapticFeedback.lightImpact();
      ScaffoldMessenger.of(context).hideCurrentSnackBar();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'May bagong alert ($newCount unread)',
            style: GoogleFonts.inter(),
          ),
          backgroundColor: AppTheme.primary,
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 4),
          action: SnackBarAction(
            label: 'Tingnan',
            textColor: Colors.white,
            onPressed: () => navigateToTab(3),
          ),
        ),
      );
    }
  }

  Future<void> refreshUnreadCount() async {
    await NotificationPoller.instance.poll(immediate: true);
  }

  void navigateToTab(int index, {String? search, String? status}) {
    HapticFeedback.selectionClick();
    setState(() {
      _selectedIndex = index;
      _loadedTabs.add(index);
    });
    if (index == 1) {
      _pendingSearch = search;
      _pendingStatus = status;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _casesScreenKey.currentState?.applyExternalFilters(
          search: _pendingSearch,
          status: _pendingStatus,
        );
        _pendingSearch = null;
        _pendingStatus = null;
      });
    }
    if (index == 3) {
      // Refresh real count from API — do NOT force badge to 0
      refreshUnreadCount();
      _notificationScreenKey.currentState?.refreshFromPoller();
    }
  }

  void _onItemTapped(int index) {
    if (_selectedIndex == index) return;
    navigateToTab(index);
  }

  Widget _screenFor(int index) {
    switch (index) {
      case 0:
        return DashboardScreen(key: _dashboardScreenKey);
      case 1:
        return CasesScreen(key: _casesScreenKey);
      case 2:
        return AnalyticsScreen();
      case 3:
        return NotificationScreen(key: _notificationScreenKey);
      case 4:
        return const ProfileScreen();
      default:
        return const SizedBox.shrink();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: Column(
        children: [
          ValueListenableBuilder<bool>(
            valueListenable: ApiService.isOfflineNotifier,
            builder: (context, offline, _) {
              if (!offline) return const SizedBox.shrink();
              return Material(
                color: AppTheme.accentAmber,
                child: SafeArea(
                  bottom: false,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.wifi_off, color: Colors.white, size: 14),
                        const SizedBox(width: 6),
                        Text('Offline mode', style: GoogleFonts.inter(color: Colors.white, fontSize: 12)),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
          Expanded(
            child: IndexedStack(
              index: _selectedIndex,
              children: List.generate(5, (i) {
                if (!_loadedTabs.contains(i)) return const SizedBox.shrink();
                return _screenFor(i);
              }),
            ),
          ),
        ],
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.08),
              blurRadius: 20,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: SafeArea(
          top: false,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
            child: Row(
              children: List.generate(5, (i) {
                final selected = _selectedIndex == i;
                final isAlerts = i == 3;
                return Expanded(
                  child: GestureDetector(
                    onTap: () => _onItemTapped(i),
                    behavior: HitTestBehavior.opaque,
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      decoration: BoxDecoration(
                        color: selected ? AppTheme.primaryLight : Colors.transparent,
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Stack(
                            clipBehavior: Clip.none,
                            children: [
                              Icon(
                                selected ? _iconsActive[i] : _icons[i],
                                size: 22,
                                color: selected ? AppTheme.primary : AppTheme.textMuted,
                              ),
                              if (isAlerts && _unreadCount > 0)
                                Positioned(
                                  top: -4,
                                  right: -10,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                                    decoration: BoxDecoration(
                                      color: AppTheme.accentRose,
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: Text(
                                      _unreadCount > 99 ? '99+' : '$_unreadCount',
                                      style: GoogleFonts.inter(
                                        fontSize: 9,
                                        fontWeight: FontWeight.w700,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _labels[i],
                            style: GoogleFonts.inter(
                              fontSize: 10,
                              fontWeight: selected ? FontWeight.w600 : FontWeight.w500,
                              color: selected ? AppTheme.primary : AppTheme.textMuted,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }
}
