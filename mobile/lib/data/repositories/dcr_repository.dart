import 'dart:convert';
import 'package:drift/drift.dart';
import 'package:intl/intl.dart';
import 'package:uuid/uuid.dart';
import '../../core/database/app_database.dart';

class DcrRepository {
  DcrRepository({required this.db});

  final AppDatabase db;
  static const _uuidGen = Uuid();

  /// Watch recent DCRs ordered by date.
  Stream<List<DcrEntity>> watchRecentDcrs() {
    return (db.select(db.dcrs)
          ..orderBy([(t) => OrderingTerm.desc(t.date)]))
        .watch();
  }

  /// Get active products for sampling.
  Future<List<ProductEntity>> getAvailableProducts() {
    return (db.select(db.products)
          ..orderBy([(t) => OrderingTerm(expression: t.name)]))
        .get();
  }

  /// Get promotional inputs.
  Future<List<PromotionalInputEntity>> getPromotionalInputs() {
    return (db.select(db.promotionalInputs)
          ..orderBy([(t) => OrderingTerm(expression: t.name)]))
        .get();
  }

  /// Creates a DCR record offline with samples and gifts, and logs to outbox.
  Future<DcrEntity> recordDcrOffline({
    required String doctorUuid,
    required DateTime date,
    String? doctorName,
    String? remarks,
    double? latitude,
    double? longitude,
    String? signatureSvg,
    Map<int, int> sampleQuantities = const {}, // productId -> quantity
    Map<int, int> inputQuantities = const {}, // inputId -> quantity
  }) async {
    final clientUuid = _uuidGen.v4();
    final now = DateTime.now();

    final dcr = DcrEntity(
      uuid: clientUuid,
      date: date,
      doctorUuid: doctorUuid,
      doctorName: doctorName,
      remarks: remarks,
      latitude: latitude,
      longitude: longitude,
      signatureSvg: signatureSvg,
      syncStatus: 'pending',
      createdAt: now,
    );

    await db.into(db.dcrs).insert(dcr);

    // Save Samples
    for (final entry in sampleQuantities.entries) {
      if (entry.value > 0) {
        await db.into(db.dcrProducts).insert(
              DcrProductsCompanion.insert(
                dcrUuid: clientUuid,
                productId: entry.key,
                quantity: Value(entry.value),
              ),
            );
      }
    }

    // Save Inputs
    for (final entry in inputQuantities.entries) {
      if (entry.value > 0) {
        await db.into(db.dcrInputs).insert(
              DcrInputsCompanion.insert(
                dcrUuid: clientUuid,
                promotionalInputId: entry.key,
                quantity: Value(entry.value),
              ),
            );
      }
    }

    // Build payload matching Laravel syncDcrs format
    final payload = {
      'uuid': clientUuid,
      'date': DateFormat('yyyy-MM-dd').format(date),
      'doctor_uuid': doctorUuid,
      'remarks': remarks,
      'latitude': latitude,
      'longitude': longitude,
      'products': sampleQuantities.entries
          .where((e) => e.value > 0)
          .map((e) => {'product_id': e.key, 'quantity': e.value})
          .toList(),
      'inputs': inputQuantities.entries
          .where((e) => e.value > 0)
          .map((e) => {'promotional_input_id': e.key, 'quantity': e.value})
          .toList(),
    };

    await db.into(db.syncOutbox).insert(
          SyncOutboxCompanion.insert(
            entityType: 'dcr',
            entityUuid: clientUuid,
            payloadJson: jsonEncode(payload),
            createdAt: now,
          ),
        );

    return dcr;
  }

  /// Bulk upsert products downloaded from server.
  Future<void> upsertProducts(List<Map<String, dynamic>> rawList) async {
    await db.batch((batch) {
      for (final item in rawList) {
        final id = item['id'] as int?;
        if (id == null) continue;

        batch.insert(
          db.products,
          ProductsCompanion(
            id: Value(id),
            name: Value(item['name']?.toString() ?? 'Product'),
            therapeuticAreaId: Value(item['therapeutic_area_id'] as int?),
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

  /// Bulk upsert promotional inputs downloaded from server.
  Future<void> upsertInputs(List<Map<String, dynamic>> rawList) async {
    await db.batch((batch) {
      for (final item in rawList) {
        final id = item['id'] as int?;
        if (id == null) continue;

        batch.insert(
          db.promotionalInputs,
          PromotionalInputsCompanion(
            id: Value(id),
            name: Value(item['name']?.toString() ?? 'Promotional Input'),
            type: Value(item['type']?.toString()),
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
