import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/bantuan_service.dart';
import '../../widgets/notifikasi_popup.dart';
import 'bantuan_riwayat_screen.dart';

class BantuanScreen extends StatefulWidget {
  const BantuanScreen({super.key});

  @override
  State<BantuanScreen> createState() => _BantuanScreenState();
}

class _BantuanScreenState extends State<BantuanScreen> {
  final _namaController = TextEditingController();
  final _emailController = TextEditingController();
  final _subjekController = TextEditingController();
  final _pesanController = TextEditingController();
  bool _isLoading = false;

  static const List<Map<String, String>> _faq = [
    {'title': 'Cara daftar akun', 'answer': 'Untuk mendaftar, klik tombol "Daftar" di halaman utama lalu isi nama lengkap, email, dan password. Kamu bisa memilih peran: "Anak Kos" atau "Pemilik Kos".'},
    {'title': 'Cara login', 'answer': 'Kamu bisa masuk dengan email dan password di halaman Masuk. Jika lupa password, klik tautan "Lupa password?".'},
    {'title': 'Peran dalam aplikasi', 'answer': 'Aplikasi ini memiliki 4 peran: Anak Kos, Pemilik Kos, Admin, dan Super Admin.'},
    {'title': 'Cara cari kos', 'answer': 'Halaman "Cari Kos" menampilkan semua kos aktif. Kamu bisa mencari berdasarkan nama, kota, harga, dan kapasitas.'},
    {'title': 'Cara chat pemilik', 'answer': 'Buka detail kos lalu klik "Hubungi Pemilik" atau "Chat Pemilik".'},
    {'title': 'Cara pembayaran', 'answer': 'Pembayaran dilakukan dengan transfer lalu ajukan bukti di dashboard Anak Kos.'},
    {'title': 'Tagihan dan denda', 'answer': 'Tagihan dibuat setiap bulan. Denda harian ditambahkan jika melewati jatuh tempo.'},
    {'title': 'Cara kelola kos', 'answer': 'Sebagai Pemilik Kos, buka menu "Kelola Kos" untuk menambah/mengedit properti dan kamar.'},
    {'title': 'Hubungi admin', 'answer': 'Gunakan form "Hubungi Admin" di bawah atau chatbot untuk pertanyaan langsung.'},
    {'title': 'Ganti password', 'answer': 'Buka menu Pengaturan lalu bagian "Profil & Keamanan".'},
  ];

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _namaController.text = user?.nama ?? '';
    _emailController.text = user?.email ?? '';
  }

  @override
  void dispose() {
    _namaController.dispose();
    _emailController.dispose();
    _subjekController.dispose();
    _pesanController.dispose();
    super.dispose();
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: AppTheme.error),
    );
  }

  Future<void> _kirim() async {
    final nama = _namaController.text.trim();
    final email = _emailController.text.trim();
    final pesan = _pesanController.text.trim();
    final emailValid = RegExp(r'^[\w\.\-+]+@[\w\-]+(\.[\w\-]+)+$').hasMatch(email);

    if (nama.isEmpty) {
      _showError('Nama wajib diisi.');
      return;
    }
    if (email.isEmpty || !emailValid) {
      _showError('Email wajib diisi dengan format yang valid.');
      return;
    }
    if (pesan.length < 10) {
      _showError('Pesan minimal 10 karakter.');
      return;
    }

    setState(() => _isLoading = true);
    try {
      await BantuanService.kirimBantuan(
        nama: nama,
        email: email,
        subjek: _subjekController.text.trim().isNotEmpty ? _subjekController.text.trim() : null,
        pesan: pesan,
      );
      _subjekController.clear();
      _pesanController.clear();
      if (mounted) {
        NotifikasiPopup.show(context, message: 'Pesan kamu berhasil dikirim. Admin akan membalas secepatnya, dan balasan bisa dilihat di Riwayat Bantuan.');
      }
    } catch (e) {
      if (mounted) _showError('Pesan gagal terkirim. $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SingleChildScrollView(
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(24, 48, 24, 32),
              decoration: const BoxDecoration(
                gradient: LinearGradient(colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)]),
              ),
              child: const Column(
                children: [
                  Text('Pusat Bantuan', style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.white)),
                  SizedBox(height: 8),
                  Text('Temukan jawaban, atau hubungi admin kami langsung.', style: TextStyle(fontSize: 14, color: Colors.white70)),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Pertanyaan yang Sering Diajukan', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),
                  ...List.generate(_faq.length, (i) => _buildFaqItem(_faq[i])),
                  const SizedBox(height: 32),
                  LayoutBuilder(
                    builder: (context, constraints) {
                      final sempit = constraints.maxWidth < 720;
                      if (sempit) {
                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            _buildContactForm(),
                            const SizedBox(height: 16),
                            _buildInfoPanel(),
                          ],
                        );
                      }
                      return Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(flex: 6, child: _buildContactForm()),
                          const SizedBox(width: 16),
                          Expanded(flex: 5, child: _buildInfoPanel()),
                        ],
                      );
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFaqItem(Map<String, String> item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: AppTheme.card,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.bdrLight),
      ),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.symmetric(horizontal: 16),
          childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          title: Text(item['title']!, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
          children: [
            Text(item['answer']!, style: TextStyle(fontSize: 13, color: AppTheme.txtSec, height: 1.5)),
          ],
        ),
      ),
    );
  }

  Widget _buildContactForm() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppTheme.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.bdrLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Hubungi Admin', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          Text('Tidak menemukan jawaban? Kirim pesan ke admin.', style: TextStyle(fontSize: 13, color: AppTheme.txtSec)),
          const SizedBox(height: 20),
          TextField(controller: _namaController, decoration: const InputDecoration(labelText: 'Nama', hintText: 'Nama kamu')),
          const SizedBox(height: 12),
          TextField(controller: _emailController, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email', hintText: 'nama@email.com')),
          const SizedBox(height: 12),
          TextField(controller: _subjekController, decoration: const InputDecoration(labelText: 'Subjek (opsional)', hintText: 'Contoh: Tidak bisa login')),
          const SizedBox(height: 12),
          TextField(controller: _pesanController, maxLines: 4, decoration: const InputDecoration(labelText: 'Pesan', hintText: 'Ceritakan masalahmu...', alignLabelWithHint: true)),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Balasan bisa dilihat di Riwayat Bantuan', style: TextStyle(fontSize: 11, color: AppTheme.txtMuted)),
              ElevatedButton(
                onPressed: _isLoading ? null : _kirim,
                style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary, padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10)),
                child: _isLoading
                    ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Kirim', style: TextStyle(fontSize: 13)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoPanel() {
    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)]),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Kamu sudah login?', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
              const SizedBox(height: 4),
              const Text('Lihat riwayat pesan bantuanmu dan balasan dari admin.', style: TextStyle(fontSize: 13, color: Colors.white70)),
              const SizedBox(height: 12),
              GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const BantuanRiwayatScreen())),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(8)),
                  child: const Text('Buka Riwayat Bantuan', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.primary)),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(color: AppTheme.card, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppTheme.bdrLight)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Info Bantuan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              _buildInfoItem(Icons.chat_rounded, 'Chatbot siap 24 jam â€” jawaban instan.'),
              const SizedBox(height: 8),
              _buildInfoItem(Icons.schedule_rounded, 'Admin membalas dalam 1x24 jam.'),
              const SizedBox(height: 8),
              _buildInfoItem(Icons.lock_rounded, 'Data pesanmu aman dan hanya dilihat admin.'),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildInfoItem(IconData icon, String text) {
    return Row(
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(color: const Color(0xFFF0FDFA), borderRadius: BorderRadius.circular(8)),
          child: Icon(icon, size: 16, color: AppTheme.primary),
        ),
        const SizedBox(width: 10),
        Expanded(child: Text(text, style: TextStyle(fontSize: 12, color: AppTheme.txtSec))),
      ],
    );
  }
}
