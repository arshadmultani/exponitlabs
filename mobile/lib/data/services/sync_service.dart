import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';

class SyncService {
  SyncService({required this.apiClient});

  final ApiClient apiClient;

  /// Fetches delta master data from Laravel server since a given timestamp.
  Future<Map<String, dynamic>> fetchMasterData({DateTime? since}) async {
    final queryParams = <String, dynamic>{};
    if (since != null) {
      queryParams['since'] = since.toIso8601String();
    }

    final response = await apiClient.dio.get<Map<String, dynamic>>(
      ApiConstants.masterDataSync,
      queryParameters: queryParams,
    );

    return response.data ?? {};
  }

  /// Uploads pending locally-created doctors batch.
  Future<List<String>> uploadDoctorsBatch(
      List<Map<String, dynamic>> doctors) async {
    if (doctors.isEmpty) return [];

    final response = await apiClient.dio.post<Map<String, dynamic>>(
      ApiConstants.doctorsBatchSync,
      data: {'doctors': doctors},
    );

    final data = response.data;
    if (data != null && data['synced_uuids'] is List) {
      return (data['synced_uuids'] as List).map((e) => e.toString()).toList();
    }
    return [];
  }

  /// Uploads pending DCRs batch with products and promotional inputs.
  Future<List<String>> uploadDcrsBatch(List<Map<String, dynamic>> dcrs) async {
    if (dcrs.isEmpty) return [];

    final response = await apiClient.dio.post<Map<String, dynamic>>(
      ApiConstants.dcrBatchSync,
      data: {'dcrs': dcrs},
    );

    final data = response.data;
    if (data != null && data['synced_uuids'] is List) {
      return (data['synced_uuids'] as List).map((e) => e.toString()).toList();
    }
    return [];
  }
}
