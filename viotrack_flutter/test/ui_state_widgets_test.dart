import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:viotrack_flutter/widgets/case_timeline_widget.dart';
import 'package:viotrack_flutter/widgets/cases_fetch_error_state.dart';

void main() {
  testWidgets('CaseTimelineWidget renders without overflow on narrow width', (tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: MediaQuery(
          data: MediaQueryData(size: Size(320, 640)),
          child: Scaffold(
            body: SingleChildScrollView(
              child: CaseTimelineWidget(
                currentStatus: 'Hearing Scheduled',
              ),
            ),
          ),
        ),
      ),
    );

    expect(find.byType(CaseTimelineWidget), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('CasesFetchErrorState shows retry action', (tester) async {
    var retried = false;

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: CasesFetchErrorState(onRetry: () => retried = true),
        ),
      ),
    );

    expect(find.text('Unable to load cases'), findsOneWidget);
    await tester.tap(find.text('Try again'));
    await tester.pump();
    expect(retried, isTrue);
  });
}
