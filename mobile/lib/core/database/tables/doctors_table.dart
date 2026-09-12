import 'package:drift/drift.dart';

@DataClassName('DoctorEntity')
class Doctors extends Table {
  IntColumn get id => integer().nullable()();
  TextColumn get uuid => text()();
  TextColumn get name => text()();
  TextColumn get email => text().nullable()();
  TextColumn get phone => text().nullable()();
  TextColumn get specialty => text().nullable()();
  TextColumn get qualification => text().nullable()();
  TextColumn get town => text().nullable()();
  IntColumn get areaId => integer().nullable()();
  TextColumn get clinicName => text().nullable()();
  TextColumn get address => text().nullable()();
  RealColumn get latitude => real().nullable()();
  RealColumn get longitude => real().nullable()();
  TextColumn get status => text().withDefault(const Constant('active'))();
  TextColumn get syncStatus =>
      text().withDefault(const Constant('synced'))(); // 'pending' | 'synced'
  DateTimeColumn get updatedAt => dateTime().nullable()();

  @override
  Set<Column> get primaryKey => {uuid};
}
