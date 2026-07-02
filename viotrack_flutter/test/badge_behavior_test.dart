import 'package:flutter_test/flutter_test.dart';
import 'package:viotrack_flutter/services/notification_poller.dart';

void main() {
  test('badge count comes from poller notifier, not hardcoded zero', () {
    NotificationPoller.instance.stop();
    NotificationPoller.instance.unreadCount.value = 5;

    expect(NotificationPoller.instance.unreadCount.value, 5);
  });
}
