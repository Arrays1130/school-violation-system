import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import '../screens/case_details_screen.dart';

/// Routes FCM notification taps to in-app destinations.
class PushNavigationService {
  PushNavigationService._();

  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();

  static int? _pendingCaseId;

  @visibleForTesting
  static int? get pendingCaseId => _pendingCaseId;

  static void handleRemoteMessage(RemoteMessage message) {
    handleData(message.data);
  }

  static void handleData(Map<String, dynamic> data) {
    final raw = data['case_id'];
    if (raw == null) return;
    final caseId = int.tryParse(raw.toString());
    if (caseId == null) return;
    openCaseDetails(caseId);
  }

  static void openCaseDetails(int caseId) {
    try {
      final navigator = navigatorKey.currentState;
      if (navigator == null) {
        _pendingCaseId = caseId;
        return;
      }

      navigator.push(
        MaterialPageRoute(
          builder: (_) => CaseDetailsScreen(caseId: caseId),
        ),
      );
    } catch (_) {
      _pendingCaseId = caseId;
    }
  }

  static void consumePendingNavigation() {
    final caseId = _pendingCaseId;
    if (caseId == null) return;
    _pendingCaseId = null;
    openCaseDetails(caseId);
  }

  @visibleForTesting
  static void resetForTest() {
    _pendingCaseId = null;
  }
}
