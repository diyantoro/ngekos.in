import '../config/api_config.dart';
import '../models/properti.dart';
import 'api_service.dart';

class KatalogService {
  static Future<Map<String, dynamic>> getKatalog({
    String? search,
    String? kota,
    int? hargaMin,
    int? hargaMax,
    int? kapasitas,
    String? sort,
    int? perPage,
    int page = 1,
  }) async {
    final params = <String, String>{'page': page.toString()};
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (kota != null && kota.isNotEmpty) params['kota'] = kota;
    if (hargaMin != null) params['harga_min'] = hargaMin.toString();
    if (hargaMax != null) params['harga_max'] = hargaMax.toString();
    if (kapasitas != null) params['kapasitas'] = kapasitas.toString();
    if (sort != null && sort.isNotEmpty) params['sort'] = sort;
    if (perPage != null) params['per_page'] = perPage.toString();

    final uri = Uri.parse(ApiConfig.katalog).replace(queryParameters: params);
    return await ApiService.get(uri.toString());
  }

  static Future<Properti> getDetail(int id) async {
    final data = await ApiService.get(ApiConfig.katalogDetail(id));
    return Properti.fromJson(data);
  }

  static Future<Map<String, dynamic>> sewaKamar(
    int propertiId,
    int kamarId, {
    required String tanggalMasuk,
  }) async {
    return await ApiService.post(
      ApiConfig.katalogSewaKamar(propertiId, kamarId),
      body: {'tanggal_masuk': tanggalMasuk},
    );
  }
}
