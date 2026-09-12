import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:drift/native.dart';
import 'package:mobile/core/database/app_database.dart';
import 'package:mobile/core/di/providers.dart';
import 'package:mobile/main.dart';

void main() {
  testWidgets('App renders navigation tabs and brand theme smoke test',
      (WidgetTester tester) async {
    final testDb = AppDatabase(NativeDatabase.memory());

    // Build app with overridden in-memory DB provider
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          databaseProvider.overrideWithValue(testDb),
        ],
        child: const ExponitFieldApp(),
      ),
    );

    await tester.pumpAndSettle();

    // Verify presence of navigation items
    expect(find.text('Doctors'), findsWidgets);
    expect(find.text('Visual Aid'), findsWidgets);
    expect(find.text('DCR'), findsWidgets);
    expect(find.text('Games'), findsWidgets);

    // Verify search bar on initial Doctors screen
    expect(find.byType(TextField), findsOneWidget);

    await testDb.close();
  });
}
