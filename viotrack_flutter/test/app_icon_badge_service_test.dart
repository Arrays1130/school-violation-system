import 'package:flutter_test/flutter_test.dart';
import 'package:viotrack_flutter/services/app_icon_badge_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  setUp(AppIconBadgeService.resetCache);

  test('update accepts unread counts without throwing on test VM', () async {
    await AppIconBadgeService.update(3);
    await AppIconBadgeService.update(3); // dedupe
    await AppIconBadgeService.clear();
  });
}
