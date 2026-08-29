import '../config/api_config.dart';
import '../models/bantuan.dart';
import 'api_service.dart';

class BantuanService {
  static Future<void> kirimBantuan({
    required String nama,
    required String email,
    String? subjek,
    required String pesan,
  }) async {
    await ApiService.post(ApiConfig.bantuan, body: {
      'nama': nama,
      'email': email,
      if (subjek != null) 'subjek': subjek,
      'pesan': pesan,
    });
  }

  static Future<List<BantuanMessage>> getRiwayat() async {
    final data = await ApiService.get(ApiConfig.bantuanRiwayat);
    return (data as List).map((e) => BantuanMessage.fromJson(e)).toList();
  }

  static Future<List<BantuanMessage>> getMasuk() async {
    final data = await ApiService.get(ApiConfig.bantuanMasuk);
    return (data as List).map((e) => BantuanMessage.fromJson(e)).toList();
  }

  static Future<void> balasBantuan(int id, String balasan) async {
    await ApiService.post(ApiConfig.bantuanBalas(id), body: {
      'balasan': balasan,
    });
  }

  static Future<void> tandaiDibaca(int id) async {
    await ApiService.post(ApiConfig.bantuanBaca(id));
  }
}
