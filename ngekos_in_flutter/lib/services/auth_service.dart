import 'dart:io';
import '../config/api_config.dart';
import '../models/user.dart';
import 'api_service.dart';

class AuthService {
  static Future<Map<String, dynamic>> login(String email, String password, {String? role}) async {
    final data = await ApiService.post(ApiConfig.login, body: {
      'email': email,
      'password': password,
      if (role != null && role.isNotEmpty) 'role': role,
    });
    await ApiService.setToken(data['token']);
    return data;
  }

  static Future<Map<String, dynamic>> register({
    required String nama,
    required String email,
    String? noHp,
    required String peran,
    required String password,
    required String passwordConfirmation,
    File? avatar,
  }) async {
    final fields = <String, String>{
      'nama': nama,
      'email': email,
      'no_hp': noHp ?? '',
      'peran': peran,
      'password': password,
      'password_confirmation': passwordConfirmation,
    };

    Map<String, dynamic> data;
    if (avatar != null) {
      data = await ApiService.postMultipart(
        ApiConfig.register,
        fields: fields,
        file: avatar,
        fileField: 'avatar',
      );
    } else {
      data = await ApiService.post(ApiConfig.register, body: fields);
    }
    await ApiService.setToken(data['token']);
    return data;
  }

  static Future<void> logout() async {
    try {
      await ApiService.post(ApiConfig.logout);
    } finally {
      await ApiService.setToken(null);
    }
  }

  static Future<User?> getCurrentUser() async {
    final token = await ApiService.token;
    if (token == null) return null;
    try {
      final data = await ApiService.get(ApiConfig.user);
      return User.fromJson(data);
    } catch (e) {
      await ApiService.setToken(null);
      return null;
    }
  }

  static Future<Map<String, dynamic>> fetchUserProfile() async {
    return await ApiService.get(ApiConfig.user) as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> updateProfile({String? nama, String? noHp}) async {
    return await ApiService.put(ApiConfig.userProfile, body: {
      if (nama != null) 'nama': nama,
      if (noHp != null) 'no_hp': noHp,
    });
  }
}
