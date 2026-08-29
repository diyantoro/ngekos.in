import 'package:flutter/material.dart';
import '../models/penyewaan.dart';
import '../models/tagihan.dart';
import '../models/pembayaran.dart';
import '../services/dashboard_service.dart';
import '../services/admin_dashboard_service.dart';

class DashboardProvider extends ChangeNotifier {
  Map<String, dynamic>? _dashboardData;
  List<Penyewaan> _penyewaans = [];
  List<Tagihan> _tagihans = [];
  List<Pembayaran> _pembayarans = [];
  bool _isLoading = false;
  String? _error;

  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<Penyewaan> get penyewaans => _penyewaans;
  List<Tagihan> get tagihans => _tagihans;
  List<Pembayaran> get pembayarans => _pembayarans;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadAnakKosDashboard() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _dashboardData = await DashboardService.getAnakKosDashboard();
      _penyewaans = await DashboardService.getPenyewaan();
      _tagihans = await DashboardService.getTagihan();
      _pembayarans = await DashboardService.getPembayaran();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadPemilikDashboard() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _dashboardData = await DashboardService.getPemilikDashboard();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadAdminDashboard() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _dashboardData = await AdminDashboardService.getAdminDashboard();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadSuperAdminDashboard() async {
    _isLoading = true;
    _error = null;
    notifyListeners();
    try {
      _dashboardData = await AdminDashboardService.getSuperAdminDashboard();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _error = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }
}
