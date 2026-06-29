import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:async';
import '../api_service.dart';
import '../theme/app_theme.dart';
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

class MainLayoutState extends State<MainLayout> {
  final GlobalKey<CasesScreenState> _casesScreenKey = GlobalKey<CasesScreenState>();
  final Set<int> _loadedTabs = {0};
  int _selectedIndex = 0;
  String? _pendingSearch;
  String? _pendingStatus;
  int _unreadCount = 0;
  Timer? _badgeTimer;

  @override
  void initState() {
    super.initState();
    _fetchUnreadCount();
    _badgeTimer = Timer.periodic(const Duration(seconds: 60), (_) => _fetchUnreadCount());
  }

  @override
  void dispose() {
    _badgeTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchUnreadCount() async {
    try {
      final count = await ApiService().getUnreadCount();
      if (mounted) setState(() => _unreadCount = count);
    } catch (_) {}
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
    if (index == 3) setState(() => _unreadCount = 0);
  }

  void _onItemTapped(int index) {
    if (_selectedIndex == index) return;
    navigateToTab(index);
  }

  Widget _screenFor(int index) {
    switch (index) {
      case 0:
        return const DashboardScreen();
      case 1:
        return CasesScreen(key: _casesScreenKey);
      case 2:
        return AnalyticsScreen();
      case 3:
        return NotificationScreen();
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
                        Text(
                          'Offline mode',
                          style: GoogleFonts.inter(color: Colors.white, fontSize: 12),
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
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: _onItemTapped,
        backgroundColor: Colors.white,
        indicatorColor: AppTheme.primaryLight,
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: [
          const NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home, color: AppTheme.primary),
            label: 'Home',
          ),
          const NavigationDestination(
            icon: Icon(Icons.folder_outlined),
            selectedIcon: Icon(Icons.folder, color: AppTheme.primary),
            label: 'Cases',
          ),
          const NavigationDestination(
            icon: Icon(Icons.bar_chart_outlined),
            selectedIcon: Icon(Icons.bar_chart, color: AppTheme.primary),
            label: 'Insights',
          ),
          NavigationDestination(
            icon: Badge(
              isLabelVisible: _unreadCount > 0,
              label: Text('$_unreadCount'),
              child: const Icon(Icons.notifications_outlined),
            ),
            selectedIcon: Badge(
              isLabelVisible: _unreadCount > 0,
              label: Text('$_unreadCount'),
              child: const Icon(Icons.notifications, color: AppTheme.primary),
            ),
            label: 'Alerts',
          ),
          const NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person, color: AppTheme.primary),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}
