import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../auth/pilih_peran_screen.dart';
import '../settings/pengaturan_screen.dart';
import '../public/bantuan_screen.dart';
import '../public/bantuan_riwayat_screen.dart';
import '../management/properti_list_screen.dart';
import '../../widgets/user_avatar.dart';

class AkunScreen extends StatelessWidget {
  const AkunScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: AppTheme.card,
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(20),
                    bottomRight: Radius.circular(20),
                  ),
                ),
                child: Column(
                  children: [
                    UserAvatar(
                      avatar: user?.avatar,
                      inisial: user?.inisial,
                      nama: user?.nama,
                      size: AvatarSize.xl,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      user?.nama ?? 'User',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      user?.email ?? '',
                      style: TextStyle(fontSize: 14, color: AppTheme.txtSec),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      decoration: BoxDecoration(
                        color: user?.isAnakKos == true
                            ? AppTheme.primary.withValues(alpha: 0.1)
                            : AppTheme.accent.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        user?.isAnakKos == true ? 'Pencari Kos' : (user?.peran == 'super_admin' ? 'Super Admin' : user?.peran == 'admin' ? 'Admin' : 'Pemilik Kos'),
                        style: TextStyle(
                          fontSize: 12,
                          color: user?.isAnakKos == true ? AppTheme.primary : AppTheme.accent,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              _MenuItem(
                icon: Icons.person_outline_rounded,
                title: 'Edit Profil',
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PengaturanScreen())),
              ),
              _MenuItem(
                icon: Icons.settings_outlined,
                title: 'Pengaturan',
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PengaturanScreen())),
              ),
              _MenuItem(
                icon: Icons.notifications_outlined,
                title: 'Notifikasi',
                onTap: () {},
              ),
              if (user?.isAnakKos == true) ...[
                _MenuItem(
                  icon: Icons.receipt_long_outlined,
                  title: 'Tagihan Saya',
                  onTap: () {},
                ),
                _MenuItem(
                  icon: Icons.key_outlined,
                  title: 'Penyewaan Saya',
                  onTap: () {},
                ),
              ],
              if (user?.isPemilik == true || user?.peran == 'admin' || user?.peran == 'super_admin') ...[
                _MenuItem(
                  icon: Icons.apartment_outlined,
                  title: 'Properti Saya',
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PropertiListScreen(isActive: true))),
                ),
              ],
              _MenuItem(
                icon: Icons.help_outline_rounded,
                title: 'Bantuan',
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BantuanScreen())),
              ),
              _MenuItem(
                icon: Icons.history_rounded,
                title: 'Riwayat Bantuan',
                badge: user?.bantuanBelumDibaca ?? 0,
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BantuanRiwayatScreen())),
              ),
              const SizedBox(height: 16),
              _MenuItem(
                icon: Icons.logout_rounded,
                title: 'Keluar',
                color: AppTheme.error,
                onTap: () async {
                  final confirm = await showDialog<bool>(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: const Text('Keluar'),
                      content: const Text('Yakin ingin keluar?'),
                      actions: [
                        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
                        TextButton(
                          onPressed: () => Navigator.pop(ctx, true),
                          child: const Text('Keluar', style: TextStyle(color: AppTheme.error)),
                        ),
                      ],
                    ),
                  );
                  if (confirm == true) {
                    await auth.logout();
                    if (context.mounted) {
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(builder: (_) => const PilihPeranScreen()),
                      );
                    }
                  }
                },
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}

class _MenuItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final Color? color;
  final VoidCallback onTap;
  final int badge;

  const _MenuItem({required this.icon, required this.title, this.color, required this.onTap, this.badge = 0});

  @override
  Widget build(BuildContext context) {
    final itemColor = color ?? AppTheme.txt;
    return Material(
      color: AppTheme.card,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: AppTheme.bdr),
      ),
      clipBehavior: Clip.antiAlias,
      child: ListTile(
        leading: Icon(icon, color: itemColor),
        title: Text(title, style: TextStyle(color: itemColor, fontWeight: FontWeight.w500)),
        trailing: badge > 0
            ? Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: AppTheme.error, borderRadius: BorderRadius.circular(20)),
                    child: Text(
                      badge.toString(),
                      style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(width: 4),
                  Icon(Icons.chevron_right_rounded, color: color != null ? itemColor : AppTheme.txtSec),
                ],
              )
            : Icon(Icons.chevron_right_rounded, color: color != null ? itemColor : AppTheme.txtSec),
        onTap: onTap,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }
}

