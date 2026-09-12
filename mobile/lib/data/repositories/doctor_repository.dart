import 'dart:convert';
import 'package:drift/drift.dart';
import 'package:uuid/uuid.dart';
import '../../core/database/app_database.dart';

class DoctorRepository {
  DoctorRepository({required this.db});

  final AppDatabase db;
  static const _uuidGen = Uuid();

  /// Watch active doctors ordered by name.
  Stream<List<DoctorEntity>> watchActiveDoctors() => db.watchAllDoctors();

  /// Search doctors across name, specialty, clinic, or town.
  Future<List<DoctorEntity>> searchDoctors(String query) =>
      db.searchDoctors(query);

  /// Find doctor by UUID.
  Future<DoctorEntity?> getDoctorByUuid(String uuid) {
    return (db.select(db.doctors)..where((t) => t.uuid.equals(uuid)))
        .getSingleOrNull();
  }

  /// Creates a doctor offline on the field.
  /// Generates client UUID, saves to local SQLite, and logs to sync_outbox.
  Future<DoctorEntity> createDoctorOffline({
    required String name,
    String? email,
    String? phone,
    String? specialty,
    String? qualification,
    String? town,
    String? clinicName,
    String? address,
    double? latitude,
    double? longitude,
  }) async {
    final clientUuid = _uuidGen.v4();
    final now = DateTime.now();

    final doctor = DoctorEntity(
      uuid: clientUuid,
      name: name,
      email: email,
      phone: phone,
      specialty: specialty,
      qualification: qualification,
      town: town,
      clinicName: clinicName,
      address: address,
      latitude: latitude,
      longitude: longitude,
      status: 'active',
      syncStatus: 'pending',
      updatedAt: now,
    );

    await db.into(db.doctors).insert(doctor);

    // Queue in Outbox
    final payload = {
      'uuid': clientUuid,
      'name': name,
      'email': email,
      'phone': phone,
      'specialty': specialty,
      'qualification': qualification,
      'town': town,
      'clinic_name': clinicName,
      'address': address,
      'latitude': latitude,
      'longitude': longitude,
    };

    await db.into(db.syncOutbox).insert(
          SyncOutboxCompanion.insert(
            entityType: 'doctor',
            entityUuid: clientUuid,
            payloadJson: jsonEncode(payload),
            createdAt: now,
          ),
        );

    return doctor;
  }

  /// Bulk upsert doctors received from server sync.
  Future<void> upsertFromServer(List<Map<String, dynamic>> rawList) async {
    await db.batch((batch) {
      for (final item in rawList) {
        final uuid = item['uuid']?.toString();
        if (uuid == null || uuid.isEmpty) continue;

        batch.insert(
          db.doctors,
          DoctorsCompanion(
            id: Value(item['id'] as int?),
            uuid: Value(uuid),
            name: Value(item['name']?.toString() ?? 'Unnamed Doctor'),
            email: Value(item['email']?.toString()),
            phone: Value(item['phone']?.toString()),
            specialty: Value(item['specialty']?.toString()),
            qualification: Value(item['qualification']?.toString()),
            town: Value(item['town']?.toString()),
            areaId: Value(item['area_id'] as int?),
            clinicName: Value(item['clinic_name']?.toString()),
            address: Value(item['address']?.toString()),
            latitude: Value((item['latitude'] as num?)?.toDouble()),
            longitude: Value((item['longitude'] as num?)?.toDouble()),
            status: Value(item['status']?.toString() ?? 'active'),
            syncStatus: const Value('synced'),
            updatedAt: Value(
              item['updated_at'] != null
                  ? DateTime.tryParse(item['updated_at'].toString())
                  : DateTime.now(),
            ),
          ),
          mode: InsertMode.insertOrReplace,
        );
      }
    });
  }
}
