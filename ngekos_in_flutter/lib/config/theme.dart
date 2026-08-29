import 'package:flutter/material.dart';

class AppTheme {
  static const Color primary = Color(0xFF0D9488);
  static const Color primaryLight = Color(0xFF14B8A6);
  static const Color primaryDark = Color(0xFF0F766E);
  static const Color accent = Color(0xFF10B981);
  static const Color background = Color(0xFFF9FAFB);
  static const Color surface = Colors.white;
  static const Color textPrimary = Color(0xFF111827);
  static const Color textSecondary = Color(0xFF6B7280);
  static const Color textMuted = Color(0xFF9CA3AF);
  static const Color border = Color(0xFFE5E7EB);
  static const Color borderLight = Color(0xFFF3F4F6);
  static const Color error = Color(0xFFEF4444);
  static const Color success = Color(0xFF10B981);
  static const Color warning = Color(0xFFF59E0B);
  static const Color info = Color(0xFF0EA5E9);
  static const Color rose = Color(0xFFF43F5E);

  static const Color cyan = Color(0xFF06B6D4);
  static const Color sky = Color(0xFF0EA5E9);
  static const Color emerald = Color(0xFF10B981);
  static const Color amber = Color(0xFFF59E0B);

  static const Color surfaceGray = Color(0xFFF3F4F6);

  static const LinearGradient primaryGradient = LinearGradient(
    colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient heroGradient = LinearGradient(
    colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static Color getStatColor(String tone) {
    switch (tone) {
      case 'teal':
        return primary;
      case 'cyan':
        return cyan;
      case 'emerald':
        return emerald;
      case 'sky':
        return sky;
      case 'amber':
        return amber;
      case 'rose':
        return rose;
      default:
        return primary;
    }
  }

  static Color getStatBg(String tone) {
    switch (tone) {
      case 'teal':
        return const Color(0xFFF0FDFA);
      case 'cyan':
        return const Color(0xFFECFEFF);
      case 'emerald':
        return const Color(0xFFECFDF5);
      case 'sky':
        return const Color(0xFFF0F9FF);
      case 'amber':
        return const Color(0xFFFFFBEB);
      case 'rose':
        return const Color(0xFFFFF1F2);
      default:
        return const Color(0xFFF0FDFA);
    }
  }

  static Map<String, Color> get statusColors => {
    'tersedia': const Color(0xFF10B981),
    'terisi': amber,
    'aktif': const Color(0xFF10B981),
    'nonaktif': rose,
    'selesai': textSecondary,
    'menunggu': amber,
    'menunggu_verifikasi': amber,
    'disetujui': const Color(0xFF10B981),
    'check_in': sky,
    'diverifikasi': const Color(0xFF10B981),
    'ditolak': rose,
    'batal': textSecondary,
    'belum_bayar': rose,
    'lunas': const Color(0xFF10B981),
    'terlambat': rose,
  };

  static Map<String, Color> get statusBgColors => {
    'tersedia': const Color(0xFFECFDF5),
    'terisi': const Color(0xFFFFFBEB),
    'aktif': const Color(0xFFECFDF5),
    'nonaktif': const Color(0xFFFFF1F2),
    'selesai': surfaceGray,
    'menunggu': const Color(0xFFFFFBEB),
    'menunggu_verifikasi': const Color(0xFFFFFBEB),
    'disetujui': const Color(0xFFECFDF5),
    'check_in': const Color(0xFFF0F9FF),
    'diverifikasi': const Color(0xFFECFDF5),
    'ditolak': const Color(0xFFFFF1F2),
    'batal': surfaceGray,
    'belum_bayar': const Color(0xFFFFF1F2),
    'lunas': const Color(0xFFECFDF5),
    'terlambat': const Color(0xFFFFF1F2),
  };

  static String formatStatus(String status) {
    return status.replaceAll('_', ' ').split(' ').map((w) =>
      w[0].toUpperCase() + w.substring(1)).join(' ');
  }

  static String formatRupiah(int amount) {
    return 'Rp ${amount.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';
  }

  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primary,
        brightness: Brightness.light,
        surface: surface,
      ),
      scaffoldBackgroundColor: background,
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.white,
        foregroundColor: textPrimary,
        elevation: 0,
        shadowColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: borderLight),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: primary, width: 2),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primary,
          foregroundColor: Colors.white,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
      textTheme: const TextTheme(
        headlineLarge: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: textPrimary),
        headlineMedium: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: textPrimary),
        headlineSmall: TextStyle(fontSize: 20, fontWeight: FontWeight.w600, color: textPrimary),
        titleLarge: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: textPrimary),
        titleMedium: TextStyle(fontSize: 16, fontWeight: FontWeight.w500, color: textPrimary),
        bodyLarge: TextStyle(fontSize: 16, color: textPrimary),
        bodyMedium: TextStyle(fontSize: 14, color: textSecondary),
        bodySmall: TextStyle(fontSize: 12, color: textSecondary),
      ),
    );
  }
}
