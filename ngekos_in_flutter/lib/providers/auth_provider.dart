import 'package:flutter/material.dart';
import '../models/user.dart';
import '../services/auth_service.dart';
import '../services/push_service.dart';

class AuthProvider extends ChangeNotifier {
  User? _user;
  bool _isLoading = false;
  String? _error;

  User? get user => _user;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _user != null;
  String? get error => _error;

  bool get isAnakKos => _user?.isAnakKos ?? false;
  bool get isPemilik => _user?.isPemilik ?? false;

  Future<void> init() async {
    _user = await AuthService.getCurrentUser();
    notifyListeners();
  }

  Future<void> refreshUser() async {
    try {
      final data = await AuthService.fetchUserProfile();
      _user = User.fromJson(data);
      notifyListeners();
    } catch (_) {
      // Biarkan user tetap ter-login jika gagal refresh (misal koneksi bermasalah).
    }
  }

  Future<bool> login(String email, String password, {String? role}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final data = await AuthService.login(email, password, role: role);
      _user = User.fromJson(data['user']);
      _isLoading = false;
      notifyListeners();
      await PushService.registerDeviceToken();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register({
    required String nama,
    required String email,
    String? noHp,
    required String peran,
    required String password,
    required String passwordConfirmation,
    dynamic avatar,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      final data = await AuthService.register(
        nama: nama,
        email: email,
        noHp: noHp,
        peran: peran,
        password: password,
        passwordConfirmation: passwordConfirmation,
        avatar: avatar,
      );
      _user = User.fromJson(data['user']);
      _isLoading = false;
      notifyListeners();
      await PushService.registerDeviceToken();
      return true;
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await PushService.logout();
    await AuthService.logout();
    _user = null;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
