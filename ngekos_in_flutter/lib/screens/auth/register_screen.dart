import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../src/platform_file.dart';
import '../../widgets/auth_scaffold.dart';
import '../main_screen.dart';
import 'login_screen.dart';

class RegisterScreen extends StatefulWidget {
  final String peran;
  const RegisterScreen({super.key, required this.peran});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _namaController = TextEditingController();
  final _emailController = TextEditingController();
  final _noHpController = TextEditingController();
  final _passwordController = TextEditingController();
  final _passwordConfirmController = TextEditingController();
  late String _peran;
  bool _obscurePassword = true;
  bool _obscureConfirm = true;
  PlatformFile? _avatar;

  @override
  void initState() {
    super.initState();
    _peran = widget.peran;
  }

  @override
  void dispose() {
    _namaController.dispose();
    _emailController.dispose();
    _noHpController.dispose();
    _passwordController.dispose();
    _passwordConfirmController.dispose();
    super.dispose();
  }

  Future<void> _pickAvatar() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery, maxWidth: 800, maxHeight: 800, imageQuality: 85);
    if (picked != null) {
      final file = await PlatformFile.fromXFile(picked);
      if (mounted) setState(() => _avatar = file);
    }
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    final auth = context.read<AuthProvider>();
    final success = await auth.register(
      nama: _namaController.text.trim(),
      email: _emailController.text.trim(),
      noHp: _noHpController.text.trim().isNotEmpty ? _noHpController.text.trim() : null,
      peran: _peran,
      password: _passwordController.text,
      passwordConfirmation: _passwordConfirmController.text,
      avatar: _avatar,
    );

    if (success && mounted) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const MainScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isPemilik = _peran == 'pemilik';
    final roleColor = isPemilik ? AppTheme.accent : AppTheme.primary;
    final selectedBorder = isPemilik ? AppTheme.accent : AppTheme.primary;
    final selectedBg = isPemilik ? const Color(0xFFECFDF5) : const Color(0xFFF0FDFA);

    return AuthScaffold(
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const SizedBox(height: 8),
            const Text(
              'Daftar Akun Baru',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 4),
            const Text(
              'Bergabung dan mulai cari kos impianmu.',
              style: TextStyle(fontSize: 14, color: AppTheme.textSecondary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            // Avatar upload
            Center(
              child: GestureDetector(
                onTap: _pickAvatar,
                child: Container(
                  height: 80,
                  width: 80,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: AppTheme.heroGradient,
                    boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 8, offset: Offset(0, 4))],
                    border: Border.all(color: Colors.white, width: 3),
                  ),
                  child: _avatar != null
                      ? ClipOval(child: Image.memory(_avatar!.bytes, fit: BoxFit.cover))
                      : const Icon(Icons.person_rounded, color: Colors.white, size: 40),
                ),
              ),
            ),
            const SizedBox(height: 6),
            const Text('Foto profil (opsional)', style: TextStyle(fontSize: 12, color: AppTheme.textMuted), textAlign: TextAlign.center),
            const SizedBox(height: 20),
            TextFormField(
              controller: _namaController,
              decoration: const InputDecoration(
                labelText: 'Nama Lengkap',
                hintText: 'Nama lengkap Anda',
                prefixIcon: Icon(Icons.person_outline),
              ),
              validator: (v) => v == null || v.isEmpty ? 'Nama wajib diisi' : null,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(
                labelText: 'Email',
                hintText: 'nama@email.com',
                prefixIcon: Icon(Icons.email_outlined),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Email wajib diisi';
                if (!v.contains('@')) return 'Email tidak valid';
                return null;
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _noHpController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                labelText: 'No. HP',
                hintText: '08xxxxxxxxxx',
                prefixIcon: Icon(Icons.phone_outlined),
              ),
            ),
            const SizedBox(height: 16),
            Text('Daftar Sebagai', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500, color: AppTheme.textPrimary)),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: _RoleCard(
                    imageAsset: 'assets/images/login-tenant.svg',
                    title: 'Pencari Kos',
                    subtitle: 'Cari & tanya kamar',
                    selected: _peran == 'anak_kos',
                    borderColor: AppTheme.primary,
                    selectedBorder: selectedBorder,
                    selectedBg: selectedBg,
                    roleColor: roleColor,
                    onTap: () => setState(() => _peran = 'anak_kos'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _RoleCard(
                    imageAsset: 'assets/images/login-owner.svg',
                    title: 'Pemilik Kos',
                    subtitle: 'Promosikan kos',
                    selected: _peran == 'pemilik',
                    borderColor: AppTheme.accent,
                    selectedBorder: selectedBorder,
                    selectedBg: selectedBg,
                    roleColor: roleColor,
                    onTap: () => setState(() => _peran = 'pemilik'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _passwordController,
              obscureText: _obscurePassword,
              decoration: InputDecoration(
                labelText: 'Password',
                hintText: 'Minimal 8 karakter',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility),
                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                ),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Password wajib diisi';
                if (v.length < 8) return 'Password minimal 8 karakter';
                return null;
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _passwordConfirmController,
              obscureText: _obscureConfirm,
              decoration: InputDecoration(
                labelText: 'Konfirmasi Password',
                hintText: 'Ulangi password',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  icon: Icon(_obscureConfirm ? Icons.visibility_off : Icons.visibility),
                  onPressed: () => setState(() => _obscureConfirm = !_obscureConfirm),
                ),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Konfirmasi password wajib diisi';
                if (v != _passwordController.text) return 'Password tidak cocok';
                return null;
              },
            ),
            const SizedBox(height: 8),
            Consumer<AuthProvider>(
              builder: (context, auth, _) {
                if (auth.error != null) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Text(auth.error!, style: const TextStyle(color: AppTheme.error, fontSize: 14)),
                  );
                }
                return const SizedBox.shrink();
              },
            ),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: Consumer<AuthProvider>(
                builder: (context, auth, _) {
                  return ElevatedButton(
                    onPressed: auth.isLoading ? null : _handleRegister,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primary,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    child: auth.isLoading
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Daftar', style: TextStyle(fontSize: 16)),
                  );
                },
              ),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                GestureDetector(
                  onTap: () {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(builder: (_) => LoginScreen(peran: _peran)),
                    );
                  },
                  child: Text('Sudah punya akun?', style: TextStyle(fontSize: 14, color: Colors.grey[600], decoration: TextDecoration.underline)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _RoleCard extends StatelessWidget {
  final String imageAsset;
  final String title;
  final String subtitle;
  final bool selected;
  final Color borderColor;
  final Color selectedBorder;
  final Color selectedBg;
  final Color roleColor;
  final VoidCallback onTap;

  const _RoleCard({
    required this.imageAsset,
    required this.title,
    required this.subtitle,
    required this.selected,
    required this.borderColor,
    required this.selectedBorder,
    required this.selectedBg,
    required this.roleColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Stack(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: selected ? selectedBg : Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: selected ? selectedBorder : const Color(0xFFE5E7EB), width: 2),
              boxShadow: selected ? [BoxShadow(color: selectedBorder.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 4))] : null,
            ),
            child: Column(
              children: [
                SvgPicture.asset(imageAsset, height: 64, width: 64),
                const SizedBox(height: 8),
                Text(title, style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: selected ? roleColor : AppTheme.textPrimary)),
                const SizedBox(height: 2),
                Text(subtitle, style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
              ],
            ),
          ),
          if (selected)
            Positioned(
              top: -8,
              right: -8,
              child: Container(
                width: 24,
                height: 24,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: selectedBorder,
                  border: Border.all(color: Colors.white, width: 2),
                ),
                child: const Icon(Icons.check_rounded, color: Colors.white, size: 14),
              ),
            ),
        ],
      ),
    );
  }
}
