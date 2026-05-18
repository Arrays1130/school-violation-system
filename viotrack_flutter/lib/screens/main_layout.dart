import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'dart:ui';
import 'dashboard_screen.dart';
import 'analytics_screen.dart';
import 'notification_screen.dart';
import 'cases_screen.dart';
import 'profile_screen.dart';
import '../theme/app_theme.dart';

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
  }

  @override
  void initState() {
    super.initState();
  }

  void _onItemTapped(int index) {
    if (_selectedIndex == index) return;
    HapticFeedback.lightImpact();
    setState(() => _selectedIndex = index);
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

    return Scaffold(
      body: IndexedStack(
        index: _selectedIndex,
        children: screens,
      ),
      extendBody: true,
      bottomNavigationBar: _buildBottomNav(navItems),
    );
  }

  Widget _buildBottomNav(List<_NavItem> navItems) {
    return ClipRect(
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.92),
            border: Border(
                top: BorderSide(
                    color: AppTheme.inputBorder.withOpacity(0.8), width: 1)),
            boxShadow: AppTheme.navShadow,
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: List.generate(navItems.length, (i) {
                  final item = navItems[i];
                  final isSelected = _selectedIndex == i;
                  return GestureDetector(
                    onTap: () => _onItemTapped(i),
                    behavior: HitTestBehavior.opaque,
                    child: AnimatedContainer(
                      duration: 250.ms,
                      curve: Curves.easeOutCubic,
                      padding: EdgeInsets.symmetric(
                          horizontal: isSelected ? 18 : 10, vertical: 8),
                      decoration: BoxDecoration(
                        gradient: isSelected ? AppTheme.accentGradient : null,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            isSelected ? item.activeIcon : item.inactiveIcon,
                            size: 22,
                            color: isSelected
                                ? Colors.white
                                : AppTheme.textMuted,
                          ),
                          if (isSelected) ...[
                            const SizedBox(width: 6),
                            Text(
                              item.label,
                              style: GoogleFonts.outfit(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                              ),
                            ),
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
