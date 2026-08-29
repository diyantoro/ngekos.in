import 'dart:io';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:permission_handler/permission_handler.dart';

import '../config/api_config.dart';
import '../screens/public/bantuan_riwayat_screen.dart';
import '../services/api_service.dart';

/// Helper notifikasi push (Firebase Cloud Messaging).
///
/// Jika Firebase belum dikonfigurasi (google-services.json belum ada),
/// seluruh fitur ini aman dinonaktifkan tanpa mengganggu aplikasi.
class PushService {
  PushService._();

  /// Dipakai MaterialApp supaya notifikasi bisa menavigasi layar apa pun.
  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();

  /// Callback saat notifikasi pertama kali dipasang (include
  /// FirebaseMessaging.onMessage) — dipakai untuk me-refresh badge.
  static Future<void> Function()? onForeground;

  static bool _ready = false;
  static bool get isReady => _ready;

  static RemoteMessage? _pendingTap;

  /// Panggil sekali di `main()`.
  static Future<void> init() async {
    try {
      await Firebase.initializeApp();
      _ready = true;
    } catch (_) {
      // Firebase belum disiapkan — push nonaktif, app tetap berjalan.
      _ready = false;
      return;
    }

    final messaging = FirebaseMessaging.instance;

    await _mintaIzinNotifikasi();

    // Aplikasi dibuka melalui notifikasi dari kondisi terminated.
    final initial = await messaging.getInitialMessage();
    if (initial != null) _handleTap(initial);

    // Aplikasi dikembalikan ke foreground melalui notifikasi.
    FirebaseMessaging.onMessageOpenedApp.listen(_handleTap);

    // Notifikasi diterima saat app aktif — cukup refresh data (badge).
    FirebaseMessaging.onMessage.listen((_) {
      if (onForeground != null) onForeground!();
    });

    // Token FCM berubah (mis. re-install aplikasi) — daftarkan ulang.
    messaging.onTokenRefresh.listen((_) => registerDeviceToken());

    await registerDeviceToken();
  }

  /// Daftarkan token perangkat ke backend. Panggil ulang setelah login/daftar.
  static Future<void> registerDeviceToken() async {
    if (!_ready) return;
    try {
      final token = await FirebaseMessaging.instance.getToken();
      if (token == null || token.isEmpty) return;
      await ApiService.post(
        ApiConfig.deviceToken,
        body: {
          'token': token,
          'platform': Platform.isIOS ? 'ios' : 'android',
        },
      );
    } catch (_) {
      // Gagal (mis. belum login / server mati) — dicoba lagi saat token refresh.
    }
  }

  /// Hapus token perangkat dari backend saat logout.
  static Future<void> logout() async {
    if (!_ready) return;
    try {
      final token = await FirebaseMessaging.instance.getToken();
      if (token == null || token.isEmpty) return;
      await ApiService.delete(
        ApiConfig.deviceToken,
        body: {'token': token},
      );
    } catch (_) {}
  }

  /// Proses notifikasi "tap" yang masuk sebelum Navigator siap (app dibuka
  /// dari keadaan mati). Panggil dari SplashScreen setelah layar pertama.
  static void processPendingTap() {
    final message = _pendingTap;
    if (message == null) return;
    _pendingTap = null;
    _openMessage(message);
  }

  static void _handleTap(RemoteMessage message) {
    _pendingTap = message;
    final navigator = navigatorKey.currentState;
    if (navigator == null) return;
    _pendingTap = null;
    _openMessage(message);
  }

  static void _openMessage(RemoteMessage message) {
    final navigator = navigatorKey.currentState;
    if (navigator == null) return;

    final type = message.data['type'];
    if (type == 'bantuan_balasan') {
      navigator.push(
        MaterialPageRoute(builder: (_) => const BantuanRiwayatScreen()),
      );
    }
  }

  static Future<void> _mintaIzinNotifikasi() async {
    try {
      await Permission.notification.request();
    } catch (_) {}
  }
}