import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/user_full.dart';
import '../../services/pengguna_service.dart';
import '../../widgets/user_avatar.dart';
import '../../widgets/status_badge.dart';

class PenggunaScreen extends StatefulWidget {
  const PenggunaScreen({super.key});

  @override
  State<PenggunaScreen> createState() => _PenggunaScreenState();
}

class _PenggunaScreenState extends State<PenggunaScreen> {
  List<UserFull> _users = [];
  bool _isLoading = true;
  String? _search;
  String? _peran;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final data = await PenggunaService.getPengguna(search: _search, peran: _peran);
      setState(() { _users = data; _isLoading = false; });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Kelola Pengguna', style: TextStyle(fontWeight: FontWeight.bold))),
      body: Column(
        children: [
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    decoration: InputDecoration(
                      hintText: 'Cari pengguna...',
                      prefixIcon: const Icon(Icons.search_rounded, size: 20),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                      filled: true,
                      fillColor: AppTheme.surfaceGray,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      isDense: true,
                    ),
                    onSubmitted: (v) { _search = v; _load(); },
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(color: AppTheme.surfaceGray, borderRadius: BorderRadius.circular(10)),
                  child: DropdownButton<String>(
                    value: _peran,
                    underline: const SizedBox(),
                    hint: const Text('Semua', style: TextStyle(fontSize: 13)),
                    isDense: true,
                    items: const [
                      DropdownMenuItem(value: null, child: Text('Semua')),
                      DropdownMenuItem(value: 'anak_kos', child: Text('Anak Kos')),
                      DropdownMenuItem(value: 'pemilik', child: Text('Pemilik')),
                      DropdownMenuItem(value: 'admin', child: Text('Admin')),
                      DropdownMenuItem(value: 'super_admin', child: Text('Super Admin')),
                    ],
                    onChanged: (v) { setState(() => _peran = v); _load(); },
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
                : RefreshIndicator(
                    onRefresh: _load,
                    child: ListView.separated(
                      padding: const EdgeInsets.all(16),
                      itemCount: _users.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 8),
                      itemBuilder: (context, index) => _buildUserCard(_users[index]),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildUserCard(UserFull user) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppTheme.borderLight)),
      child: Row(
        children: [
          UserAvatar(avatar: user.avatar, inisial: user.inisial, nama: user.nama, size: AvatarSize.md),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(user.nama, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                Text(user.email, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                const SizedBox(height: 4),
                Row(
                  children: [
                    StatusBadge(status: user.aktif ? 'aktif' : 'nonaktif'),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(color: AppTheme.surfaceGray, borderRadius: BorderRadius.circular(4)),
                      child: Text(user.roleLabel, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppTheme.textSecondary)),
                    ),
                  ],
                ),
              ],
            ),
          ),
          PopupMenuButton(
            itemBuilder: (_) => [
              PopupMenuItem(
                child: Text(user.aktif ? 'Nonaktifkan' : 'Aktifkan'),
                onTap: () async {
                  await PenggunaService.toggleStatus(user.id);
                  _load();
                },
              ),
            ],
          ),
        ],
      ),
    );
  }
}
