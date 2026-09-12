import 'package:drift/drift.dart';

@DataClassName('DcrEntity')
class Dcrs extends Table {
  IntColumn get id => integer().nullable()();
  TextColumn get uuid => text()();
  DateTimeColumn get date => dateTime()();
  TextColumn get doctorUuid => text()();
  IntColumn get doctorId => integer().nullable()();
  TextColumn get doctorName => text().nullable()();
  TextColumn get remarks => text().nullable()();
  RealColumn get latitude => real().nullable()();
  RealColumn get longitude => real().nullable()();
  TextColumn get signatureSvg => text().nullable()();
  TextColumn get syncStatus =>
      text().withDefault(const Constant('pending'))(); // 'pending' | 'synced' | 'failed'
  DateTimeColumn get createdAt => dateTime()();

  @override
  Set<Column> get primaryKey => {uuid};
}

@DataClassName('DcrProductEntity')
class DcrProducts extends Table {
  IntColumn get id => integer().autoIncrement()();
  TextColumn get dcrUuid => text()();
  IntColumn get productId => integer()();
  IntColumn get quantity => integer().withDefault(const Constant(1))();
}

@DataClassName('DcrInputEntity')
class DcrInputs extends Table {
  IntColumn get id => integer().autoIncrement()();
  TextColumn get dcrUuid => text()();
  IntColumn get promotionalInputId => integer()();
  IntColumn get quantity => integer().withDefault(const Constant(1))();
}
