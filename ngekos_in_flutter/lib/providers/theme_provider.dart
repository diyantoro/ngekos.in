import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeProvider extends ChangeNotifier with WidgetsBindingObserver {
  static ThemeProvider? _instance;

  static const String _prefsKey = 'theme_mode';

  ThemeMode _mode = ThemeMode.light;

  ThemeMode get mode => _mode;

  /// Mode efektif yang benar-benar dirender. Mode `system` di-resolve ke
  /// kecerahan perangkat/browser sehingga UI polesan manual (`AppTheme.*`)
  /// selalu selaras dengan tema yang dipakai `MaterialApp`.
  bool get isDark {
    switch (_mode) {
      case ThemeMode.dark:
        return true;
      case ThemeMode.light:
        return false;
      case ThemeMode.system:
        return WidgetsBinding.instance.platformDispatcher.platformBrightness ==
            Brightness.dark;
    }
  }

  /// Instance global agar `AppTheme` (yang memakai konstanta desk) bisa
  /// membaca mode saat ini — dipakai sebagai sumber kebenaran tunggal.
  static ThemeProvider get instance => _instance!;

  ThemeProvider() {
    _instance = this;
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void didChangePlatformBrightness() {
    if (_mode == ThemeMode.system) notifyListeners();
  }

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_prefsKey);
    // Default terang: aplikasi hanya gelap bila pengguna eksplisit
    // men-toggle dark mode, tidak mengikuti kecerahan sistem diam-diam.
    _mode = switch (saved) {
      'dark' => ThemeMode.dark,
      'system' => ThemeMode.system,
      _ => ThemeMode.light,
    };
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
