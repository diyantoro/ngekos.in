import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../../config/theme.dart';
import '../../widgets/auth_scaffold.dart';
import 'login_screen.dart';
import 'register_screen.dart';

class PilihPeranScreen extends StatelessWidget {
  const PilihPeranScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return AuthScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Masuk sebagai',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.txt),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            'Pilih peran kamu untuk melanjutkan.',
            style: TextStyle(fontSize: 14, color: AppTheme.txtSec),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          _PeranCard(
            imageAsset: 'assets/images/login-tenant.svg',
            title: 'Pencari Kos',
            subtitle: 'Cari & tanya kamar kos',
            borderColor: AppTheme.primary,
            onTap: () => _navigate(context, 'anak_kos'),
          ),
          const SizedBox(height: 12),
          _PeranCard(
            imageAsset: 'assets/images/login-owner.svg',
            title: 'Pemilik Kos',
            subtitle: 'Kelola & promosikan kos',
            borderColor: AppTheme.accent,
            onTap: () => _navigate(context, 'pemilik'),
          ),
          const SizedBox(height: 24),
          const Divider(color: AppTheme.border),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text('Belum punya akun?', style: TextStyle(fontSize: 14, color: AppTheme.txtSec)),
              const SizedBox(width: 4),
              GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const RegisterScreen(peran: 'anak_kos'))),
                child: Text('Daftar sekarang', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppTheme.primary)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _navigate(BuildContext context, String peran) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => LoginScreen(peran: peran)),
    );
  }
}

class _PeranCard extends StatelessWidget {
  final String imageAsset;
  final String title;
  final String subtitle;
  final Color borderColor;
  final VoidCallback onTap;

  const _PeranCard({
    required this.imageAsset,
    required this.title,
    required this.subtitle,
    required this.borderColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppTheme.card,
      borderRadius: BorderRadius.circular(16),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: borderColor.withValues(alpha: 0.5), width: 2),
          ),
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              SvgPicture.asset(imageAsset, height: 80, width: 80),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppTheme.txt)),
                    const SizedBox(height: 4),
                    Text(subtitle, style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                  ],
                ),
              ),
              Icon(Icons.chevron_right_rounded, color: AppTheme.txtMuted),
            ],
          ),
        ),
      ),
    );
  }
}
