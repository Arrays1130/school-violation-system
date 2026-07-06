import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter/services.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import 'case_details_screen.dart';
import '../widgets/skeleton_loader.dart';
import '../widgets/empty_state_widget.dart';
import '../widgets/app_ui.dart';
import '../widgets/vt_ui.dart';

class CasesScreen extends StatefulWidget {
  const CasesScreen({super.key});

  @override
  State<CasesScreen> createState() => CasesScreenState();
}

class CasesScreenState extends State<CasesScreen> {
  final ApiService _apiService = ApiService();
  final TextEditingController _searchController = TextEditingController();
  List<dynamic> _allViolations = [];
  List<dynamic> _filteredViolations = [];
  bool _isLoading = true;
  String _selectedSeverity = 'All';
  String _selectedStatus = 'All';
  String _selectedDate = 'All Time';
  Timer? _debounce;
  bool _isAscending = false;
  final ScrollController _scrollController = ScrollController();
  int _page = 1;
  bool _hasMore = true;
  bool _loadingMore = false;
  bool _showAdvancedFilters = false;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadInitialData();
  }

  void _onScroll() {
    if (!_scrollController.hasClients) return;
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      _loadMore();
    }
  }

  Future<void> _loadMore() async {
    if (_loadingMore || !_hasMore || _isLoading) return;
    setState(() => _loadingMore = true);
    final nextPage = _page + 1;
    try {
      final result = await _apiService.getViolations(
        forcedRefresh: true,
        page: nextPage,
      );
      if (!mounted) return;
      if (result is Map) {
        final data = (result['data'] as List<dynamic>?) ?? [];
        final current = result['current_page'] as int? ?? nextPage;
        final last = result['last_page'] as int? ?? current;
        setState(() {
          _page = current;
          _allViolations = [..._allViolations, ...data];
          _hasMore = current < last;
          _filteredViolations = _computeFilteredList();
        });
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  Future<void> _loadInitialData() async {
    final cachedViolations = await _apiService.getPersistentCache('violations');
    if (cachedViolations != null) {
      if (mounted) {
        setState(() {
          if (cachedViolations is Map) {
            _allViolations = (cachedViolations['data'] ?? []) as List<dynamic>;
          } else if (cachedViolations is List) {
            _allViolations = cachedViolations;
          }
          _filteredViolations = _computeFilteredList();
          _isLoading = false;
        });
      }
    }
    final hadCache = cachedViolations != null;
    await _fetchData(
      showLoading: _isLoading,
      forcedRefresh: !hadCache,
    );
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void applyExternalFilters({String? search, String? status}) {
    if (search != null) _searchController.text = search;
    if (status != null) _selectedStatus = status;
    _applyFilters();
  }

  void refreshFromPoller() {
    if (!mounted) return;
    _fetchData(showLoading: false, forcedRefresh: false);
  }

  Future<void> _fetchData({
    bool showLoading = true,
    bool forcedRefresh = false,
  }) async {
    if (showLoading && mounted) setState(() => _isLoading = true);
    try {
      final dynamic result = await _apiService.getViolations(
        forcedRefresh: forcedRefresh,
        page: 1,
      );
      if (mounted) {
        setState(() {
          _page = 1;
          if (result is Map) {
            _allViolations = (result['data'] as List<dynamic>?) ?? [];
            final current = result['current_page'] as int? ?? 1;
            final last = result['last_page'] as int? ?? current;
            _hasMore = current < last;
          } else if (result is List) {
            _allViolations = result;
            _hasMore = false;
          }
          _filteredViolations = _computeFilteredList();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              e.toString().replaceAll('Exception: ', ''),
              style: GoogleFonts.inter(),
            ),
            backgroundColor: Colors.redAccent,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  List<dynamic> _computeFilteredList() {
    final query = _searchController.text.toLowerCase();
    final now = DateTime.now();

    final filtered = _allViolations.where((v) {
      final studentName = (v['student']?['full_name'] ?? '')
          .toString()
          .toLowerCase();
      final violationTitle = (v['violation']?['title'] ?? '')
          .toString()
          .toLowerCase();
      final severity = v['violation']?['severity'] ?? 'Minor';
      final status = v['status'] ?? 'Pending';
      final dateStr = v['created_at'] ?? '';

      final matchesSearch =
          studentName.contains(query) || violationTitle.contains(query);
      final matchesSeverity =
          _selectedSeverity == 'All' || severity == _selectedSeverity;
      final matchesStatus =
          _selectedStatus == 'All' || status == _selectedStatus;

      var matchesDate = true;
      if (_selectedDate != 'All Time' && dateStr.isNotEmpty) {
        try {
          final date = DateTime.parse(dateStr);
          if (_selectedDate == 'Today') {
            matchesDate =
                date.year == now.year &&
                date.month == now.month &&
                date.day == now.day;
          } else if (_selectedDate == 'This Week') {
            final weekStart = now.subtract(Duration(days: now.weekday - 1));
            final startOfWeek = DateTime(
              weekStart.year,
              weekStart.month,
              weekStart.day,
            );
            matchesDate = date.isAfter(
              startOfWeek.subtract(const Duration(seconds: 1)),
            );
          } else if (_selectedDate == 'This Month') {
            matchesDate = date.year == now.year && date.month == now.month;
          }
        } catch (_) {
          matchesDate = true;
        }
      }

      return matchesSearch &&
          matchesSeverity &&
          matchesStatus &&
          matchesDate;
    }).toList();

    filtered.sort((a, b) {
      final int idA = a['id'] ?? 0;
      final int idB = b['id'] ?? 0;
      return _isAscending ? idA.compareTo(idB) : idB.compareTo(idA);
    });

    return filtered;
  }

  void _applyFilters() {
    if (!mounted) return;
    setState(() => _filteredViolations = _computeFilteredList());
  }

  int _statusCount([String? status]) {
    if (status == null || status == 'All') {
      return _allViolations.length;
    }
    return _allViolations
        .where((item) => (item['status'] ?? 'Pending').toString() == status)
        .length;
  }

  void _clearAllFilters() {
    setState(() {
      _selectedSeverity = 'All';
      _selectedStatus = 'All';
      _selectedDate = 'All Time';
      _searchController.clear();
      _isAscending = false;
    });
    _applyFilters();
  }

  @override
  Widget build(BuildContext context) {
    final bottomPadding =
        AppTheme.bottomNavClearance + MediaQuery.paddingOf(context).bottom;

    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: RefreshIndicator(
        onRefresh: () => _fetchData(showLoading: false, forcedRefresh: true),
        color: AppTheme.primary,
        child: CustomScrollView(
          controller: _scrollController,
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(child: _buildHeader()),
            SliverToBoxAdapter(child: _buildSearchBar()),
            SliverToBoxAdapter(child: _buildQuickStatusOverview()),
            SliverToBoxAdapter(child: _buildAdvancedFilters()),
            if (_isLoading)
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                sliver: SliverToBoxAdapter(
                  child: ShimmerLoader.buildListSkeleton(),
                ),
              )
            else if (_filteredViolations.isEmpty)
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
                      if (index >= _filteredViolations.length) {
                        return const Padding(
                          padding: EdgeInsets.symmetric(vertical: 16),
                          child: Center(child: CircularProgressIndicator()),
                        );
                      }
                      return RepaintBoundary(
                        child: _buildViolationCard(
                          _filteredViolations[index],
                          index,
                        ),
                      );
                    },
                    childCount: _filteredViolations.length +
                        (_loadingMore ? 1 : 0),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return AppUi.gradientHeader(
      greeting: '${_filteredViolations.length} cases',
      title: 'Cases',
      subtitle: 'Search, filter, and open any case record.',
      badge: AppUi.iconCircle(
        icon: Icons.inventory_2_outlined,
        color: AppTheme.primaryNavy,
        size: 36,
        iconSize: 18,
        backgroundColor: Colors.white,
      ),
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _headerIconButton(
            Icons.refresh_rounded,
            AppTheme.textSub,
            _fetchData,
          ),
          const SizedBox(width: 8),
          _headerIconButton(
            _isAscending
                ? Icons.arrow_upward_rounded
                : Icons.arrow_downward_rounded,
            Colors.white,
            _showSortSheet,
            filled: true,
          ),
        ],
      ),
      bottom: Row(
        children: [
          AppUi.brandPill(
            label: '${_filteredViolations.length} records found',
            leading: Icon(
              Icons.inventory_2_outlined,
              size: 14,
              color: Colors.white.withValues(alpha: 0.92),
            ),
          ),
          if (_hasActiveFilters()) ...[
            const SizedBox(width: 10),
            AppUi.brandPill(
              label:
                  'Showing ${_filteredViolations.length} of ${_allViolations.length}',
              leading: Icon(
                Icons.filter_list_rounded,
                size: 14,
                color: Colors.white.withValues(alpha: 0.92),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildQuickStatusOverview() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 6, 20, 10),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            _buildQuickStatusCard(
              label: 'All cases',
              count: _statusCount(),
              icon: Icons.folder_open_rounded,
              status: 'All',
              color: AppTheme.primaryNavy,
            ),
            const SizedBox(width: 10),
            _buildQuickStatusCard(
              label: 'Pending',
              count: _statusCount('Pending'),
              icon: Icons.schedule_rounded,
              status: 'Pending',
              color: AppTheme.accentAmber,
            ),
            const SizedBox(width: 10),
            _buildQuickStatusCard(
              label: 'Hearings',
              count: _statusCount('Hearing Scheduled'),
              icon: Icons.gavel_rounded,
              status: 'Hearing Scheduled',
              color: AppTheme.accentCyan,
            ),
            const SizedBox(width: 10),
            _buildQuickStatusCard(
              label: 'Closed',
              count: _statusCount('Closed'),
              icon: Icons.check_circle_outline_rounded,
              status: 'Closed',
              color: AppTheme.accentEmerald,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickStatusCard({
    required String label,
    required int count,
    required IconData icon,
    required String status,
    required Color color,
  }) {
    final isSelected =
        _selectedStatus == status ||
        (status == 'All' && _selectedStatus == 'All');
    return GestureDetector(
      onTap: () {
        HapticFeedback.selectionClick();
        setState(() => _selectedStatus = status);
        _applyFilters();
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 124,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: isSelected ? AppTheme.heroGradient : null,
          color: isSelected ? null : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isSelected
                ? Colors.transparent
                : AppTheme.inputBorder.withValues(alpha: 0.8),
          ),
          boxShadow: AppTheme.cardShadow,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AppUi.iconCircle(
              icon: icon,
              color: isSelected ? Colors.white : color,
              size: 34,
              iconSize: 16,
              backgroundColor: isSelected
                  ? Colors.white.withValues(alpha: 0.14)
                  : color.withValues(alpha: 0.12),
            ),
            const SizedBox(height: 12),
            Text(
              '$count',
              style: GoogleFonts.inter(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                color: isSelected ? Colors.white : AppTheme.textMain,
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: isSelected
                    ? Colors.white.withValues(alpha: 0.84)
                    : AppTheme.textMuted,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _headerIconButton(
    IconData icon,
    Color color,
    VoidCallback onTap, {
    bool filled = false,
  }) {
    return GestureDetector(
      onTap: () {
        HapticFeedback.lightImpact();
        onTap();
      },
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: filled
              ? Colors.white.withValues(alpha: 0.2)
              : Colors.white.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(icon, color: filled ? Colors.white : color, size: 20),
      ),
    );
  }

  bool _hasActiveFilters() {
    return _selectedSeverity != 'All' ||
        _selectedStatus != 'All' ||
        _selectedDate != 'All Time' ||
        _searchController.text.isNotEmpty;
  }

  void _showSortSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
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
            const SizedBox(height: 20),
            Text(
              "Sort & Options",
              style: GoogleFonts.inter(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: AppTheme.primaryNavy,
              ),
            ),
            const SizedBox(height: 20),
            Text(
              "ORDER",
              style: GoogleFonts.inter(
                fontSize: 10,
                fontWeight: FontWeight.w900,
                color: AppTheme.textMuted,
                letterSpacing: 1.5,
              ),
            ),
            const SizedBox(height: 10),
            _sortOption(
              ctx,
              icon: Icons.arrow_downward_rounded,
              label: "Newest First",
              isSelected: !_isAscending,
              onTap: () {
                if (mounted) setState(() => _isAscending = false);
                _applyFilters();
                Navigator.pop(ctx);
              },
            ),
            const SizedBox(height: 8),
            _sortOption(
              ctx,
              icon: Icons.arrow_upward_rounded,
              label: "Oldest First",
              isSelected: _isAscending,
              onTap: () {
                if (mounted) setState(() => _isAscending = true);
                _applyFilters();
                Navigator.pop(ctx);
              },
            ),
            const SizedBox(height: 20),
            if (_hasActiveFilters()) ...[
              TextButton.icon(
                onPressed: () {
                  _clearAllFilters();
                  Navigator.pop(ctx);
                },
                icon: const Icon(
                  Icons.clear_all_rounded,
                  color: AppTheme.accentRose,
                ),
                label: Text(
                  "Clear All Filters",
                  style: GoogleFonts.inter(
                    color: AppTheme.accentRose,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _sortOption(
    BuildContext ctx, {
    required IconData icon,
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: isSelected
              ? AppTheme.primaryNavy.withValues(alpha: 0.06)
              : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected
                ? AppTheme.primaryNavy.withValues(alpha: 0.2)
                : AppTheme.inputBorder,
          ),
        ),
        child: Row(
          children: [
            Icon(
              icon,
              size: 18,
              color: isSelected ? AppTheme.primaryNavy : AppTheme.textMuted,
            ),
            const SizedBox(width: 12),
            Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: isSelected ? AppTheme.primaryNavy : AppTheme.textMain,
              ),
            ),
            const Spacer(),
            if (isSelected)
              const Icon(
                Icons.check_rounded,
                size: 18,
                color: AppTheme.primaryNavy,
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 10),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppTheme.inputBorder),
          boxShadow: AppTheme.softShadow,
        ),
        child: TextField(
          controller: _searchController,
          onChanged: (_) {
            if (_debounce?.isActive ?? false) _debounce!.cancel();
            _debounce = Timer(
              const Duration(milliseconds: 300),
              _applyFilters,
            );
          },
          textInputAction: TextInputAction.search,
          onSubmitted: (_) => _applyFilters(),
          style: GoogleFonts.inter(
            fontSize: 15,
            color: AppTheme.textMain,
            fontWeight: FontWeight.w500,
          ),
          decoration: InputDecoration(
            hintText: 'Search student name or violation',
            hintStyle: GoogleFonts.inter(
              color: AppTheme.textHint,
              fontSize: 15,
            ),
            prefixIcon: const Icon(
              Icons.search_rounded,
              color: AppTheme.primary,
              size: 22,
            ),
            suffixIcon: ValueListenableBuilder<TextEditingValue>(
              valueListenable: _searchController,
              builder: (context, value, _) {
                if (value.text.isEmpty) return const SizedBox.shrink();
                return IconButton(
                  onPressed: () {
                    _searchController.clear();
                    _applyFilters();
                  },
                  icon: const Icon(
                    Icons.close_rounded,
                    color: AppTheme.textMuted,
                  ),
                );
              },
            ),
            border: InputBorder.none,
            enabledBorder: InputBorder.none,
            focusedBorder: InputBorder.none,
            contentPadding: const EdgeInsets.symmetric(vertical: 14),
          ),
        ),
      ),
    );
  }

  Widget _buildAdvancedFilters() {
    return AppUi.expandTile(
      title: 'More filters',
      subtitle: _hasActiveFilters()
          ? '${_filteredViolations.length} results · filters active'
          : 'Date, severity, and sort options',
      expanded: _showAdvancedFilters || _hasActiveFilters(),
      onToggle: () => setState(() => _showAdvancedFilters = !_showAdvancedFilters),
      trailing: _hasActiveFilters()
          ? TextButton(
              onPressed: _clearAllFilters,
              child: Text(
                'Clear',
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.w700,
                  color: AppTheme.accentRose,
                  fontSize: 12,
                ),
              ),
            )
          : null,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Date range',
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: AppTheme.textMuted,
            ),
          ),
          const SizedBox(height: 10),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                ...['All Time', 'Today', 'This Week', 'This Month'].map((opt) {
                  final isSelected = _selectedDate == opt;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: _buildFilterChip(
                      label: opt,
                      isSelected: isSelected,
                      onTap: () {
                        HapticFeedback.selectionClick();
                        if (mounted) setState(() => _selectedDate = opt);
                        _applyFilters();
                      },
                    ),
                  );
                }),
              ],
            ),
          ),
          const SizedBox(height: 14),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterGroup(
                  'Severity',
                  ['All', 'Minor', 'Major'],
                  _selectedSeverity,
                  (val) {
                    if (mounted) setState(() => _selectedSeverity = val);
                    _applyFilters();
                  },
                ),
                const SizedBox(width: 16),
                _buildFilterGroup(
                  'Status',
                  ['All', 'Pending', 'Hearing Scheduled', 'Closed'],
                  _selectedStatus,
                  (val) {
                    if (mounted) setState(() => _selectedStatus = val);
                    _applyFilters();
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: Text(
                  '${_filteredViolations.length} matching cases',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textMuted,
                  ),
                ),
              ),
              GestureDetector(
                onTap: () {
                  HapticFeedback.selectionClick();
                  setState(() => _isAscending = !_isAscending);
                  _applyFilters();
                },
                child: AppUi.brandPill(
                  label: _isAscending ? 'Oldest first' : 'Newest first',
                  textColor: AppTheme.primaryNavy,
                  backgroundColor: AppTheme.primaryLight,
                  borderColor: AppTheme.primary.withValues(alpha: 0.12),
                  leading: Icon(
                    _isAscending
                        ? Icons.arrow_upward_rounded
                        : Icons.arrow_downward_rounded,
                    size: 14,
                    color: AppTheme.primaryNavy,
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 6,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip({
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
        decoration: BoxDecoration(
          gradient: isSelected ? AppTheme.heroGradient : null,
          color: isSelected ? null : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? Colors.transparent : AppTheme.inputBorder,
          ),
          boxShadow: isSelected ? AppTheme.softShadow : null,
        ),
        child: Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: isSelected ? Colors.white : AppTheme.textMuted,
          ),
        ),
      ),
    );
  }

  Widget _buildFilterGroup(
    String label,
    List<String> options,
    String current,
    Function(String) onSelected,
  ) {
    return Row(
      children: [
        Text(
          "$label:",
          style: GoogleFonts.inter(
            fontSize: 11,
            fontWeight: FontWeight.w800,
            color: AppTheme.textMuted,
          ),
        ),
        const SizedBox(width: 8),
        ...options.map((opt) {
          bool isSelected = current == opt;
          return Padding(
            padding: const EdgeInsets.only(right: 6),
            child: GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: () {
                HapticFeedback.selectionClick();
                onSelected(opt);
              },
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 7,
                ),
                decoration: BoxDecoration(
                  gradient: isSelected ? AppTheme.heroGradient : null,
                  color: isSelected ? null : Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isSelected
                        ? Colors.transparent
                        : AppTheme.inputBorder,
                  ),
                  boxShadow: isSelected ? AppTheme.softShadow : null,
                ),
                child: Text(
                  opt,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: isSelected ? Colors.white : AppTheme.textMuted,
                  ),
                ),
              ),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildEmptyState() {
    return const EmptyStateWidget(
      icon: Icons.search_off_rounded,
      title: "No results found",
      message:
          "We couldn't find any cases matching your search or filters. Try adjusting them.",
    );
  }

  Widget _buildViolationCard(dynamic violation, int index) {
    final status = violation['status'] ?? 'Pending';
    final severity = violation['violation']?['severity'] ?? 'Minor';
    final severityColor = _getSeverityColor(severity);
    final studentName = violation['student']?['full_name'] ?? 'Unknown Student';
    final violationTitle = violation['violation']?['title'] ?? 'N/A';
    final caseId = "#${(violation['id'] ?? 0).toString().padLeft(4, '0')}";

    return RepaintBoundary(
      child: Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.inputBorder.withValues(alpha: 0.8)),
        boxShadow: AppTheme.softShadow,
      ),
      clipBehavior: Clip.antiAlias,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
        onTap: () {
          HapticFeedback.mediumImpact();
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => CaseDetailsScreen(caseId: violation['id']),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          child: Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  gradient: severity == 'Major'
                      ? AppTheme.warmGradient
                      : AppTheme.accentGradient,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  _getSeverityIcon(severity),
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
                            studentName,
                            style: GoogleFonts.inter(
                              fontWeight: FontWeight.w800,
                              fontSize: 14,
                              color: AppTheme.textMain,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          caseId,
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.textHint,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      violationTitle,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        color: AppTheme.textMuted,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        AppUi.brandPill(
                          label: severity,
                          textColor: severityColor,
                          backgroundColor: severityColor.withValues(alpha: 0.1),
                          borderColor: severityColor.withValues(alpha: 0.12),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          _formatDate(violation['created_at'] ?? ''),
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            color: AppTheme.textHint,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  VtStatusChip.fromStatus(status),
                  const SizedBox(height: 10),
                  Icon(
                    Icons.chevron_right_rounded,
                    color: AppTheme.textHint,
                    size: 18,
                  ),
                ],
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
    if (dateStr.isEmpty) return '';
    try {
      final date = DateTime.parse(dateStr);
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
      return '${months[date.month - 1]} ${date.day}, ${date.year}';
    } catch (_) {
      return '';
    }
  }

  Color _getSeverityColor(String severity) {
    if (severity == 'Major') return AppTheme.accentAmber;
    return AppTheme.primaryNavy;
  }

  IconData _getSeverityIcon(String severity) {
    if (severity == 'Major') return Icons.warning_amber_rounded;
    return Icons.info_outline_rounded;
  }
}
