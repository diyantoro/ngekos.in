import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import '../src/platform_file.dart';

class ApiService {
  static String? _token;

  static const Duration _timeout = Duration(seconds: 25);

  /// Token disimpan di Android Keystore / iOS Keychain (bukan SharedPreferences)
  /// agar tidak mudah terbaca oleh pihak luar. Diganti null saat test.
  static FlutterSecureStorage? _secureStorage;

  static FlutterSecureStorage get _storage =>
      _secureStorage ??= const FlutterSecureStorage(
        aOptions: AndroidOptions(encryptedSharedPreferences: true),
      );

  /// Dapat diganti saat test dengan MockClient dari package:http.
  static http.Client client = http.Client();

  static Future<String?> get token async {
    if (_token != null) return _token;
    _token = await _storage.read(key: 'auth_token');
    return _token;
  }

  static Future<void> setToken(String? newToken) async {
    _token = newToken;
    if (newToken != null) {
      await _storage.write(key: 'auth_token', value: newToken);
    } else {
      await _storage.delete(key: 'auth_token');
    }
  }

  static Future<Map<String, String>> _headers() async {
    final t = await token;
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (t != null) {
      headers['Authorization'] = 'Bearer $t';
    }
    return headers;
  }

  static Future<dynamic> get(String url) async {
    final response = await client
        .get(
          Uri.parse(url),
          headers: await _headers(),
        )
        .timeout(_timeout, onTimeout: _timeoutException);
    return _handleResponse(response);
  }

  static Future<dynamic> post(String url, {Map<String, dynamic>? body}) async {
    final response = await client
        .post(
          Uri.parse(url),
          headers: await _headers(),
          body: body != null ? jsonEncode(body) : null,
        )
        .timeout(_timeout, onTimeout: _timeoutException);
    return _handleResponse(response);
  }

  static Future<dynamic> put(String url, {Map<String, dynamic>? body}) async {
    final response = await client
        .put(
          Uri.parse(url),
          headers: await _headers(),
          body: body != null ? jsonEncode(body) : null,
        )
        .timeout(_timeout, onTimeout: _timeoutException);
    return _handleResponse(response);
  }

  static Future<dynamic> delete(String url, {Map<String, dynamic>? body}) async {
    final response = await client
        .delete(
          Uri.parse(url),
          headers: await _headers(),
          body: body != null ? jsonEncode(body) : null,
        )
        .timeout(_timeout, onTimeout: _timeoutException);
    return _handleResponse(response);
  }

  static Future<dynamic> postMultipart(String url, {Map<String, String>? fields, PlatformFile? file, String? fileField}) async {
    final t = await token;
    final request = http.MultipartRequest('POST', Uri.parse(url));
    request.headers['Accept'] = 'application/json';
    if (t != null) request.headers['Authorization'] = 'Bearer $t';
    if (fields != null) request.fields.addAll(fields);
    if (file != null && fileField != null) {
      request.files.add(http.MultipartFile.fromBytes(fileField, file.bytes, filename: file.name));
    }
    final streamedResponse = await request.send().timeout(_timeout, onTimeout: _timeoutException);
    final response = await http.Response.fromStream(streamedResponse);
    return _handleResponse(response);
  }

  static Never _timeoutException() {
    throw ApiException(
      statusCode: 0,
      message: 'Waktu koneksi habis. Periksa koneksi internet atau pastikan server aktif.',
    );
  }

  static dynamic _handleResponse(http.Response response) {
    // Server mati / bermasalah: hapus token yang sudah tidak berlaku.
    if (response.statusCode == 401) setToken(null);

    dynamic body;
    try {
      body = jsonDecode(response.body);
    } catch (_) {
      // Respons bukan JSON (mis. HTML error dari server) — jangan sampai crash,
      // hanya tampilkan status generic.
      body = null;
    }

    if (body is Map<String, dynamic> && body['errors'] is Map) {
      // Backend menyebut field yg bermasalah (validation error) — bungkus jadi pesan.
      final first = (body['errors'] as Map).values.firstWhere(
            (v) => v is List && v.isNotEmpty,
            orElse: () => '',
          );
      if (first is List && first.isNotEmpty) {
        throw ApiException(
          statusCode: response.statusCode,
          message: first.first.toString(),
          errors: body['errors'] as Map<String, dynamic>?,
        );
      }
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }
    throw ApiException(
      statusCode: response.statusCode,
      message: body?['message'] ?? 'Terjadi kesalahan',
      errors: body?['errors'],
    );
  }
}

class ApiException implements Exception {
  final int statusCode;
  final String message;
  final Map<String, dynamic>? errors;

  ApiException({required this.statusCode, required this.message, this.errors});

  @override
  String toString() => message;
}
