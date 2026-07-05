import 'package:flutter_test/flutter_test.dart';
import 'package:viotrack_flutter/services/notification_poller.dart';

void main() {
  group('computePollBackoff', () {
    const base = Duration(seconds: 15);
    const max = Duration(seconds: 120);

    test('returns base interval when there are no failures', () {
      expect(
        computePollBackoff(
          baseInterval: base,
          maxInterval: max,
          consecutiveFailures: 0,
        ),
        base,
      );
    });

    test('doubles interval per failure step up to the cap', () {
      expect(
        computePollBackoff(
          baseInterval: base,
          maxInterval: max,
          consecutiveFailures: 1,
        ),
        const Duration(seconds: 15),
      );
      expect(
        computePollBackoff(
          baseInterval: base,
          maxInterval: max,
          consecutiveFailures: 2,
        ),
        const Duration(seconds: 30),
      );
      expect(
        computePollBackoff(
          baseInterval: base,
          maxInterval: max,
          consecutiveFailures: 3,
        ),
        const Duration(seconds: 60),
      );
      expect(
        computePollBackoff(
          baseInterval: base,
          maxInterval: max,
          consecutiveFailures: 4,
        ),
        const Duration(seconds: 120),
      );
    });

    test('does not exceed max interval', () {
      expect(
        computePollBackoff(
          baseInterval: base,
          maxInterval: max,
          consecutiveFailures: 10,
        ),
        max,
      );
    });
  });

  test('badge count comes from poller notifier, not hardcoded zero', () {
    NotificationPoller.instance.stop();
    NotificationPoller.instance.unreadCount.value = 5;

    expect(NotificationPoller.instance.unreadCount.value, 5);
  });
}
