import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../services/api_service.dart';
import 'pilih_peran_screen.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _emailController = TextEditingController();
  final _otpController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();

  int _step = 0; // 0 = email, 1 = kode, 2 = password baru
  bool _isLoading = false;
  String? _error;
  String _email = '';
  String? _devOtp;

  @override
  void dispose() {
    _emailController.dispose();
    _otpController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  Future<void> _sendOtp() async {
    if (_emailController.text.trim().isEmpty ||
        !_emailController.text.contains('@')) {
      setState(() => _error = 'Masukkan email yang valid.');
      return;
    }
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final data = await ApiService.post(ApiConfig.forgotPasswordOtp, body: {
        'email': _emailController.text.trim(),
      });
      if (!mounted) return;
      setState(() {
        _email = _emailController.text.trim();
        _devOtp = data is Map<String, dynamic> ? data['dev_otp'] as String? : null;
        _step = 1;
        _isLoading = false;
      });
      if (_devOtp != null && _devOtp!.isNotEmpty) {
        _otpController.text = _devOtp!;
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _verifyOtp() async {
    if (_otpController.text.trim().length < 6) {
      setState(() => _error = 'Masukkan kode verifikasi (6 digit).');
      return;
    }
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      await ApiService.post(ApiConfig.verifyPasswordOtp, body: {
        'email': _email,
        'otp': _otpController.text.trim(),
        'password': _passwordController.text,
        'password_confirmation': _confirmController.text,
      });
      if (!mounted) return;
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen()));
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Password berhasil direset. Silakan login.')),
      );
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  void _nextStep() {
    if (_step == 1) {
      if (_otpController.text.trim().length < 6) {
        setState(() => _error = 'Masukkan kode verifikasi (6 digit).');
        return;
      }
      setState(() {
        _step = 2;
        _error = null;
      });
      return;
    }
    if (_step == 2) {
      if (_passwordController.text.length < 8) {
        setState(() => _error = 'Password minimal 8 karakter.');
        return;
      }
      if (_passwordController.text != _confirmController.text) {
        setState(() => _error = 'Password tidak cocok.');
        return;
      }
      _verifyOtp();
    }
  }

  void _back() {
    setState(() {
      if (_step > 0) {
        _step--;
        _error = null;
      }
    });
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
                  gradient: LinearGradient(
                    colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.lock_reset_rounded, size: 72, color: Colors.white),
                      SizedBox(height: 20),
                      Text('Ngekos.in', style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.white)),
                      SizedBox(height: 8),
                      Text('Temukan kos impianmu', style: TextStyle(fontSize: 16, color: Colors.white70)),
                    ],
                  ),
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
                      if (_step > 0)
                        Align(
                          alignment: Alignment.centerLeft,
                          child: TextButton.icon(
                            onPressed: _back,
                            icon: const Icon(Icons.arrow_back, size: 18),
                            label: const Text('Kembali'),
                          ),
                        ),
                      _stepTitle(),
                      const SizedBox(height: 8),
                      _stepSubtitle(),
                      const SizedBox(height: 32),
                      if (_error != null) ...[
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(color: const Color(0xFFFFF1F2), borderRadius: BorderRadius.circular(12)),
                          child: Text(_error!, style: const TextStyle(color: AppTheme.error, fontSize: 14)),
                        ),
                        const SizedBox(height: 16),
                      ],
                      _stepField(),
                      const SizedBox(height: 24),
                      _stepButton(),
                      if (_step != 2) ...[
                        const SizedBox(height: 16),
                        TextButton(
                          onPressed: () => Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen())),
                          child: const Text('Kembali ke login'),
                        ),
                      ],
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

  Widget _stepTitle() {
    switch (_step) {
      case 0:
        return Text('Lupa Password?', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold), textAlign: TextAlign.center);
      case 1:
        return Text('Masukkan Kode', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold), textAlign: TextAlign.center);
      default:
        return Text('Buat Password Baru', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold), textAlign: TextAlign.center);
    }
  }

  Widget _stepSubtitle() {
    switch (_step) {
      case 0:
        return Text('Masukkan email Anda dan kami akan kirim kode verifikasi.', textAlign: TextAlign.center, style: TextStyle(color: AppTheme.txtSec));
      case 1:
        return Text('Kode 6 digit telah dikirim ke $_email.', textAlign: TextAlign.center, style: TextStyle(color: AppTheme.txtSec));
      default:
        return Text('Buat password baru minimal 8 karakter.', textAlign: TextAlign.center, style: TextStyle(color: AppTheme.txtSec));
    }
  }

  Widget _stepField() {
    switch (_step) {
      case 0:
        return TextFormField(
          controller: _emailController,
          keyboardType: TextInputType.emailAddress,
          decoration: const InputDecoration(
            labelText: 'Email',
            prefixIcon: Icon(Icons.email_outlined),
            hintText: 'nama@email.com',
          ),
        );
      case 1:
        return TextFormField(
          controller: _otpController,
          keyboardType: TextInputType.number,
          maxLength: 6,
          inputFormatters: [FilteringTextInputFormatter.digitsOnly],
          decoration: const InputDecoration(
            labelText: 'Kode Verifikasi',
            prefixIcon: Icon(Icons.pin_outlined),
            hintText: '123456',
          ),
        );
      default:
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextFormField(
              controller: _passwordController,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'Password Baru',
                prefixIcon: Icon(Icons.lock_outline),
              ),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _confirmController,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'Ulangi Password',
                prefixIcon: Icon(Icons.lock_outline),
              ),
            ),
          ],
        );
    }
  }

  Widget _stepButton() {
    final String label;
    switch (_step) {
      case 0:
        label = 'Kirim Kode';
      case 1:
        label = 'Lanjut';
      default:
        label = 'Simpan Password';
    }
    return ElevatedButton(
      onPressed: _isLoading ? null : (_step == 0 ? _sendOtp : _nextStep),
      style: ElevatedButton.styleFrom(
        backgroundColor: AppTheme.primary,
        padding: const EdgeInsets.symmetric(vertical: 16),
      ),
      child: _isLoading
          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
          : Text(label, style: const TextStyle(fontSize: 16)),
    );
  }
}
