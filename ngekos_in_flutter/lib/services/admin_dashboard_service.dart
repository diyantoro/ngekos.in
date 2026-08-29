import '../config/api_config.dart';
import 'api_service.dart';

class AdminDashboardService {
  static Future<Map<String, dynamic>> getAdminDashboard() async {
    return await ApiService.get(ApiConfig.dashboardAdmin);
  }

  static Future<Map<String, dynamic>> getSuperAdminDashboard() async {
    return await ApiService.get(ApiConfig.dashboardSuperAdmin);
  }

  static Future<void> verifyPayment(int pembayaranId, {required bool approve}) async {
    await ApiService.post('${ApiConfig.dashboardSuperAdmin}/pembayaran/$pembayaranId/verifikasi', body: {
      'status': approve ? 'diverifikasi' : 'ditolak',
    });
  }
}
