import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:viotrack_flutter/services/push_navigation_service.dart';

void main() {
  setUp(PushNavigationService.resetForTest);

  test('handleData stores pending case id when navigator is unavailable', () {
    PushNavigationService.handleData({'case_id': '42'});
    expect(PushNavigationService.pendingCaseId, 42);
  });

  test('handleRemoteMessage reads case_id from message data', () {
    final message = RemoteMessage(data: const {'case_id': '7'});
    PushNavigationService.handleRemoteMessage(message);
    expect(PushNavigationService.pendingCaseId, 7);
  });

  test('handleData ignores payloads without case_id', () {
    PushNavigationService.handleData({'title': 'Alert'});
    expect(PushNavigationService.pendingCaseId, isNull);
  });
}
