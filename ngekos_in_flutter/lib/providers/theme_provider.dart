import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeProvider extends ChangeNotifier {
  static ThemeProvider? _instance;

  static const String _prefsKey = 'theme_mode';

  ThemeMode _mode = ThemeMode.light;

  ThemeMode get mode => _mode;

  bool get isDark => _mode == ThemeMode.dark;

  /// Instance global agar `AppTheme` (yang memakai konstanta desk) bisa
  /// membaca mode saat ini — dipakai sebagai sumber kebenaran tunggal.
  static ThemeProvider get instance => _instance!;

  ThemeProvider() {
    _instance = this;
  }

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_prefsKey);
    _mode = saved == 'dark'
        ? ThemeMode.dark
        : saved == 'light'
            ? ThemeMode.light
            : ThemeMode.system;
    notifyListeners();
  }

  Future<void> setMode(ThemeMode mode) async {
    if (_mode == mode) return;
    _mode = mode;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_prefsKey, mode.name);
  }

  void toggle() {
    final next = _mode == ThemeMode.dark ? ThemeMode.light : ThemeMode.dark;
    setMode(next);
  }
}
