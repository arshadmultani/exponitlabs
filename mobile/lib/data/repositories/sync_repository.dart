import 'dart:convert';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:drift/drift.dart';
import '../../core/database/app_database.dart';
import '../services/sync_service.dart';
import 'dcr_repository.dart';
import 'doctor_repository.dart';

class SyncResult {
  const SyncResult({
    required this.doctorsDownloaded,
    required this.productsDownloaded,
    required this.inputsDownloaded,
    required this.doctorsUploaded,
    required this.dcrsUploaded,
    this.errorMessage,
  });

  final int doctorsDownloaded;
  final int productsDownloaded;
  final int inputsDownloaded;
  final int doctorsUploaded;
  final int dcrsUploaded;
  final String? errorMessage;

  bool get isSuccess => errorMessage == null;
}

class SyncRepository {
  SyncRepository({
    required this.db,
    required this.syncService,
    required this.doctorRepository,
    required this.dcrRepository,
  });

  final AppDatabase db;
  final SyncService syncService;
  final DoctorRepository doctorRepository;
  final DcrRepository dcrRepository;

  /// Stream of network connectivity states.
  Stream<List<ConnectivityResult>> get connectivityStream =>
      Connectivity().onConnectivityChanged;

  /// Watch count of pending items in outbox.
  Stream<int> watchPendingOutboxCount() {
    final countExpr = db.syncOutbox.id.count();
    final query = db.selectOnly(db.syncOutbox)
      ..addColumns([countExpr])
      ..where(db.syncOutbox.status.equals('pending'));

    return query.map((row) => row.read(countExpr) ?? 0).watchSingle();
  }

  /// Performs full 2-way delta synchronization with Laravel backend.
  Future<SyncResult> performFullSync({DateTime? since}) async {
    try {
      // 1. Uplink: Push pending outbox items first
      final doctorsUploaded = await _pushPendingDoctors();
      final dcrsUploaded = await _pushPendingDcrs();

      // 2. Downlink: Pull latest master data
      final masterData = await syncService.fetchMasterData(since: since);

      int doctorsDownloaded = 0;
      int productsDownloaded = 0;
      int inputsDownloaded = 0;

      if (masterData['doctors'] is List) {
        final docs = (masterData['doctors'] as List)
            .whereType<Map<String, dynamic>>()
            .toList();
        await doctorRepository.upsertFromServer(docs);
        doctorsDownloaded = docs.length;
      }

      if (masterData['products'] is List) {
        final prods = (masterData['products'] as List)
            .whereType<Map<String, dynamic>>()
            .toList();
        await dcrRepository.upsertProducts(prods);
        productsDownloaded = prods.length;
      }

      if (masterData['promotional_inputs'] is List) {
        final inputs = (masterData['promotional_inputs'] as List)
            .whereType<Map<String, dynamic>>()
            .toList();
        await dcrRepository.upsertInputs(inputs);
        inputsDownloaded = inputs.length;
      }

      return SyncResult(
        doctorsDownloaded: doctorsDownloaded,
        productsDownloaded: productsDownloaded,
        inputsDownloaded: inputsDownloaded,
        doctorsUploaded: doctorsUploaded,
        dcrsUploaded: dcrsUploaded,
      );
    } catch (e) {
      return SyncResult(
        doctorsDownloaded: 0,
        productsDownloaded: 0,
        inputsDownloaded: 0,
        doctorsUploaded: 0,
        dcrsUploaded: 0,
        errorMessage: e.toString(),
      );
    }
  }

  Future<int> _pushPendingDoctors() async {
    final pending = await (db.select(db.syncOutbox)
          ..where((t) =>
              t.entityType.equals('doctor') & t.status.equals('pending')))
        .get();

    if (pending.isEmpty) return 0;

    final batch = <Map<String, dynamic>>[];
    for (final item in pending) {
      try {
        final json = jsonDecode(item.payloadJson) as Map<String, dynamic>;
        batch.add(json);
      } catch (_) {}
    }

    final syncedUuids = await syncService.uploadDoctorsBatch(batch);

    // Mark outbox & doctor entity as synced
    for (final uuid in syncedUuids) {
      await (db.update(db.syncOutbox)
            ..where((t) => t.entityUuid.equals(uuid)))
          .write(const SyncOutboxCompanion(status: Value('synced')));

      await (db.update(db.doctors)..where((t) => t.uuid.equals(uuid)))
          .write(const DoctorsCompanion(syncStatus: Value('synced')));
    }

    return syncedUuids.length;
  }

  Future<int> _pushPendingDcrs() async {
    final pending = await (db.select(db.syncOutbox)
          ..where(
              (t) => t.entityType.equals('dcr') & t.status.equals('pending')))
        .get();

    if (pending.isEmpty) return 0;

    final batch = <Map<String, dynamic>>[];
    for (final item in pending) {
      try {
        final json = jsonDecode(item.payloadJson) as Map<String, dynamic>;
        batch.add(json);
      } catch (_) {}
    }

    final syncedUuids = await syncService.uploadDcrsBatch(batch);

    for (final uuid in syncedUuids) {
      await (db.update(db.syncOutbox)
            ..where((t) => t.entityUuid.equals(uuid)))
          .write(const SyncOutboxCompanion(status: Value('synced')));

      await (db.update(db.dcrs)..where((t) => t.uuid.equals(uuid)))
          .write(const DcrsCompanion(syncStatus: Value('synced')));
    }

    return syncedUuids.length;
  }
}
