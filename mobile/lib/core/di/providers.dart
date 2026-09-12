import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/dcr_repository.dart';
import '../../data/repositories/doctor_repository.dart';
import '../../data/repositories/sync_repository.dart';
import '../../data/services/sync_service.dart';
import '../database/app_database.dart';
import '../network/api_client.dart';

final databaseProvider = Provider<AppDatabase>((ref) {
  final db = AppDatabase();
  ref.onDispose(db.close);
  return db;
});

final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient();
});

final syncServiceProvider = Provider<SyncService>((ref) {
  return SyncService(apiClient: ref.watch(apiClientProvider));
});

final doctorRepositoryProvider = Provider<DoctorRepository>((ref) {
  return DoctorRepository(db: ref.watch(databaseProvider));
});

final dcrRepositoryProvider = Provider<DcrRepository>((ref) {
  return DcrRepository(db: ref.watch(databaseProvider));
});

final syncRepositoryProvider = Provider<SyncRepository>((ref) {
  return SyncRepository(
    db: ref.watch(databaseProvider),
    syncService: ref.watch(syncServiceProvider),
    doctorRepository: ref.watch(doctorRepositoryProvider),
    dcrRepository: ref.watch(dcrRepositoryProvider),
  );
});

// Reactive Streams
final doctorsStreamProvider = StreamProvider<List<DoctorEntity>>((ref) {
  return ref.watch(doctorRepositoryProvider).watchActiveDoctors();
});

final pendingOutboxCountProvider = StreamProvider<int>((ref) {
  return ref.watch(syncRepositoryProvider).watchPendingOutboxCount();
});

final recentDcrsStreamProvider = StreamProvider<List<DcrEntity>>((ref) {
  return ref.watch(dcrRepositoryProvider).watchRecentDcrs();
});
