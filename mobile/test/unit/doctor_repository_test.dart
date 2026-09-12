import 'package:drift/native.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/core/database/app_database.dart';
import 'package:mobile/data/repositories/doctor_repository.dart';

void main() {
  late AppDatabase db;
  late DoctorRepository repository;

  setUp(() {
    // Run in-memory SQLite database for test isolation
    db = AppDatabase(NativeDatabase.memory());
    repository = DoctorRepository(db: db);
  });

  tearDown(() async {
    await db.close();
  });

  group('DoctorRepository Offline Tests', () {
    test('createDoctorOffline creates local doctor and queues outbox item',
        () async {
      final doctor = await repository.createDoctorOffline(
        name: 'Dr. John Doe',
        specialty: 'Cardiologist',
        clinicName: 'Heart Care Center',
        town: 'Downtown',
      );

      expect(doctor.name, equals('Dr. John Doe'));
      expect(doctor.specialty, equals('Cardiologist'));
      expect(doctor.syncStatus, equals('pending'));
      expect(doctor.uuid, isNotEmpty);

      // Verify doctor exists in local database
      final searchResults = await repository.searchDoctors('John');
      expect(searchResults.length, equals(1));
      expect(searchResults.first.uuid, equals(doctor.uuid));

      // Verify pending item is logged in sync_outbox
      final outboxItems = await db.select(db.syncOutbox).get();
      expect(outboxItems.length, equals(1));
      expect(outboxItems.first.entityType, equals('doctor'));
      expect(outboxItems.first.entityUuid, equals(doctor.uuid));
      expect(outboxItems.first.status, equals('pending'));
    });

    test('searchDoctors matches across name, specialty, and clinic', () async {
      await repository.createDoctorOffline(
        name: 'Dr. Sarah Jenkins',
        specialty: 'Pulmonologist',
        clinicName: 'Apex Chest Hospital',
        town: 'North District',
      );

      await repository.createDoctorOffline(
        name: 'Dr. Amit Patel',
        specialty: 'Orthopedic',
        clinicName: 'Lifecare Clinic',
        town: 'West Hub',
      );

      // Search by specialty
      final chestDocs = await repository.searchDoctors('Pulmon');
      expect(chestDocs.length, equals(1));
      expect(chestDocs.first.name, equals('Dr. Sarah Jenkins'));

      // Search by clinic
      final clinicDocs = await repository.searchDoctors('Lifecare');
      expect(clinicDocs.length, equals(1));
      expect(clinicDocs.first.name, equals('Dr. Amit Patel'));
    });
  });
}
