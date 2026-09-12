import 'package:drift/drift.dart';
import 'connection.dart';
import 'tables/dcrs_table.dart';
import 'tables/doctors_table.dart';
import 'tables/products_table.dart';
import 'tables/promotional_inputs_table.dart';
import 'tables/slide_analytics_table.dart';
import 'tables/sync_outbox_table.dart';

part 'app_database.g.dart';

@DriftDatabase(tables: [
  Doctors,
  Products,
  PromotionalInputs,
  Dcrs,
  DcrProducts,
  DcrInputs,
  SlideAnalytics,
  SyncOutbox,
])
class AppDatabase extends _$AppDatabase {
  AppDatabase([QueryExecutor? e]) : super(e ?? openDatabaseConnection());

  @override
  int get schemaVersion => 1;

  // --- Convenience Queries ---

  /// Watch all active doctors ordered by name.
  Stream<List<DoctorEntity>> watchAllDoctors() {
    return (select(doctors)
          ..where((tbl) => tbl.status.equals('active'))
          ..orderBy([(t) => OrderingTerm(expression: t.name)]))
        .watch();
  }

  /// Search doctors by query across name, specialty, clinic, and town.
  Future<List<DoctorEntity>> searchDoctors(String query) {
    if (query.trim().isEmpty) {
      return (select(doctors)
            ..where((tbl) => tbl.status.equals('active'))
            ..orderBy([(t) => OrderingTerm(expression: t.name)]))
          .get();
    }

    final lower = '%${query.toLowerCase()}%';
    return (select(doctors)
          ..where((tbl) =>
              tbl.status.equals('active') &
              (tbl.name.lower().like(lower) |
                  tbl.specialty.lower().like(lower) |
                  tbl.clinicName.lower().like(lower) |
                  tbl.town.lower().like(lower)))
          ..orderBy([(t) => OrderingTerm(expression: t.name)]))
        .get();
  }

  /// Watch pending outbox items for sync worker.
  Stream<List<SyncOutboxEntity>> watchPendingOutbox() {
    return (select(syncOutbox)
          ..where((tbl) => tbl.status.equals('pending'))
          ..orderBy([(t) => OrderingTerm(expression: t.createdAt)]))
        .watch();
  }
}
