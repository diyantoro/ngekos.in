import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static String? _token;

  static const Duration _timeout = Duration(seconds: 25);

  /// Dapat diganti saat test dengan MockClient dari package:http.
  static http.Client client = http.Client();

  static Future<String?> get token async {
    if (_token != null) return _token;
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    return _token;
  }

  static Future<void> setToken(String? newToken) async {
    _token = newToken;
    final prefs = await SharedPreferences.getInstance();
    if (newToken != null) {
      await prefs.setString('auth_token', newToken);
    } else {
      await prefs.remove('auth_token');
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

  static Future<dynamic> postMultipart(String url, {Map<String, String>? fields, File? file, String? fileField}) async {
    final t = await token;
    final request = http.MultipartRequest('POST', Uri.parse(url));
    request.headers['Accept'] = 'application/json';
    if (t != null) request.headers['Authorization'] = 'Bearer $t';
    if (fields != null) request.fields.addAll(fields);
    if (file != null && fileField != null) {
      request.files.add(await http.MultipartFile.fromPath(fileField, file.path));
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
    final body = jsonDecode(response.body);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }
    throw ApiException(
      statusCode: response.statusCode,
      message: body['message'] ?? 'Terjadi kesalahan',
      errors: body['errors'],
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
