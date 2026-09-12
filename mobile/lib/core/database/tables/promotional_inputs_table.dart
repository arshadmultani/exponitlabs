import 'package:drift/drift.dart';

@DataClassName('PromotionalInputEntity')
class PromotionalInputs extends Table {
  IntColumn get id => integer()();
  TextColumn get name => text()();
  TextColumn get type => text().nullable()();
  DateTimeColumn get updatedAt => dateTime().nullable()();

  @override
  Set<Column> get primaryKey => {id};
}
