import '../config/api_config.dart';
import 'api_service.dart';

class PengaturanService {
  static Future<Map<String, dynamic>> getPengaturan() async {
    return await ApiService.get(ApiConfig.pengaturan);
  }

  static Future<void> simpanSitus({
    required String nama,
    String? deskripsi,
    String? email,
    String? telepon,
    String? alamat,
  }) async {
    await ApiService.post(ApiConfig.pengaturanSitus, body: {
      'nama': nama,
      if (deskripsi != null) 'deskripsi': deskripsi,
      if (email != null) 'email': email,
      if (telepon != null) 'telepon': telepon,
      if (alamat != null) 'alamat': alamat,
    });
  }

  static Future<void> simpanKos({
    required String jatuhTempo,
    required String dendaPerHari,
  }) async {
    await ApiService.post(ApiConfig.pengaturanKos, body: {
      'jatuh_tempo': jatuhTempo,
      'denda_per_hari': dendaPerHari,
    });
  }

  static Future<void> simpanNotifikasi(Map<String, bool> notifikasi) async {
    await ApiService.post(ApiConfig.pengaturanNotifikasi, body: notifikasi);
  }

  static Future<void> updatePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) async {
    await ApiService.post(ApiConfig.pengaturanGantiPassword, body: {
      'current_password': currentPassword,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
  }

  static Future<void> deleteAccount({required String password}) async {
    await ApiService.post('${ApiConfig.user}/delete', body: {
      'password': password,
    });
  }
}
