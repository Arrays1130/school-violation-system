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
  Timer? _autoRefreshTimer;
  bool _isAscending = false;
  final ScrollController _scrollController = ScrollController();
  int _page = 1;
  bool _hasMore = true;
  bool _loadingMore = false;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _loadInitialData();
    _autoRefreshTimer =
        Timer.periodic(const Duration(seconds: 30), (_) => _fetchData(showLoading: false));
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
      final result = await _apiService.getViolations(forcedRefresh: true, page: nextPage);
      if (!mounted) return;
      if (result is Map) {
        final data = (result['data'] as List<dynamic>?) ?? [];
        final current = result['current_page'] as int? ?? nextPage;
        final last = result['last_page'] as int? ?? current;
        setState(() {
          _page = current;
          _allViolations = [..._allViolations, ...data];
          _hasMore = current < last;
          _applyFilters();
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
        if (mounted) setState(() {
          if (cachedViolations is Map) {
            _allViolations = (cachedViolations['data'] ?? []) as List<dynamic>;
          } else if (cachedViolations is List) {
            _allViolations = cachedViolations;
          }
          _applyFilters();
          _isLoading = false;
        });
      }
    }
    await _fetchData(showLoading: _isLoading);
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _autoRefreshTimer?.cancel();
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
    _fetchData(showLoading: false);
  }

  Future<void> _fetchData({bool showLoading = true}) async {
    if (showLoading && mounted) setState(() => _isLoading = true);
    try {
      final dynamic result =
          await _apiService.getViolations(forcedRefresh: true, page: 1);
      if (mounted) {
        if (mounted) setState(() {
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
          _applyFilters();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        if (mounted) setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString().replaceAll('Exception: ', ''), style: GoogleFonts.outfit()),
            backgroundColor: Colors.redAccent,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _applyFilters() {
    String query = _searchController.text.toLowerCase();
    final now = DateTime.now();
    if (mounted) setState(() {
      _filteredViolations = _allViolations.where((v) {
        final studentName =
            (v['student']?['full_name'] ?? '').toString().toLowerCase();
        final violationTitle =
            (v['violation']?['title'] ?? '').toString().toLowerCase();
        final severity = v['violation']?['severity'] ?? 'Minor';
        final status = v['status'] ?? 'Pending';
        final dateStr = v['created_at'] ?? '';

        bool matchesSearch =
            studentName.contains(query) || violationTitle.contains(query);
        bool matchesSeverity =
            _selectedSeverity == 'All' || severity == _selectedSeverity;
        bool matchesStatus =
            _selectedStatus == 'All' || status == _selectedStatus;

        // Date filter
        bool matchesDate = true;
        if (_selectedDate != 'All Time' && dateStr.isNotEmpty) {
          try {
            final date = DateTime.parse(dateStr);
            if (_selectedDate == 'Today') {
              matchesDate = date.year == now.year &&
                  date.month == now.month &&
                  date.day == now.day;
            } else if (_selectedDate == 'This Week') {
              final weekStart = now.subtract(Duration(days: now.weekday - 1));
              final startOfWeek = DateTime(weekStart.year, weekStart.month, weekStart.day);
              matchesDate = date.isAfter(startOfWeek.subtract(const Duration(seconds: 1)));
            } else if (_selectedDate == 'This Month') {
              matchesDate =
                  date.year == now.year && date.month == now.month;
            }
          } catch (_) {
            matchesDate = true;
          }
        }

        return matchesSearch && matchesSeverity && matchesStatus && matchesDate;
      }).toList();
      _filteredViolations.sort((a, b) {
        final int idA = a['id'] ?? 0;
        final int idB = b['id'] ?? 0;
        return _isAscending ? idA.compareTo(idB) : idB.compareTo(idA);
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgLight,
      body: Column(
        children: [
          _buildHeader(),
          _buildSearchAndFilters(),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _fetchData,
              color: AppTheme.primary,
              child: _isLoading
                  ? Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      child: ShimmerLoader.buildListSkeleton(),
                    )
                  : _filteredViolations.isEmpty
                      ? SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          child: Container(
                            height: MediaQuery.of(context).size.height * 0.55,
                            alignment: Alignment.center,
                            child: _buildEmptyState(),
                          ),
                        )
                      : ListView.builder(
                          controller: _scrollController,
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                          itemCount: _filteredViolations.length + (_loadingMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index >= _filteredViolations.length) {
                              return const Padding(
                                padding: EdgeInsets.symmetric(vertical: 16),
                                child: Center(child: CircularProgressIndicator()),
                              );
                            }
                            return _buildViolationCard(
                                _filteredViolations[index], index);
                          },
                        ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return AppUi.gradientHeader(
      greeting: 'Violation records',
      title: 'Cases',
      trailing: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _headerIconButton(Icons.refresh_rounded, AppTheme.textSub, _fetchData),
          const SizedBox(width: 8),
          _headerIconButton(
            _isAscending ? Icons.arrow_upward_rounded : Icons.arrow_downward_rounded,
            Colors.white,
            _showSortSheet,
            filled: true,
          ),
        ],
      ),
      bottom: Text(
        '${_filteredViolations.length} records found',
        style: GoogleFonts.inter(
          fontSize: 13,
          color: Colors.white.withValues(alpha: 0.85),
        ),
      ),
    );
  }

  Widget _headerIconButton(IconData icon, Color color, VoidCallback onTap, {bool filled = false}) {
    return GestureDetector(
      onTap: () {
        HapticFeedback.lightImpact();
        onTap();
      },
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: filled ? Colors.white.withValues(alpha: 0.2) : Colors.white.withValues(alpha: 0.15),
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
            Text("Sort & Options",
                style: GoogleFonts.outfit(
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    color: AppTheme.primaryNavy)),
            const SizedBox(height: 20),
            Text("ORDER",
                style: GoogleFonts.outfit(
                    fontSize: 10,
                    fontWeight: FontWeight.w900,
                    color: AppTheme.textMuted,
                    letterSpacing: 1.5)),
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
                  if (mounted) setState(() {
                    _selectedSeverity = 'All';
                    _selectedStatus = 'All';
                    _selectedDate = 'All Time';
                    _searchController.clear();
                    _isAscending = false;
                  });
                  _applyFilters();
                  Navigator.pop(ctx);
                },
                icon: const Icon(Icons.clear_all_rounded, color: AppTheme.accentRose),
                label: Text("Clear All Filters",
                    style: GoogleFonts.outfit(
                        color: AppTheme.accentRose,
                        fontWeight: FontWeight.w700)),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _sortOption(BuildContext ctx, {
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
          color: isSelected ? AppTheme.primaryNavy.withOpacity(0.06) : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? AppTheme.primaryNavy.withOpacity(0.2) : AppTheme.inputBorder,
          ),
        ),
        child: Row(
          children: [
            Icon(icon,
                size: 18,
                color: isSelected ? AppTheme.primaryNavy : AppTheme.textMuted),
            const SizedBox(width: 12),
            Text(label,
                style: GoogleFonts.outfit(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: isSelected ? AppTheme.primaryNavy : AppTheme.textMain)),
            const Spacer(),
            if (isSelected)
              const Icon(Icons.check_rounded, size: 18, color: AppTheme.primaryNavy),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchAndFilters() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
      child: Column(
        children: [
          // Search bar
          Container(
            decoration: BoxDecoration(
              color: AppTheme.bgLight,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppTheme.inputBorder),
            ),
            child: TextField(
              controller: _searchController,
              onChanged: (value) {
                if (_debounce?.isActive ?? false) _debounce!.cancel();
                _debounce = Timer(
                    const Duration(milliseconds: 300), () => _applyFilters());
              },
              style: GoogleFonts.outfit(fontSize: 14, color: AppTheme.textMain),
              decoration: InputDecoration(
                hintText: "Search student or violation...",
                hintStyle: GoogleFonts.outfit(color: AppTheme.textHint, fontSize: 14),
                prefixIcon: const Icon(Icons.search_rounded,
                    color: AppTheme.textMuted, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? GestureDetector(
                        onTap: () {
                          _searchController.clear();
                          _applyFilters();
                        },
                        child: const Icon(Icons.close_rounded,
                            color: AppTheme.textMuted, size: 18),
                      )
                    : null,
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(vertical: 12),
              ),
            ),
          ),
          const SizedBox(height: 10),
          // Date filter chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                Text("Date:",
                    style: GoogleFonts.outfit(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textMuted)),
                const SizedBox(width: 8),
                ...['All Time', 'Today', 'This Week', 'This Month'].map((opt) {
                  final isSelected = _selectedDate == opt;
                  return Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: GestureDetector(
                      onTap: () {
                        HapticFeedback.selectionClick();
                        if (mounted) setState(() => _selectedDate = opt);
                        _applyFilters();
                      },
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? AppTheme.primaryNavy
                              : Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                              color: isSelected
                                  ? Colors.transparent
                                  : AppTheme.inputBorder),
                        ),
                        child: Text(
                          opt,
                          style: GoogleFonts.outfit(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: isSelected
                                  ? Colors.white
                                  : AppTheme.textMuted),
                        ),
                      ),
                    ),
                  );
                }),
              ],
            ),
          ),
          const SizedBox(height: 8),
          // Severity + Status chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterGroup(
                    "Severity", ["All", "Minor", "Major"], _selectedSeverity,
                    (val) {
                  if (mounted) setState(() => _selectedSeverity = val);
                  _applyFilters();
                }),
                const SizedBox(width: 16),
                _buildFilterGroup(
                    "Status",
                    ["All", "Pending", "Hearing Scheduled", "Closed"],
                    _selectedStatus, (val) {
                  if (mounted) setState(() => _selectedStatus = val);
                  _applyFilters();
                }),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterGroup(String label, List<String> options, String current,
      Function(String) onSelected) {
    return Row(
      children: [
        Text("$label:",
            style: GoogleFonts.outfit(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: AppTheme.textMuted)),
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
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  gradient: isSelected ? AppTheme.accentGradient : null,
                  color: isSelected ? null : Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                      color: isSelected
                          ? Colors.transparent
                          : AppTheme.inputBorder),
                  boxShadow: isSelected
                      ? [
                          BoxShadow(
                              color: AppTheme.accentCyan.withOpacity(0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 3))
                        ]
                      : null,
                ),
                child: Text(
                  opt,
                  style: GoogleFonts.outfit(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: isSelected ? Colors.white : AppTheme.textMuted),
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
      message: "We couldn't find any cases matching your search or filters. Try adjusting them.",
    );
  }

  Widget _buildViolationCard(dynamic violation, int index) {
    final status = violation['status'] ?? 'Pending';
    final severity = violation['violation']?['severity'] ?? 'Minor';

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.inputBorder.withOpacity(0.8)),
        boxShadow: AppTheme.softShadow,
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: () {
          HapticFeedback.mediumImpact();
          Navigator.push(
              context,
              MaterialPageRoute(
                  builder: (context) =>
                      CaseDetailsScreen(caseId: violation['id'])));
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
                  boxShadow: [
                    BoxShadow(
                        color: _getSeverityColor(severity).withOpacity(0.3),
                        blurRadius: 8,
                        offset: const Offset(0, 4)),
                  ],
                ),
                child: Icon(_getSeverityIcon(severity),
                    color: Colors.white, size: 20),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      violation['student']?['full_name'] ?? 'Unknown Student',
                      style: GoogleFonts.outfit(
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                          color: AppTheme.textMain),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      violation['violation']?['title'] ?? 'N/A',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.outfit(
                          fontSize: 12, color: AppTheme.textMuted),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      _formatDate(violation['created_at'] ?? ''),
                      style: GoogleFonts.outfit(
                          fontSize: 10,
                          color: AppTheme.textHint,
                          fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  VtStatusChip.fromStatus(status),
                  const SizedBox(height: 4),
                  Text("#${(violation['id'] ?? 0).toString().padLeft(4, '0')}",
                      style: GoogleFonts.outfit(
                          fontSize: 9,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textHint)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatDate(String dateStr) {
    if (dateStr.isEmpty) return '';
    try {
      final date = DateTime.parse(dateStr);
      final months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      return '${months[date.month - 1]} ${date.day}, ${date.year}';
    } catch (_) {
      return '';
    }
  }

  Widget _buildStatusBadge(String status) {
    return VtStatusChip.fromStatus(status);
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
