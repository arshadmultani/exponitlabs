import 'package:drift/drift.dart';

@DataClassName('ProductEntity')
class Products extends Table {
  IntColumn get id => integer()();
  TextColumn get name => text()();
  IntColumn get therapeuticAreaId => integer().nullable()();
  DateTimeColumn get updatedAt => dateTime().nullable()();

  @override
  Set<Column> get primaryKey => {id};
}
