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
import '../widgets/app_ui.dart';
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

  static const _labels = ['Home', 'Cases', 'Stats', 'Alerts', 'Profile'];
  static const _semanticsLabels = [
    'Home dashboard',
    'Cases list',
    'Analytics stats',
    'Notifications and alerts',
    'Profile and settings',
  ];
  static const _icons = [
    Icons.home_outlined,
    Icons.folder_copy_outlined,
    Icons.insights_outlined,
    Icons.notifications_none_rounded,
    Icons.account_circle_outlined,
  ];
  static const _iconsActive = [
    Icons.home_rounded,
    Icons.folder_copy_rounded,
    Icons.insights_rounded,
    Icons.notifications_rounded,
    Icons.account_circle_rounded,
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
      _casesScreenKey.currentState?.refreshFromPoller();
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
      AppUi.showSnack(
        context,
        'New alert ($newCount unread)',
        kind: SnackKind.info,
        actionLabel: 'View',
        onAction: () => navigateToTab(3),
      );
    }
  }

  Future<void> refreshUnreadCount({bool refreshLists = false}) async {
    await NotificationPoller.instance.poll(
      immediate: true,
      refreshLists: refreshLists,
    );
  }

  void navigateToTab(
    int index, {
    String? search,
    String? status,
    bool focusSearch = false,
    String? month,
  }) {
    HapticFeedback.selectionClick();
    setState(() {
      _selectedIndex = index;
      _loadedTabs.add(index);
    });
    if (index == 1) {
      _pendingSearch = search;
      _pendingStatus = status;
      final shouldFocusSearch = focusSearch;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _casesScreenKey.currentState?.applyExternalFilters(
          search: _pendingSearch,
          status: _pendingStatus,
          monthAbbrev: month,
          focusSearch: shouldFocusSearch,
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
    return PopScope(
      canPop: _selectedIndex == 0,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop && _selectedIndex != 0) {
          navigateToTab(0);
        }
      },
      child: AnnotatedRegion<SystemUiOverlayStyle>(
        value: const SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: Brightness.dark,
          statusBarBrightness: Brightness.light,
        ),
        child: Scaffold(
        backgroundColor: AppTheme.bgLight,
        appBar: AppBar(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.white,
          elevation: 0,
          scrolledUnderElevation: 0,
          automaticallyImplyLeading: false,
          centerTitle: false,
          titleSpacing: 0,
          toolbarHeight: 56,
          title: _buildFacebookTabBar(),
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(3),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: List.generate(5, (i) {
                    final selected = _selectedIndex == i;
                    return Expanded(
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 180),
                        height: 3,
                        margin: EdgeInsets.symmetric(
                          horizontal: selected ? 16 : 28,
                        ),
                        decoration: BoxDecoration(
                          color: selected
                              ? AppTheme.primary
                              : Colors.transparent,
                          borderRadius: const BorderRadius.vertical(
                            top: Radius.circular(3),
                          ),
                        ),
                      ),
                    );
                  }),
                ),
                Divider(
                  height: 0.5,
                  thickness: 0.5,
                  color: AppTheme.inputBorder.withValues(alpha: 0.9),
                ),
              ],
            ),
          ),
        ),
        body: Column(
          children: [
            ValueListenableBuilder<bool>(
              valueListenable: ApiService.isOfflineNotifier,
              builder: (context, offline, _) {
                if (!offline) return const SizedBox.shrink();
                return Material(
                  color: AppTheme.accentAmber.withValues(alpha: 0.12),
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(20, 10, 20, 10),
                    child: Row(
                      children: [
                        Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            color: AppTheme.accentAmber.withValues(alpha: 0.16),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.wifi_off_rounded,
                            color: AppTheme.accentAmber,
                            size: 18,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'You are offline',
                                style: GoogleFonts.inter(
                                  color: AppTheme.textMain,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              Text(
                                'Showing saved data where available.',
                                style: GoogleFonts.inter(
                                  color: AppTheme.textMuted,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
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
                  return AnimatedOpacity(
                    opacity: _selectedIndex == i ? 1.0 : 0.0,
                    duration: const Duration(milliseconds: 220),
                    curve: Curves.easeOut,
                    child: _screenFor(i),
                  );
                }),
              ),
            ),
          ],
        ),
      ),
        ),
    );
  }

  Widget _buildFacebookTabBar() {
    return SizedBox(
      height: 56,
      child: Row(
        children: List.generate(5, (i) {
          final selected = _selectedIndex == i;
          final isAlerts = i == 3;
          return Expanded(
            child: Semantics(
              button: true,
              selected: selected,
              label: _semanticsLabels[i],
              child: InkWell(
                onTap: () => _onItemTapped(i),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    SizedBox(
                      width: 28,
                      height: 26,
                      child: Stack(
                        clipBehavior: Clip.none,
                        alignment: Alignment.center,
                        children: [
                          Icon(
                            selected ? _iconsActive[i] : _icons[i],
                            size: 26,
                            color: selected
                                ? AppTheme.primary
                                : const Color(0xFF65676B),
                          ),
                          if (isAlerts && _unreadCount > 0)
                            Positioned(
                              top: -4,
                              right: -8,
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 4,
                                  vertical: 1,
                                ),
                                constraints: const BoxConstraints(
                                  minWidth: 16,
                                  minHeight: 14,
                                ),
                                decoration: BoxDecoration(
                                  color: AppTheme.accentRose,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  _unreadCount > 99 ? '99+' : '$_unreadCount',
                                  textAlign: TextAlign.center,
                                  style: GoogleFonts.inter(
                                    fontSize: 8,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      _labels[i],
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.inter(
                        fontSize: 10,
                        fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
                        color: selected
                            ? AppTheme.primary
                            : const Color(0xFF65676B),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }),
      ),
    );
  }
}
