import '../config/api_config.dart';
import '../models/user_full.dart';
import 'api_service.dart';

class PenggunaService {
  static Future<List<UserFull>> getPengguna({String? search, String? peran}) async {
    final params = <String, String>{};
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (peran != null && peran.isNotEmpty) params['peran'] = peran;

    final uri = Uri.parse(ApiConfig.pengguna).replace(queryParameters: params.isNotEmpty ? params : null);
    final dynamic data = await ApiService.get(uri.toString());
    final List items = data is List ? data : (data['data'] as List? ?? []);
    return items.map((e) => UserFull.fromJson(e as Map<String, dynamic>)).toList();
  }

  static Future<void> toggleStatus(int userId) async {
    await ApiService.post(ApiConfig.penggunaDetail(userId), body: {
      'action': 'toggle_status',
    });
  }

  static Future<void> updateRole(int userId, String peran) async {
    await ApiService.put(ApiConfig.penggunaDetail(userId), body: {
      'peran': peran,
    });
  }
}
