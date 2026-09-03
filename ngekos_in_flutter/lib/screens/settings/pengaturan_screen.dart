import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/pengaturan_service.dart';
import '../../services/auth_service.dart';
import '../../widgets/greeting_banner.dart';
import '../../widgets/tab_bar_widget.dart';
import '../../widgets/notifikasi_popup.dart';
import '../../widgets/user_avatar.dart';

class PengaturanScreen extends StatefulWidget {
  const PengaturanScreen({super.key});

  @override
  State<PengaturanScreen> createState() => _PengaturanScreenState();
}

class _PengaturanScreenState extends State<PengaturanScreen> {
  int _tab = 0;
  bool _isSuperAdmin = false;
  bool _bolehKelola = false;

  // Situs
  final _situsNamaController = TextEditingController();
  final _situsDeskripsiController = TextEditingController();
  final _situsEmailController = TextEditingController();
  final _situsTeleponController = TextEditingController();
  final _situsAlamatController = TextEditingController();

  // Kos
  String _jatuhTempo = 'akhir';
  final _dendaController = TextEditingController(text: '0');

  // Profil
  final _namaController = TextEditingController();
  final _noHpController = TextEditingController();
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  // Notifikasi
  Map<String, bool> _notifikasi = {
    'tagihan_baru': true,
    'pembayaran_diverifikasi': true,
    'chat_baru': true,
  };

  @override
  void initState() {
    super.initState();
    final auth = context.read<AuthProvider>();
    _isSuperAdmin = auth.user?.peran == 'super_admin';
    _namaController.text = auth.user?.nama ?? '';
    _noHpController.text = auth.user?.noHp ?? '';
    if (_isSuperAdmin) _tab = 0;
    _loadPengaturan();
  }

  Future<void> _loadPengaturan() async {
    try {
      final data = await PengaturanService.getPengaturan();
      if (!mounted) return;
      setState(() {
        _bolehKelola = data['boleh_kelola'] == true;
        final situs = (data['situs'] as Map?) ?? const {};
        _situsNamaController.text = (situs['nama'] ?? '') as String;
        _situsDeskripsiController.text = (situs['deskripsi'] ?? '') as String;
        _situsEmailController.text = (situs['email'] ?? '') as String;
        _situsTeleponController.text = (situs['telepon'] ?? '') as String;
        _situsAlamatController.text = (situs['alamat'] ?? '') as String;
        final kos = (data['kos'] as Map?) ?? const {};
        _jatuhTempo = (kos['jatuh_tempo'] ?? 'akhir') as String;
        _dendaController.text = (kos['denda_per_hari'] ?? 0).toString();
        final notif = (data['notifikasi'] as Map?) ?? const {};
        _notifikasi = {
          'tagihan_baru': notif['tagihan_baru'] == true,
          'pembayaran_diverifikasi': notif['pembayaran_diverifikasi'] == true,
          'chat_baru': notif['chat_baru'] == true,
        };
      });
    } catch (_) {
      // Biarkan nilai default bila gagal memuat.
    }
  }

  @override
  void dispose() {
    _situsNamaController.dispose();
    _situsDeskripsiController.dispose();
    _situsEmailController.dispose();
    _situsTeleponController.dispose();
    _situsAlamatController.dispose();
    _dendaController.dispose();
    _namaController.dispose();
    _noHpController.dispose();
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _simpanProfil() async {
    final auth = context.read<AuthProvider>();
    try {
      await AuthService.updateProfile(nama: _namaController.text.trim(), noHp: _noHpController.text.trim());
      await auth.init();
      if (mounted) NotifikasiPopup.show(context, message: 'Profil berhasil diperbarui.');
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
    }
  }

  Future<void> _simpanPassword() async {
    if (_currentPasswordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password saat ini wajib diisi'), backgroundColor: AppTheme.error));
      return;
    }
    if (_newPasswordController.text.length < 8) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password minimal 8 karakter'), backgroundColor: AppTheme.error));
      return;
    }
    if (_newPasswordController.text != _confirmPasswordController.text) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password tidak cocok'), backgroundColor: AppTheme.error));
      return;
    }
    try {
      await PengaturanService.updatePassword(
        currentPassword: _currentPasswordController.text,
        password: _newPasswordController.text,
        passwordConfirmation: _confirmPasswordController.text,
      );
      _currentPasswordController.clear();
      _newPasswordController.clear();
      _confirmPasswordController.clear();
      if (mounted) NotifikasiPopup.show(context, message: 'Password berhasil diubah.');
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      backgroundColor: AppTheme.background,
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            GreetingBanner(
              roleLabel: 'Pengaturan',
              description: 'Kelola profil, keamanan, dan preferensi akun.',
              icon: const Icon(Icons.settings_rounded, color: Colors.white, size: 20),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppTheme.borderLight)),
              child: Column(
                children: [
                  UserAvatar(
                    avatar: auth.user?.avatar,
                    inisial: auth.user?.inisial,
                    nama: auth.user?.nama,
                    size: AvatarSize.xl,
                  ),
                  const SizedBox(height: 12),
                  Text(auth.user?.nama ?? '', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text(auth.user?.email ?? '', style: const TextStyle(fontSize: 14, color: AppTheme.textSecondary)),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Container(
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppTheme.borderLight)),
              child: Column(
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: TabBarWidget(
                      tabs: [
                        if (_bolehKelola) 'Situs',
                        if (_bolehKelola) 'Kos',
                        'Profil',
                        'Notifikasi',
                      ],
                      selectedIndex: _tab,
                      onTabChanged: (i) => setState(() => _tab = i),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: _buildTabContent(),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTabContent() {
    if (_bolehKelola && _tab == 0) return _buildSitusTab();
    if (_bolehKelola && _tab == 1) return _buildKosTab();
    if ((!_bolehKelola && _tab == 0) || (_bolehKelola && _tab == 2)) return _buildProfilTab();
    return _buildNotifikasiTab();
  }

  Widget _buildSitusTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildLabel('Nama Aplikasi'),
        TextField(controller: _situsNamaController, decoration: const InputDecoration(hintText: 'Ngekos.in')),
        const SizedBox(height: 12),
        _buildLabel('Deskripsi'),
        TextField(controller: _situsDeskripsiController, maxLines: 2, decoration: const InputDecoration(hintText: 'Deskripsi singkat')),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              _buildLabel('Email'), TextField(controller: _situsEmailController, keyboardType: TextInputType.emailAddress),
            ])),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              _buildLabel('Telepon'), TextField(controller: _situsTeleponController),
            ])),
          ],
        ),
        const SizedBox(height: 12),
        _buildLabel('Alamat'), TextField(controller: _situsAlamatController),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: () async {
          try {
            await PengaturanService.simpanSitus(
              nama: _situsNamaController.text.trim(),
              deskripsi: _situsDeskripsiController.text.trim(),
              email: _situsEmailController.text.trim(),
              telepon: _situsTeleponController.text.trim(),
              alamat: _situsAlamatController.text.trim(),
            );
            if (mounted) NotifikasiPopup.show(context, message: 'Pengaturan situs berhasil disimpan.');
          } catch (e) {
            if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
          }
        }, child: const Text('Simpan Pengaturan Situs')),
      ],
    );
  }

  Widget _buildKosTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildLabel('Tanggal Jatuh Tempo'),
        DropdownButtonFormField<String>(
          initialValue: _jatuhTempo,
          decoration: const InputDecoration(),
          items: [
            const DropdownMenuItem(value: 'akhir', child: Text('Akhir bulan')),
            ...List.generate(28, (i) => DropdownMenuItem(value: '${i + 1}', child: Text('Tanggal ${i + 1}'))),
          ],
          onChanged: (v) => setState(() => _jatuhTempo = v ?? 'akhir'),
        ),
        const SizedBox(height: 12),
        _buildLabel('Denda Keterlambatan (Rp/hari)'),
        TextField(controller: _dendaController, keyboardType: TextInputType.number),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: () async {
          try {
            await PengaturanService.simpanKos(jatuhTempo: _jatuhTempo, dendaPerHari: _dendaController.text);
            if (mounted) NotifikasiPopup.show(context, message: 'Pengaturan kos berhasil disimpan.');
          } catch (e) {
            if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
          }
        }, child: const Text('Simpan Pengaturan Kos')),
      ],
    );
  }

  Widget _buildProfilTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildLabel('Nama Lengkap'),
        TextField(controller: _namaController),
        const SizedBox(height: 12),
        _buildLabel('No. HP'),
        TextField(controller: _noHpController, keyboardType: TextInputType.phone),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _simpanProfil, child: const Text('Simpan Profil')),
        const Divider(height: 32),
        _buildLabel('Ganti Password'),
        TextField(controller: _currentPasswordController, obscureText: true, decoration: const InputDecoration(hintText: 'Password saat ini')),
        const SizedBox(height: 12),
        TextField(controller: _newPasswordController, obscureText: true, decoration: const InputDecoration(hintText: 'Password baru')),
        const SizedBox(height: 12),
        TextField(controller: _confirmPasswordController, obscureText: true, decoration: const InputDecoration(hintText: 'Ulangi password baru')),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: _simpanPassword, child: const Text('Ubah Password')),
      ],
    );
  }

  Widget _buildNotifikasiTab() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildNotifTile('tagihan_baru', 'Email saat tagihan bulanan dibuat'),
        _buildNotifTile('pembayaran_diverifikasi', 'Email saat pembayaran diverifikasi'),
        _buildNotifTile('chat_baru', 'Pemberitahuan pesan chat baru'),
        const SizedBox(height: 16),
        ElevatedButton(onPressed: () async {
          try {
            await PengaturanService.simpanNotifikasi(_notifikasi);
            if (mounted) NotifikasiPopup.show(context, message: 'Preferensi notifikasi berhasil disimpan.');
          } catch (e) {
            if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
          }
        }, child: const Text('Simpan Preferensi')),
      ],
    );
  }

  Widget _buildNotifTile(String key, String label) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: AppTheme.background, borderRadius: BorderRadius.circular(12)),
      child: Row(
        children: [
          Expanded(child: Text(label, style: const TextStyle(fontSize: 14))),
          Switch(
            value: _notifikasi[key] ?? true,
            onChanged: (v) => setState(() => _notifikasi[key] = v),
            activeThumbColor: AppTheme.primary,
          ),
        ],
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Text(text, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
    );
  }
}
