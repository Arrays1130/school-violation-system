import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import '../services/notification_poller.dart';
import '../services/push_bootstrap.dart';
import '../services/fcm_service.dart';
import '../services/push_navigation_service.dart';
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
  final GlobalKey<CasesScreenState> _casesScreenKey =
      GlobalKey<CasesScreenState>();
  final GlobalKey<NotificationScreenState> _notificationScreenKey =
      GlobalKey<NotificationScreenState>();
  final Set<int> _loadedTabs = {0};
  int _selectedIndex = 0;
  String? _pendingSearch;
  String? _pendingStatus;
  int _unreadCount = 0;
  StreamSubscription<void>? _listRefreshSub;

  static const _labels = ['Home', 'Cases', 'Insights', 'Alerts', 'Profile'];
  static const _semanticsLabels = [
    'Home dashboard',
    'Cases list',
    'Analytics insights',
    'Notifications and alerts',
    'Profile and settings',
  ];
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
    _listRefreshSub = NotificationPoller.instance.listRefresh.stream.listen((
      _,
    ) {
      _notificationScreenKey.currentState?.refreshFromPoller();
      _dashboardScreenKey.currentState?.refreshFromPoller();
      if (_selectedIndex == 1) {
        _casesScreenKey.currentState?.refreshFromPoller();
      }
    });
    NotificationPoller.instance.start();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      PushNavigationService.consumePendingNavigation();
      if (PushBootstrap.isInitialized) {
        unawaited(FCMService.handleLaunchNotification());
        unawaited(FCMService.syncTokenWithBackend());
      }
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    NotificationPoller.instance.unreadCount.removeListener(
      _onUnreadCountChanged,
    );
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
    final increased =
        newCount > previous &&
        previous >= 0 &&
        NotificationPoller.instance.hasBaseline;

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

  Future<void> refreshUnreadCount({bool refreshLists = false}) async {
    await NotificationPoller.instance.poll(
      immediate: true,
      refreshLists: refreshLists,
    );
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
      refreshUnreadCount(refreshLists: true);
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _notificationScreenKey.currentState?.refreshFromPoller();
      });
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
      extendBody: true,
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
                        const Icon(
                          Icons.wifi_off,
                          color: Colors.white,
                          size: 14,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          'Offline mode',
                          style: GoogleFonts.inter(
                            color: Colors.white,
                            fontSize: 12,
                          ),
                        ),
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
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
        child: Container(
          decoration: BoxDecoration(
            color: AppTheme.bgCard.withValues(alpha: 0.96),
            borderRadius: BorderRadius.circular(28),
            border: Border.all(
              color: AppTheme.inputBorder.withValues(alpha: 0.85),
            ),
            boxShadow: AppTheme.navShadow,
          ),
          child: SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(8, 8, 8, 10),
              child: Row(
                children: List.generate(5, (i) {
                  final selected = _selectedIndex == i;
                  final isAlerts = i == 3;
                  return Expanded(
                    child: Semantics(
                      button: true,
                      selected: selected,
                      label: _semanticsLabels[i],
                      child: Tooltip(
                        message: _labels[i],
                        child: GestureDetector(
                          onTap: () => _onItemTapped(i),
                          behavior: HitTestBehavior.opaque,
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 220),
                            curve: Curves.easeOutCubic,
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            decoration: BoxDecoration(
                              gradient: selected ? AppTheme.heroGradient : null,
                              color: selected ? null : Colors.transparent,
                              borderRadius: BorderRadius.circular(22),
                              boxShadow: selected ? AppTheme.floatShadow : null,
                            ),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (selected)
                                  Padding(
                                    padding: const EdgeInsets.only(bottom: 4),
                                    child: Container(
                                      width: 18,
                                      height: 4,
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.85),
                                        borderRadius: BorderRadius.circular(999),
                                      ),
                                    ),
                                  ),
                                Stack(
                                  clipBehavior: Clip.none,
                                  children: [
                                    Icon(
                                      selected ? _iconsActive[i] : _icons[i],
                                      size: 22,
                                      color: selected
                                          ? Colors.white
                                          : AppTheme.textMuted,
                                    ),
                                    if (isAlerts && _unreadCount > 0)
                                      Positioned(
                                        top: -5,
                                        right: -12,
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 5,
                                            vertical: 2,
                                          ),
                                          decoration: BoxDecoration(
                                            color: AppTheme.accentRose,
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: Text(
                                            _unreadCount > 99
                                                ? '99+'
                                                : '$_unreadCount',
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
                                    fontWeight: selected
                                        ? FontWeight.w600
                                        : FontWeight.w500,
                                    color: selected
                                        ? Colors.white
                                        : AppTheme.textMuted,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  );
                }),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
