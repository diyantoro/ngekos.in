import 'package:flutter/foundation.dart';

class ApiConfig {
  /// Override saat build: flutter run --dart-define=API_BASE_URL=http://192.168.1.5:8000/api
  static const String _overrideBaseUrl = String.fromEnvironment('API_BASE_URL');

  static String get baseUrl {
    if (_overrideBaseUrl.isNotEmpty) return _overrideBaseUrl;
    if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
      // Android emulator: 10.0.2.2 menunjuk ke host machine. Perangkat fisik:
      // gunakan --dart-define=API_BASE_URL=http://<IP-LAN-PC>:8000/api
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://127.0.0.1:8000/api';
  }

  /// Ubah nilai URL penyimpanan (mungkin relatif seperti `/storage/xxx.jpg`)
  /// menjadi URL absolut terhadap host API yang sedang dipakai.
  ///
  /// API mengembalikan path relatif terhadap host-nya sendiri agar gambar
  /// bisa diakses dari emulator/perangkat fisik (yang pakai host berbeda
  /// dengan `APP_URL` di server), bukan dari host server.
  static String? resolveStorageUrl(String? raw) {
    if (raw == null || raw.isEmpty) return null;
    if (raw.startsWith('http://') || raw.startsWith('https://')) return raw;

    // On web, route /storage/ through /api/storage/ to bypass CORS
    // (Apache serves static files without CORS headers)
    if (kIsWeb && raw.startsWith('/storage/')) {
      return '$baseUrl$raw';
    }

    var host = baseUrl;
    if (host.endsWith('/api')) host = host.substring(0, host.length - 4);
    if (host.endsWith('/')) host = host.substring(0, host.length - 1);

    if (raw.startsWith('/')) return host + raw;

    return '$host/$raw';
  }

  static String get login => '$baseUrl/login';
  static String get register => '$baseUrl/register';
  static String get logout => '$baseUrl/logout';
  static String get user => '$baseUrl/user';
  static String get userProfile => '$baseUrl/user/profile';

  static String get forgotPassword => '$baseUrl/forgot-password';
  static String get forgotPasswordOtp => '$baseUrl/forgot-password/otp';
  static String get verifyPasswordOtp => '$baseUrl/forgot-password/verify';
  static String resetPassword(String token) => '$baseUrl/reset-password/$token';

  static String get katalog => '$baseUrl/kos';
  static String katalogDetail(int id) => '$baseUrl/kos/$id';
  static String katalogSewaKamar(int propertiId, int kamarId) =>
      '$baseUrl/kos/$propertiId/kamar/$kamarId/sewa';

  static String get chat => '$baseUrl/chat';
  static String chatDetail(int propertiId) => '$baseUrl/chat/$propertiId';

  static String get dashboardAnakKos => '$baseUrl/dashboard/anak-kos';
  static String get dashboardAnakKosPenyewaan =>
      '$baseUrl/dashboard/anak-kos/penyewaan';
  static String get dashboardAnakKosTagihan =>
      '$baseUrl/dashboard/anak-kos/tagihan';
  static String get dashboardAnakKosPembayaran =>
      '$baseUrl/dashboard/anak-kos/pembayaran';
  static String get dashboardAnakKosBayar =>
      '$baseUrl/dashboard/anak-kos/bayar';

  static String get dashboardPemilik => '$baseUrl/dashboard/pemilik';
  static String get dashboardPemilikProperti =>
      '$baseUrl/dashboard/pemilik/properti';
  static String get dashboardPemilikSewaans =>
      '$baseUrl/dashboard/pemilik/sewaan';
  static String get dashboardPemilikRekap =>
      '$baseUrl/dashboard/pemilik/rekap';
  static String pemilikCheckout(int sewaanId) =>
      '$baseUrl/dashboard/pemilik/sewaan/$sewaanId/checkout';

  static String get dashboardAdmin => '$baseUrl/dashboard/admin';
  static String get dashboardSuperAdmin => '$baseUrl/dashboard/super-admin';

  static String get pengguna => '$baseUrl/pengguna';
  static String penggunaDetail(int id) => '$baseUrl/pengguna/$id';

  static String get propertiManage => '$baseUrl/pemilik/properti';
  static String propertiManageDetail(int id) => '$baseUrl/pemilik/properti/$id';
  static String kamarManage(int propertiId) =>
      '$baseUrl/pemilik/properti/$propertiId/kamar';
  static String kamarDetail(int propertiId, int kamarId) =>
      '$baseUrl/pemilik/properti/$propertiId/kamar/$kamarId';

  static String get pengaturan => '$baseUrl/pengaturan';
  static String get pengaturanSitus => '$baseUrl/pengaturan/situs';
  static String get pengaturanKos => '$baseUrl/pengaturan/kos';
  static String get pengaturanNotifikasi => '$baseUrl/pengaturan/notifikasi';
  static String get pengaturanGantiPassword => '$baseUrl/pengaturan/ganti-password';

  static String get bantuan => '$baseUrl/bantuan';
  static String get bantuanRiwayat => '$baseUrl/bantuan/riwayat';
  static String get bantuanMasuk => '$baseUrl/bantuan/masuk';
  static String bantuanBalas(int id) => '$baseUrl/bantuan/$id/balas';
  static String bantuanBaca(int id) => '$baseUrl/bantuan/$id/baca';

  static String get verifyEmail => '$baseUrl/email/verification-notification';

  static String get deviceToken => '$baseUrl/device-token';
}
