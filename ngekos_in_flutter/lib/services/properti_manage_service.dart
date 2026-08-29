import 'dart:io';
import '../config/api_config.dart';
import '../models/properti.dart';
import '../models/kamar.dart';
import 'api_service.dart';

class PropertiManageService {
  static Future<List<Properti>> getPropertiList() async {
    final dynamic data = await ApiService.get(ApiConfig.propertiManage);
    final List items = data is List ? data : (data['data'] as List? ?? []);
    return items.map((e) => Properti.fromJson(e as Map<String, dynamic>)).toList();
  }

  static Future<Properti> createProperti({
    required String nama,
    required String kota,
    required String alamat,
    String? deskripsi,
    List<String>? fasilitas,
    String? aturan,
    int? dendaPerHari,
    int? harga,
    String jenisHarga = 'bulanan',
    String status = 'aktif',
    File? foto,
  }) async {
    final fields = <String, String>{
      'nama': nama,
      'kota': kota,
      'alamat': alamat,
      'jenis_harga': jenisHarga,
      'status': status,
    };
    if (deskripsi != null) fields['deskripsi'] = deskripsi;
    if (fasilitas != null) fields['fasilitas'] = fasilitas.join(',');
    if (aturan != null) fields['aturan'] = aturan;
    if (dendaPerHari != null) fields['denda_per_hari'] = dendaPerHari.toString();
    if (harga != null) fields['harga'] = harga.toString();

    final data = await ApiService.postMultipart(
      ApiConfig.propertiManage,
      fields: fields,
      file: foto,
      fileField: 'foto',
    );
    return Properti.fromJson((data['properti'] ?? data) as Map<String, dynamic>);
  }

  static Future<Properti> updateProperti(
    int id, {
    required String nama,
    required String kota,
    required String alamat,
    String? deskripsi,
    List<String>? fasilitas,
    String? aturan,
    int? dendaPerHari,
    int? harga,
    String jenisHarga = 'bulanan',
    String status = 'aktif',
    File? foto,
  }) async {
    final fields = <String, String>{
      'nama': nama,
      'kota': kota,
      'alamat': alamat,
      'jenis_harga': jenisHarga,
      'status': status,
      '_method': 'PUT',
    };
    if (deskripsi != null) fields['deskripsi'] = deskripsi;
    if (fasilitas != null) fields['fasilitas'] = fasilitas.join(',');
    if (aturan != null) fields['aturan'] = aturan;
    if (dendaPerHari != null) fields['denda_per_hari'] = dendaPerHari.toString();
    if (harga != null) fields['harga'] = harga.toString();

    final data = await ApiService.postMultipart(
      ApiConfig.propertiManageDetail(id),
      fields: fields,
      file: foto,
      fileField: 'foto',
    );
    return Properti.fromJson((data['properti'] ?? data) as Map<String, dynamic>);
  }

  static Future<void> deleteProperti(int id) async {
    await ApiService.post('${ApiConfig.propertiManageDetail(id)}', body: {'_method': 'DELETE'});
  }

  static Future<List<Kamar>> getKamars(int propertiId) async {
    final dynamic data = await ApiService.get(ApiConfig.kamarManage(propertiId));
    final List items = data is List ? data : (data['data'] as List? ?? []);
    return items.map((e) => Kamar.fromJson(e as Map<String, dynamic>)).toList();
  }

  static Future<void> createKamar(
    int propertiId, {
    required String nama,
    required int kapasitas,
    required int hargaSewaBulanan,
    String jenisHarga = 'bulanan',
    String status = 'tersedia',
    File? foto,
  }) async {
    final fields = <String, String>{
      'nama': nama,
      'kapasitas': kapasitas.toString(),
      'harga_sewa_bulanan': hargaSewaBulanan.toString(),
      'jenis_harga': jenisHarga,
      'status': status,
    };

    await ApiService.postMultipart(
      ApiConfig.kamarManage(propertiId),
      fields: fields,
      file: foto,
      fileField: 'foto',
    );
  }

  static Future<void> updateKamar(
    int propertiId,
    int kamarId, {
    required String nama,
    required int kapasitas,
    required int hargaSewaBulanan,
    String jenisHarga = 'bulanan',
    String status = 'tersedia',
    File? foto,
  }) async {
    final fields = <String, String>{
      'nama': nama,
      'kapasitas': kapasitas.toString(),
      'harga_sewa_bulanan': hargaSewaBulanan.toString(),
      'jenis_harga': jenisHarga,
      'status': status,
      '_method': 'PUT',
    };

    await ApiService.postMultipart(
      ApiConfig.kamarDetail(propertiId, kamarId),
      fields: fields,
      file: foto,
      fileField: 'foto',
    );
  }

  static Future<void> deleteKamar(int propertiId, int kamarId) async {
    await ApiService.post(
      ApiConfig.kamarDetail(propertiId, kamarId),
      body: {'_method': 'DELETE'},
    );
  }
}
