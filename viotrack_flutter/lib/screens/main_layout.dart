import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'dart:async';
import 'dart:ui';
import 'dashboard_screen.dart';
import 'analytics_screen.dart';
import 'notification_screen.dart';
import 'cases_screen.dart';
import 'profile_screen.dart';
import '../theme/app_theme.dart';
import '../api_service.dart';

class MainLayout extends StatefulWidget {
  const MainLayout({super.key});

  static MainLayoutState? of(BuildContext context) =>
      context.findAncestorStateOfType<MainLayoutState>();

  @override
  State<MainLayout> createState() => MainLayoutState();
}

class MainLayoutState extends State<MainLayout> {
  final GlobalKey<CasesScreenState> _casesScreenKey =
      GlobalKey<CasesScreenState>();
  String? _pendingSearch;
  String? _pendingStatus;
  int _selectedIndex = 0;

  // Badge
  int _unreadCount = 0;
  Timer? _badgeTimer;

  @override
  void initState() {
    super.initState();
    _fetchUnreadCount();
    // Poll for unread count every 30 seconds
    _badgeTimer = Timer.periodic(
      const Duration(seconds: 30),
      (_) => _fetchUnreadCount(),
    );
  }

  @override
  void dispose() {
    _badgeTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchUnreadCount() async {
    try {
      final count = await ApiService().getUnreadCount();
      if (mounted) {
        setState(() => _unreadCount = count);
      }
    } catch (_) {}
  }

  void navigateToTab(int index, {String? search, String? status}) {
    HapticFeedback.lightImpact();
    setState(() => _selectedIndex = index);
    if (index == 1) {
      final casesState = _casesScreenKey.currentState;
      if (casesState != null) {
        casesState.applyExternalFilters(search: search, status: status);
      } else {
        _pendingSearch = search;
        _pendingStatus = status;
      }
    }
    // Clear badge when opening notifications tab
    if (index == 3) {
      setState(() => _unreadCount = 0);
    }
  }

  void _onItemTapped(int index) {
    if (_selectedIndex == index) return;
    HapticFeedback.lightImpact();
    setState(() => _selectedIndex = index);
    // Clear badge when opening notifications tab
    if (index == 3) {
      setState(() => _unreadCount = 0);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_pendingSearch != null || _pendingStatus != null) {
      final casesState = _casesScreenKey.currentState;
      if (casesState != null) {
        casesState.applyExternalFilters(
            search: _pendingSearch, status: _pendingStatus);
        _pendingSearch = null;
        _pendingStatus = null;
      }
    }

    final List<Widget> screens = [
      DashboardScreen(),
      CasesScreen(key: _casesScreenKey),
      AnalyticsScreen(),
      NotificationScreen(),
      const ProfileScreen(),
    ];

    final List<_NavItem> navItems = [
      _NavItem(Icons.home_rounded, Icons.home_outlined, "Home"),
      _NavItem(Icons.folder_copy_rounded, Icons.folder_copy_outlined, "Cases"),
      _NavItem(Icons.insights_rounded, Icons.insights_outlined, "Insights"),
      _NavItem(Icons.notifications_rounded, Icons.notifications_none_rounded, "Alerts"),
      _NavItem(Icons.person_rounded, Icons.person_outline_rounded, "Profile"),
    ];

    return Container(
      color: AppTheme.bgLight,
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 500),
          child: Scaffold(
            body: Column(
              children: [
                // Offline banner
                ValueListenableBuilder<bool>(
                  valueListenable: ApiService.isOfflineNotifier,
                  builder: (context, isOffline, child) {
                    if (!isOffline) return const SizedBox.shrink();
                    return Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 16),
                      color: Colors.orange.shade800,
                      child: SafeArea(
                        bottom: false,
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.wifi_off_rounded,
                                color: Colors.white, size: 14),
                            const SizedBox(width: 8),
                            Text(
                              'Offline Mode • Showing saved data',
                              style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ).animate().slideY(
                        begin: -1, end: 0, duration: 300.ms, curve: Curves.easeOut);
                  },
                ),
                Expanded(
                  child: Stack(
                    children: List.generate(screens.length, (index) {
                      return AnimatedOpacity(
                        opacity: _selectedIndex == index ? 1.0 : 0.0,
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeOutCubic,
                        child: IgnorePointer(
                          ignoring: _selectedIndex != index,
                          child: screens[index],
                        ),
                      );
                    }),
                  ),
                ),
              ],
            ),
            extendBody: true,
            bottomNavigationBar: _buildBottomNav(navItems),
          ),
        ),
      ),
    );
  }

  Widget _buildBottomNav(List<_NavItem> navItems) {
    return SafeArea(
      child: Container(
        margin: const EdgeInsets.only(left: 24, right: 24, bottom: 20),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.9),
          borderRadius: BorderRadius.circular(32),
          boxShadow: AppTheme.navShadow,
          border: Border.all(color: Colors.white, width: 1.5),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(32),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: List.generate(navItems.length, (i) {
                final item = navItems[i];
                final isSelected = _selectedIndex == i;
                final isNotifTab = i == 3;

                return GestureDetector(
                  onTap: () => _onItemTapped(i),
                  behavior: HitTestBehavior.opaque,
                  child: AnimatedContainer(
                    duration: 300.ms,
                    curve: Curves.easeOutCubic,
                    padding: EdgeInsets.symmetric(
                        horizontal: isSelected ? 20 : 12, vertical: 12),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppTheme.primaryNavy.withOpacity(0.08)
                          : Colors.transparent,
                      borderRadius: BorderRadius.circular(24),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Bell icon with badge overlay
                        Stack(
                          clipBehavior: Clip.none,
                          children: [
                            Icon(
                              isSelected ? item.activeIcon : item.inactiveIcon,
                              size: 24,
                              color: isSelected
                                  ? AppTheme.primaryNavy
                                  : AppTheme.textMuted.withOpacity(0.7),
                            ),
                            // Badge — only on notifications tab
                            if (isNotifTab && _unreadCount > 0)
                              Positioned(
                                top: -4,
                                right: -6,
                                child: AnimatedSwitcher(
                                  duration: const Duration(milliseconds: 300),
                                  child: Container(
                                    key: ValueKey(_unreadCount),
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 4, vertical: 1),
                                    decoration: BoxDecoration(
                                      color: AppTheme.accentRose,
                                      borderRadius: BorderRadius.circular(100),
                                      border: Border.all(
                                          color: Colors.white, width: 1.5),
                                    ),
                                    constraints: const BoxConstraints(
                                        minWidth: 16, minHeight: 16),
                                    child: Text(
                                      _unreadCount > 99
                                          ? '99+'
                                          : '$_unreadCount',
                                      style: GoogleFonts.outfit(
                                        fontSize: 8,
                                        fontWeight: FontWeight.w900,
                                        color: Colors.white,
                                      ),
                                      textAlign: TextAlign.center,
                                    ),
                                  ).animate().scale(
                                      begin: const Offset(0.5, 0.5),
                                      end: const Offset(1, 1),
                                      curve: Curves.easeOutBack),
                                ),
                              ),
                          ],
                        ),
                        if (isSelected) ...[
                          const SizedBox(width: 8),
                          Text(
                            item.label,
                            style: GoogleFonts.outfit(
                              fontSize: 13,
                              fontWeight: FontWeight.w800,
                              letterSpacing: -0.2,
                              color: AppTheme.primaryNavy,
                            ),
                          ).animate().fadeIn().slideX(begin: -0.2, end: 0),
                        ],
                      ],
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

class _NavItem {
  final IconData activeIcon;
  final IconData inactiveIcon;
  final String label;
  const _NavItem(this.activeIcon, this.inactiveIcon, this.label);
}
