import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../auth/pilih_peran_screen.dart';

class VerifyEmailScreen extends StatefulWidget {
  const VerifyEmailScreen({super.key});

  @override
  State<VerifyEmailScreen> createState() => _VerifyEmailScreenState();
}

class _VerifyEmailScreenState extends State<VerifyEmailScreen> {
  bool _isLoading = false;
  bool _sent = false;

  Future<void> _sendVerification() async {
    setState(() => _isLoading = true);
    try {
      await ApiService.post(ApiConfig.verifyEmail);
      setState(() { _sent = true; _isLoading = false; });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Row(
        children: [
          if (MediaQuery.of(context).size.width > 768)
            Expanded(
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)], begin: Alignment.topLeft, end: Alignment.bottomRight),
                ),
                child: const Center(
                  child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                    Icon(Icons.mark_email_read_rounded, size: 72, color: Colors.white),
                    SizedBox(height: 20),
                    Text('Ngekos.in', style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.white)),
                  ]),
                ),
              ),
            ),
          Expanded(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 400),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const SizedBox(height: 40),
                      if (MediaQuery.of(context).size.width <= 768) ...[
                        const Icon(Icons.home_rounded, size: 48, color: AppTheme.primary),
                        const SizedBox(height: 16),
                      ],
                      Text('Verifikasi Email', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold), textAlign: TextAlign.center),
                      const SizedBox(height: 16),
                      Text(
                        'Terima kasih sudah mendaftar! Sebelum mulai, silakan verifikasi alamat email kamu dengan mengklik tautan yang kami kirimkan. Jika belum menerima email, kami akan kirim ulang.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: AppTheme.txtSec, fontSize: 14, height: 1.5),
                      ),
                      const SizedBox(height: 24),
                      if (_sent) ...[
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(color: const Color(0xFFECFDF5), borderRadius: BorderRadius.circular(12)),
                          child: const Text('Tautan verifikasi baru sudah dikirim ke email kamu.', style: TextStyle(color: AppTheme.success, fontSize: 14), textAlign: TextAlign.center),
                        ),
                        const SizedBox(height: 16),
                      ],
                      ElevatedButton(
                        onPressed: _isLoading ? null : _sendVerification,
                        style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary, padding: const EdgeInsets.symmetric(vertical: 16)),
                        child: _isLoading
                            ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : const Text('Kirim Ulang Verifikasi', style: TextStyle(fontSize: 16)),
                      ),
                      const SizedBox(height: 16),
                      TextButton(
                        onPressed: () async {
                        final auth = context.read<AuthProvider>();
                        await auth.logout();
                        if (context.mounted) {
                          Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen()));
                        }
                        },
                        child: Text('Keluar', style: TextStyle(color: AppTheme.txtSec)),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
