import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/theme.dart';
import '../providers/auth_provider.dart';


class ChatbotWidget extends StatefulWidget {
  const ChatbotWidget({super.key});

  @override
  State<ChatbotWidget> createState() => _ChatbotWidgetState();
}

class _ChatbotWidgetState extends State<ChatbotWidget> {
  bool _isOpen = false;
  final _inputController = TextEditingController();
  final _scrollController = ScrollController();
  final List<Map<String, String>> _messages = [];

  static const List<Map<String, String>> _faq = [
    {'keywords': 'daftar,register,buat akun,akun baru,mendaftar,sign up', 'answer': 'Untuk mendaftar, klik tombol "Daftar" di halaman utama lalu isi nama lengkap, email, dan password. Kamu bisa memilih peran: "Anak Kos" untuk mencari kamar, atau "Pemilik Kos" untuk mendaftarkan kos-mu.'},
    {'keywords': 'login,masuk,lupa password,reset password,lupa sandi', 'answer': 'Kamu bisa masuk dengan email dan password di halaman Masuk. Jika lupa password, klik tautan "Lupa password?" di halaman masuk.'},
    {'keywords': 'peran,role,anak kos,pemilik kos,super admin,admin', 'answer': 'Aplikasi ini memiliki 4 peran: Anak Kos (mencari & menyewa kamar), Pemilik Kos (mendaftarkan kos), Admin (mengelola kamar & verifikasi), Super Admin (mengelola seluruh sistem).'},
    {'keywords': 'cari kos,katalog,pencarian,filter,promosi', 'answer': 'Halaman "Cari Kos" menampilkan semua kos aktif. Kamu bisa mencari berdasarkan nama, kota, harga maksimal, dan kapasitas.'},
    {'keywords': 'chat,pesan,tanya pemilik,hubungi pemilik,kirim pesan', 'answer': 'Untuk bertanya ke pemilik kos: buka detail kos, lalu klik "Chat Pemilik" atau "Hubungi Pemilik".'},
    {'keywords': 'pembayaran,bayar,transfer,verifikasi,bukti', 'answer': 'Pembayaran dilakukan dengan transfer lalu mengajukan pembayaran pada tagihan di dashboard Anak Kos. Admin akan memverifikasi bukti pembayaranmu.'},
    {'keywords': 'tagihan,denda,telah,jatuh tempo,terlambat', 'answer': 'Tagihan dibuat setiap bulan sesuai periode sewa. Jika melewati jatuh tempo, denda harian akan ditambahkan.'},
    {'keywords': 'kelola kos,tambah kos,properti,kamar,tambah kamar', 'answer': 'Sebagai Pemilik Kos, buka menu "Kelola Kos" di dashboard. Dari sana kamu bisa menambah kos baru, mengisi deskripsi/fasilitas, dan mengatur kamar.'},
    {'keywords': 'kontak,hubungi,bantuan,admin,keluhan,masalah,error,bug', 'answer': 'Kamu bisa menghubungi admin melalui form "Hubungi Admin" di halaman Bantuan, atau kirim pesan melalui chatbot ini.'},
    {'keywords': 'password,sandi,kata sandi,ganti password,ubah password', 'answer': 'Untuk mengganti password, buka menu Pengaturan lalu bagian "Profil & Keamanan". Pastikan password baru minimal 8 karakter.'},
    {'keywords': 'hapus akun,delete akun,keluar,logout', 'answer': 'Untuk keluar, klik menu Akun lalu pilih "Keluar". Penghapusan akun bisa dilakukan di Pengaturan.'},
  ];

  @override
  void initState() {
    super.initState();
    _messages.add({
      'from': 'bot',
      'text': 'Halo! Saya asisten virtual Ngekos.in. Ada yang bisa saya bantu? Coba tanya seputar daftar akun, cari kos, chat pemilik, atau pembayaran.',
    });
  }

  @override
  void dispose() {
    _inputController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 200),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _sendMessage([String? text]) {
    final msg = text ?? _inputController.text.trim();
    if (msg.isEmpty) return;
    _inputController.clear();

    setState(() {
      _messages.add({'from': 'user', 'text': msg});
    });

    final answer = _findAnswer(msg.toLowerCase());
    if (answer != null) {
      setState(() {
        _messages.add({'from': 'bot', 'text': answer});
      });
    } else {
      setState(() {
        _messages.add({
          'from': 'bot',
          'text': 'Maaf, saya belum bisa menjawab pertanyaan itu. Pertanyaanmu sudah kami catat. Atau coba tanya dengan kata kunci seperti "chat pemilik", "pembayaran", atau "daftar akun".',
        });
      });
    }
    _scrollToBottom();
  }

  String? _findAnswer(String text) {
    int bestScore = 0;
    String? bestAnswer;
    for (final item in _faq) {
      final keywords = item['keywords']!.split(',');
      int score = 0;
      for (final kw in keywords) {
        if (text.contains(kw.trim())) score++;
      }
      if (score > bestScore) {
        bestScore = score;
        bestAnswer = item['answer'];
      }
    }
    return bestScore > 0 ? bestAnswer : null;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    if (auth.user?.peran == 'super_admin' || auth.user?.peran == 'admin') {
      return const SizedBox.shrink();
    }

    return Stack(
      children: [
        if (_isOpen)
          Positioned(
            bottom: 80,
            right: 16,
            left: 16,
            child: Container(
              height: 420,
              constraints: const BoxConstraints(maxWidth: 380),
              margin: EdgeInsets.zero,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.15), blurRadius: 24, offset: const Offset(0, 8))],
                border: Border.all(color: AppTheme.borderLight),
              ),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    decoration: const BoxDecoration(
                      gradient: AppTheme.primaryGradient,
                      borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), shape: BoxShape.circle),
                          child: const Icon(Icons.smart_toy_rounded, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Asisten Ngekos.in', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white)),
                              Text('Balasan otomatis, cepat', style: TextStyle(fontSize: 11, color: Colors.white70)),
                            ],
                          ),
                        ),
                        GestureDetector(
                          onTap: () => setState(() => _isOpen = false),
                          child: const Icon(Icons.close_rounded, color: Colors.white70, size: 20),
                        ),
                      ],
                    ),
                  ),
                  Expanded(
                    child: ListView.builder(
                      controller: _scrollController,
                      padding: const EdgeInsets.all(12),
                      itemCount: _messages.length,
                      itemBuilder: (context, index) {
                        final msg = _messages[index];
                        final isBot = msg['from'] == 'bot';
                        return Align(
                          alignment: isBot ? Alignment.centerLeft : Alignment.centerRight,
                          child: Container(
                            margin: const EdgeInsets.symmetric(vertical: 4),
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.75),
                            decoration: BoxDecoration(
                              color: isBot ? Colors.white : AppTheme.primary,
                              borderRadius: BorderRadius.only(
                                topLeft: const Radius.circular(16),
                                topRight: const Radius.circular(16),
                                bottomLeft: Radius.circular(isBot ? 4 : 16),
                                bottomRight: Radius.circular(isBot ? 16 : 4),
                              ),
                              border: isBot ? Border.all(color: AppTheme.borderLight) : null,
                              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4, offset: const Offset(0, 1))],
                            ),
                            child: Text(
                              msg['text']!,
                              style: TextStyle(fontSize: 13, color: isBot ? AppTheme.textPrimary : Colors.white, height: 1.4),
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  SizedBox(
                    height: 42,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      children: ['Cara daftar akun', 'Chat pemilik kos', 'Cara pembayaran', 'Hubungi admin']
                          .map((s) => Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 4),
                                child: ActionChip(
                                  label: Text(s, style: const TextStyle(fontSize: 11, color: AppTheme.primary)),
                                  backgroundColor: const Color(0xFFF0FDFA),
                                  side: const BorderSide(color: Color(0xFFCCFBF1)),
                                  onPressed: () => _sendMessage(s),
                                  padding: const EdgeInsets.symmetric(horizontal: 8),
                                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                ),
                              ))
                          .toList(),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      border: Border(top: BorderSide(color: AppTheme.borderLight)),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _inputController,
                            decoration: InputDecoration(
                              hintText: 'Ketik pertanyaanmu...',
                              hintStyle: const TextStyle(fontSize: 13),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
                              filled: true,
                              fillColor: AppTheme.surfaceGray,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                              isDense: true,
                            ),
                            style: const TextStyle(fontSize: 13),
                            textInputAction: TextInputAction.send,
                            onSubmitted: (_) => _sendMessage(),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          width: 36,
                          height: 36,
                          decoration: const BoxDecoration(color: AppTheme.primary, shape: BoxShape.circle),
                          child: IconButton(
                            icon: const Icon(Icons.send_rounded, color: Colors.white, size: 18),
                            onPressed: () => _sendMessage(),
                            padding: EdgeInsets.zero,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        Positioned(
          bottom: 20,
          right: 20,
          child: GestureDetector(
            onTap: () => setState(() => _isOpen = !_isOpen),
            child: Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppTheme.primary, AppTheme.accent, Color(0xFF22C55E)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(color: AppTheme.accent.withValues(alpha: 0.4), blurRadius: 12, offset: const Offset(0, 4)),
                ],
              ),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Icon(
                    _isOpen ? Icons.close_rounded : Icons.smart_toy_rounded,
                    color: Colors.white,
                    size: 26,
                  ),
                  if (!_isOpen)
                    Positioned(
                      top: 0,
                      right: 0,
                      child: Container(
                        width: 14,
                        height: 14,
                        decoration: const BoxDecoration(
                          color: AppTheme.accent,
                          shape: BoxShape.circle,
                          border: Border.fromBorderSide(BorderSide(color: Colors.white, width: 2)),
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }
}
