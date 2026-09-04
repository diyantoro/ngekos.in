import 'package:flutter/material.dart';
import '../providers/theme_provider.dart';

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

  // ---- Variant gelap (dipakai oleh darkTheme & polesan halaman utama) ----
  static const Color darkBackground = Color(0xFF0B1220);
  static const Color darkSurface = Color(0xFF151E2E);
  static const Color darkCard = Color(0xFF1B2637);
  static const Color darkTextPrimary = Color(0xFFF1F5F9);
  static const Color darkTextSecondary = Color(0xFF94A3B8);
  static const Color darkTextMuted = Color(0xFF64748B);
  static const Color darkBorder = Color(0xFF2A3648);
  static const Color darkBorderLight = Color(0xFF223045);
  static const Color darkSurfaceGray = Color(0xFF1E293B);

  // ---- Helper sadar-tema (untuk layar yang dipoles manual) ----
  static bool get _dark => ThemeProvider.instance.isDark;

  static Color get bg => _dark ? darkBackground : background;
  static Color get card => _dark ? darkCard : Colors.white;
  static Color get surfaceC => _dark ? darkSurface : surface;
  static Color get txt => _dark ? darkTextPrimary : textPrimary;
  static Color get txtSec => _dark ? darkTextSecondary : textSecondary;
  static Color get txtMuted => _dark ? darkTextMuted : textMuted;
  static Color get bdr => _dark ? darkBorder : border;
  static Color get bdrLight => _dark ? darkBorderLight : borderLight;
  static Color get surfaceGrey => _dark ? darkSurfaceGray : surfaceGray;

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
    if (_dark) {
      switch (tone) {
        case 'teal':
          return const Color(0xFF123F36);
        case 'cyan':
          return const Color(0xFF123A45);
        case 'emerald':
          return const Color(0xFF123D2A);
        case 'sky':
          return const Color(0xFF123C52);
        case 'amber':
          return const Color(0xFF3D3512);
        case 'rose':
          return const Color(0xFF3D1E27);
        default:
          return const Color(0xFF123F36);
      }
    }
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

  static Map<String, Color> get statusColors {
    if (_dark) {
      return {
        'tersedia': const Color(0xFF34D399),
        'terisi': const Color(0xFFFBBF24),
        'aktif': const Color(0xFF34D399),
        'nonaktif': const Color(0xFFFB7185),
        'selesai': const Color(0xFF94A3B8),
        'menunggu': const Color(0xFFFBBF24),
        'menunggu_verifikasi': const Color(0xFFFBBF24),
        'disetujui': const Color(0xFF34D399),
        'check_in': const Color(0xFF38BDF8),
        'diverifikasi': const Color(0xFF34D399),
        'ditolak': const Color(0xFFFB7185),
        'batal': const Color(0xFF94A3B8),
        'belum_bayar': const Color(0xFFFB7185),
        'lunas': const Color(0xFF34D399),
        'terlambat': const Color(0xFFFB7185),
      };
    }
    return {
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
  }

  static Map<String, Color> get statusBgColors {
    if (_dark) {
      return {
        'tersedia': const Color(0xFF123D2A),
        'terisi': const Color(0xFF3D3512),
        'aktif': const Color(0xFF123D2A),
        'nonaktif': const Color(0xFF3D1E27),
        'selesai': const Color(0xFF1E293B),
        'menunggu': const Color(0xFF3D3512),
        'menunggu_verifikasi': const Color(0xFF3D3512),
        'disetujui': const Color(0xFF123D2A),
        'check_in': const Color(0xFF123C52),
        'diverifikasi': const Color(0xFF123D2A),
        'ditolak': const Color(0xFF3D1E27),
        'batal': const Color(0xFF1E293B),
        'belum_bayar': const Color(0xFF3D1E27),
        'lunas': const Color(0xFF123D2A),
        'terlambat': const Color(0xFF3D1E27),
      };
    }
    return {
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
  }

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

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primary,
        brightness: Brightness.dark,
        surface: darkSurface,
      ),
      scaffoldBackgroundColor: darkBackground,
      appBarTheme: AppBarTheme(
        backgroundColor: darkSurface,
        foregroundColor: darkTextPrimary,
        elevation: 0,
        shadowColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
      ),
      cardTheme: CardThemeData(
        color: darkCard,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: Color(0xFF223045)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: darkCard,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF2A3648)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFF2A3648)),
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
      textTheme: TextTheme(
        headlineLarge: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: darkTextPrimary),
        headlineMedium: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: darkTextPrimary),
        headlineSmall: const TextStyle(fontSize: 20, fontWeight: FontWeight.w600, color: darkTextPrimary),
        titleLarge: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: darkTextPrimary),
        titleMedium: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500, color: darkTextPrimary),
        bodyLarge: const TextStyle(fontSize: 16, color: darkTextPrimary),
        bodyMedium: const TextStyle(fontSize: 14, color: darkTextSecondary),
        bodySmall: const TextStyle(fontSize: 12, color: darkTextSecondary),
      ),
    );
  }
}
