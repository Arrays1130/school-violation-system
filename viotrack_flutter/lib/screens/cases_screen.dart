import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:flutter/services.dart';
import '../api_service.dart';
import '../theme/app_theme.dart';
import 'case_details_screen.dart';
import '../widgets/skeleton_loader.dart';

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
  Timer? _debounce;
  Timer? _autoRefreshTimer;
  bool _isAscending = false;

  @override
  void initState() {
    super.initState();
    _fetchData();
    _autoRefreshTimer =
        Timer.periodic(const Duration(seconds: 30), (_) => _fetchData());
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _autoRefreshTimer?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  void applyExternalFilters({String? search, String? status}) {
    if (search != null) _searchController.text = search;
    if (status != null) _selectedStatus = status;
    _applyFilters();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoading = true);
    try {
      final dynamic result =
          await _apiService.getViolations(forcedRefresh: true);
      setState(() {
        if (result is Map) {
          _allViolations = result['data'] as List<dynamic>;
        } else if (result is List) {
          _allViolations = result;
        }
        _applyFilters();
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  void _applyFilters() {
    String query = _searchController.text.toLowerCase();
    setState(() {
      _filteredViolations = _allViolations.where((v) {
        final studentName =
            (v['student']?['full_name'] ?? '').toString().toLowerCase();
        final violationTitle =
            (v['violation']?['title'] ?? '').toString().toLowerCase();
        final severity = v['violation']?['severity'] ?? 'Minor';
        final status = v['status'] ?? 'Pending';
        bool matchesSearch =
            studentName.contains(query) || violationTitle.contains(query);
        bool matchesSeverity =
            _selectedSeverity == 'All' || severity == _selectedSeverity;
        bool matchesStatus =
            _selectedStatus == 'All' || status == _selectedStatus;
        return matchesSearch && matchesSeverity && matchesStatus;
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
              color: AppTheme.accentPurple,
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
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
                          itemCount: _filteredViolations.length,
                          itemBuilder: (context, index) =>
                              _buildViolationCard(
                                  _filteredViolations[index], index),
                        ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
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
                    Text("Case Explorer",
                        style: GoogleFonts.outfit(
                            fontSize: 22,
                            fontWeight: FontWeight.w900,
                            color: AppTheme.textMain)),
                    Text(
                        "${_filteredViolations.length} records found",
                        style: GoogleFonts.outfit(
                            fontSize: 12, color: AppTheme.textMuted)),
                  ],
                ),
              ),
              // Refresh button
              GestureDetector(
                onTap: () {
                  HapticFeedback.mediumImpact();
                  _fetchData();
                },
                child: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppTheme.bgLight,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppTheme.inputBorder),
                  ),
                  child: const Icon(Icons.refresh_rounded,
                      color: AppTheme.textSub, size: 20),
                ),
              ),
              const SizedBox(width: 10),
              // Sort button
              GestureDetector(
                onTap: () {
                  HapticFeedback.mediumImpact();
                  setState(() => _isAscending = !_isAscending);
                  _applyFilters();
                },
                child: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    gradient: AppTheme.accentGradient,
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                          color: AppTheme.accentPurple.withOpacity(0.3),
                          blurRadius: 8,
                          offset: const Offset(0, 4)),
                    ],
                  ),
                  child: Icon(
                    _isAscending
                        ? Icons.arrow_upward_rounded
                        : Icons.arrow_downward_rounded,
                    color: Colors.white,
                    size: 20,
                  ),
                ),
              ),
            ],
          ),
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
              decoration: const InputDecoration(
                hintText: "Search student or violation...",
                prefixIcon: Icon(Icons.search_rounded,
                    color: AppTheme.textMuted, size: 20),
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding: EdgeInsets.symmetric(vertical: 12),
              ),
            ),
          ),
          const SizedBox(height: 12),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterGroup(
                    "Severity", ["All", "Minor", "Major"], _selectedSeverity,
                    (val) {
                  setState(() => _selectedSeverity = val);
                  _applyFilters();
                }),
                const SizedBox(width: 16),
                _buildFilterGroup(
                    "Status",
                    ["All", "Pending", "Hearing Scheduled", "Resolved"],
                    _selectedStatus, (val) {
                  setState(() => _selectedStatus = val);
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
              onTap: () {
                HapticFeedback.selectionClick();
                onSelected(opt);
              },
              child: AnimatedContainer(
                duration: 200.ms,
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
                              color: AppTheme.accentPurple.withOpacity(0.3),
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
        }).toList(),
      ],
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: AppTheme.bgLight,
              shape: BoxShape.circle,
            ),
            child: Icon(Icons.search_off_rounded,
                size: 48, color: AppTheme.textHint),
          ),
          const SizedBox(height: 16),
          Text("No results found",
              style: GoogleFonts.outfit(
                  color: AppTheme.textMuted,
                  fontSize: 16,
                  fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text("Try adjusting your search or filters",
              style: GoogleFonts.outfit(
                  color: AppTheme.textHint.withOpacity(0.8), fontSize: 12)),
        ],
      ),
    );
  }

  Widget _buildViolationCard(dynamic violation, int index) {
    final status = violation['status'] ?? 'Pending';
    final severity = violation['violation']?['severity'] ?? 'Minor';

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: AppTheme.softShadow,
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(18),
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
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  _buildStatusBadge(status),
                  const SizedBox(height: 4),
                  Text("#${violation['id']}",
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
    ).animate()
        .fadeIn(delay: Duration(milliseconds: 40 * index))
        .slideX(begin: 0.04);
  }

  Widget _buildStatusBadge(String status) {
    Color color = AppTheme.accentAmber;
    if (status == 'Resolved' || status == 'Closed') color = AppTheme.accentEmerald;
    if (status == 'Hearing Scheduled') color = AppTheme.accentPurple;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
          color: color.withOpacity(0.12),
          borderRadius: BorderRadius.circular(8)),
      child: Text(status.toUpperCase(),
          style: GoogleFonts.outfit(
              fontSize: 8, fontWeight: FontWeight.w900, color: color)),
    );
  }

  Color _getSeverityColor(String severity) {
    if (severity == 'Major') return AppTheme.accentAmber;
    return AppTheme.accentIndigo;
  }

  IconData _getSeverityIcon(String severity) {
    if (severity == 'Major') return Icons.warning_amber_rounded;
    return Icons.info_outline_rounded;
  }
}
