import 'package:flutter_test/flutter_test.dart';
import 'package:ngekos_in/main.dart';

void main() {
  testWidgets('App starts', (WidgetTester tester) async {
    await tester.pumpWidget(const NgekosApp());
    expect(find.text('Ngekos.in'), findsOneWidget);
  });
}
