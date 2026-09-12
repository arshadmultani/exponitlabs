import 'package:drift/drift.dart';

@DataClassName('SlideAnalyticEntity')
class SlideAnalytics extends Table {
  IntColumn get id => integer().autoIncrement()();
  TextColumn get dcrUuid => text().nullable()();
  TextColumn get doctorUuid => text().nullable()();
  IntColumn get slideIndex => integer()();
  IntColumn get durationSeconds => integer()();
  DateTimeColumn get timestamp => dateTime()();
  TextColumn get syncStatus =>
      text().withDefault(const Constant('pending'))(); // 'pending' | 'synced'
}
