import 'dart:io';

class ApiConstants {
  const ApiConstants._();

  /// Resolves the default base URL based on platform.
  /// For Android emulator, 10.0.2.2 maps to host localhost.
  /// For iOS simulator or physical devices via Herd/LAN, use host URL.
  static String get baseUrl {
    if (Platform.isAndroid) {
      // 10.0.2.2 is Android emulator's alias to host loopback
      return 'http://10.0.2.2:8000';
    }
    return 'http://exponitlabs.test';
  }

  // Endpoints
  static const String user = '/api/user';
  static const String masterDataSync = '/api/v1/sync/master-data';
  static const String doctorsBatchSync = '/api/v1/sync/doctors-batch';
  static const String dcrBatchSync = '/api/v1/sync/dcr-batch';
}
