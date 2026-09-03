import '../config/api_config.dart';
import '../src/platform_file.dart';
import '../models/penyewaan.dart';
import '../models/tagihan.dart';
import '../models/pembayaran.dart';
import 'api_service.dart';

class DashboardService {
  static Future<Map<String, dynamic>> getAnakKosDashboard() async {
    return await ApiService.get(ApiConfig.dashboardAnakKos);
  }

  static Future<List<Penyewaan>> getPenyewaan() async {
    final data = await ApiService.get(ApiConfig.dashboardAnakKosPenyewaan);
    return (data as List).map((e) => Penyewaan.fromJson(e)).toList();
  }

  static Future<List<Tagihan>> getTagihan() async {
    final data = await ApiService.get(ApiConfig.dashboardAnakKosTagihan);
    return (data as List).map((e) => Tagihan.fromJson(e)).toList();
  }

  static Future<List<Pembayaran>> getPembayaran() async {
    final data = await ApiService.get(ApiConfig.dashboardAnakKosPembayaran);
    return (data as List).map((e) => Pembayaran.fromJson(e)).toList();
  }

  static Future<void> ajukanKeluar(int sewaanId) async {
    await ApiService.post(
      '${ApiConfig.dashboardAnakKos}/$sewaanId/keluar',
      body: const {},
    );
  }

  static Future<void> bayar({
    required int tagihanId,
    required String metode,
    required int jumlah,
    PlatformFile? bukti,
  }) async {
    final fields = <String, String>{
      'tagihan_id': tagihanId.toString(),
      'metode': metode,
      'jumlah': jumlah.toString(),
    };
    await ApiService.postMultipart(
      ApiConfig.dashboardAnakKosBayar,
      fields: fields,
      file: bukti,
      fileField: 'bukti',
    );
  }

  static Future<Map<String, dynamic>> getPemilikDashboard() async {
    return await ApiService.get(ApiConfig.dashboardPemilik);
  }

  static Future<dynamic> getPemilikProperti() async {
    return await ApiService.get(ApiConfig.dashboardPemilikProperti);
  }

  static Future<List<dynamic>> getPemilikSewaans() async {
    final dynamic raw = await ApiService.get(ApiConfig.dashboardPemilikSewaans);
    if (raw is List) return raw;
    return [];
  }

  static Future<void> checkout(int sewaanId) async {
    await ApiService.post(
      ApiConfig.pemilikCheckout(sewaanId),
      body: const {},
    );
  }
}
