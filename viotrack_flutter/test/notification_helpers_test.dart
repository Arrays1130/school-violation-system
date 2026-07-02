import 'package:flutter_test/flutter_test.dart';
import 'package:viotrack_flutter/services/notification_poller.dart';
import 'package:viotrack_flutter/utils/notification_pagination.dart';

void main() {
  group('NotificationPoller', () {
    test('starts without baseline', () {
      final poller = NotificationPoller.instance;
      poller.stop();
      expect(poller.hasBaseline, isFalse);
    });
  });

  group('NotificationPagination', () {
    test('reset replaces items from paginated response', () {
      final result = NotificationPagination.applyPage(
        existing: [],
        result: {
          'data': [
            {'id': 'a'},
            {'id': 'b'},
          ],
          'meta': {'current_page': 1, 'last_page': 3},
        },
        reset: true,
      );

      expect(result['items'], hasLength(2));
      expect(result['currentPage'], 1);
      expect(result['lastPage'], 3);
    });

    test('append merges next page', () {
      final result = NotificationPagination.applyPage(
        existing: [
          {'id': 'a'},
        ],
        result: {
          'data': [
            {'id': 'b'},
          ],
          'meta': {'current_page': 2, 'last_page': 2},
        },
        reset: false,
        currentPage: 1,
        lastPage: 2,
      );

      expect(result['items'], hasLength(2));
      expect((result['items'] as List).last['id'], 'b');
      expect(result['currentPage'], 2);
    });
  });
}
