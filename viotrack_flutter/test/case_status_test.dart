import 'package:flutter_test/flutter_test.dart';
import 'package:viotrack_flutter/utils/case_status.dart';

void main() {
  test('normalize maps legacy statuses to web workflow', () {
    expect(CaseStatus.normalize('Resolved'), 'Closed');
    expect(CaseStatus.normalize('Open'), 'Pending');
    expect(CaseStatus.normalize('Hearing Scheduled'), 'Hearing Scheduled');
  });

  test('resolve shows endorsed label from endorsed_at', () {
    final endorsed = CaseStatus.resolve({
      'status': 'Pending',
      'endorsed_at': '2026-01-01T00:00:00.000000Z',
    });
    expect(endorsed, 'Endorsed to Grievance');
    expect(CaseStatus.displayLabel(endorsed), 'Endorsed');
  });

  test('workflow index matches web progress steps', () {
    expect(CaseStatus.workflowIndex('Pending'), 0);
    expect(CaseStatus.workflowIndex('Hearing Scheduled'), 1);
    expect(CaseStatus.workflowIndex('Hearing'), 2);
    expect(CaseStatus.workflowIndex('Closed'), 3);
    expect(CaseStatus.workflowIndex('Resolved'), 3);
  });

  test('matches endorsed filter separately from workflow status', () {
    final caseData = {
      'status': 'Pending',
      'endorsed_at': '2026-01-01T00:00:00.000000Z',
    };

    expect(CaseStatus.matchesFilter(caseData, 'Endorsed'), isTrue);
    expect(CaseStatus.matchesFilter(caseData, 'Pending'), isTrue);
    expect(CaseStatus.matchesFilter(caseData, 'Closed'), isFalse);
  });
}
