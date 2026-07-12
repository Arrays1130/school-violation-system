import '../theme/app_theme.dart';
import 'package:flutter/material.dart';

/// Canonical case workflow — mirrors web `caseStatus.js` and `StudentCase::STATUSES`.
class CaseStatus {
  CaseStatus._();

  static const workflowSteps = [
    'Pending',
    'Hearing Scheduled',
    'Hearing',
    'Closed',
  ];

  static const workflowShortLabels = [
    'Pending',
    'Scheduled',
    'Hearing',
    'Closed',
  ];

  static const filterOptions = [
    'All',
    'Pending',
    'Hearing Scheduled',
    'Hearing',
    'Closed',
    'Endorsed',
  ];

  /// Normalize legacy DB/API values to the current workflow vocabulary.
  static String normalize(String? status) {
    final raw = (status ?? 'Pending').trim();
    switch (raw) {
      case 'Resolved':
      case 'Dismissed':
        return 'Closed';
      case 'Open':
      case 'Under OSA Review':
      case 'Endorsed to Grievance':
        return 'Pending';
      default:
        return raw;
    }
  }

  static bool isEndorsed(Map<String, dynamic>? caseData) {
    if (caseData == null) return false;
    final endorsedAt = caseData['endorsed_at'];
    return endorsedAt != null && endorsedAt.toString().isNotEmpty;
  }

  /// Match web `resolveCaseStatus()` for display chips.
  static String resolve(Map<String, dynamic>? caseData) {
    if (caseData == null) return 'Pending';
    if (isEndorsed(caseData)) return 'Endorsed to Grievance';
    return normalize(caseData['status']?.toString());
  }

  static String displayLabel(String status, {bool short = false}) {
    if (status == 'Endorsed to Grievance') {
      return 'Endorsed';
    }
    if (status == 'Hearing Scheduled' && short) {
      return 'Scheduled';
    }
    return status;
  }

  static int workflowIndex(String? status) {
    final idx = workflowSteps.indexOf(normalize(status));
    return idx >= 0 ? idx : 0;
  }

  static Color colorFor(String? status, {bool endorsed = false}) {
    if (endorsed || status == 'Endorsed to Grievance') {
      return AppTheme.accentRose;
    }

    switch (normalize(status)) {
      case 'Hearing Scheduled':
        return AppTheme.accentCyan;
      case 'Hearing':
        return AppTheme.primaryIndigo;
      case 'Closed':
        return AppTheme.accentEmerald;
      default:
        return AppTheme.accentAmber;
    }
  }

  static bool matchesFilter(Map<String, dynamic> caseData, String filter) {
    if (filter == 'All') return true;
    if (filter == 'Endorsed') return isEndorsed(caseData);

    final status = normalize(caseData['status']?.toString());
    return status == filter;
  }

  static int countForFilter(Iterable<dynamic> cases, String filter) {
    return cases
        .whereType<Map>()
        .where((item) => matchesFilter(Map<String, dynamic>.from(item), filter))
        .length;
  }

  static int hearingStageCount(Iterable<dynamic> cases) {
    return countForFilter(cases, 'Hearing Scheduled') +
        countForFilter(cases, 'Hearing');
  }
}
