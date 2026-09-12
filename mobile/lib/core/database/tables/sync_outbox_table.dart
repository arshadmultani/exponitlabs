import 'package:drift/drift.dart';

@DataClassName('SyncOutboxEntity')
class SyncOutbox extends Table {
  IntColumn get id => integer().autoIncrement()();
  TextColumn get entityType =>
      text()(); // 'doctor' | 'dcr' | 'slide_analytic'
  TextColumn get entityUuid => text()();
  TextColumn get payloadJson => text()();
  TextColumn get status =>
      text().withDefault(const Constant('pending'))(); // 'pending' | 'syncing' | 'failed' | 'synced'
  IntColumn get retryCount => integer().withDefault(const Constant(0))();
  TextColumn get lastError => text().nullable()();
  DateTimeColumn get createdAt => dateTime()();
}
