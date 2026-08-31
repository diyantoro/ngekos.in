import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/bantuan.dart';
import '../../providers/auth_provider.dart';
import '../../services/bantuan_service.dart';
import '../../widgets/status_badge.dart';
import 'bantuan_screen.dart';

class BantuanRiwayatScreen extends StatefulWidget {
  const BantuanRiwayatScreen({super.key});

  @override
  State<BantuanRiwayatScreen> createState() => _BantuanRiwayatScreenState();
}

class _BantuanRiwayatScreenState extends State<BantuanRiwayatScreen> {
  List<BantuanMessage> _messages = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final data = await BantuanService.getRiwayat();
      if (!mounted) return;
      setState(() {
        _messages = data;
        _isLoading = false;
      });
      context.read<AuthProvider>().refreshUser();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _error = e.toString();
      });
    }
  }

  void _openKirimPesan() {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const BantuanScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(title: const Text('Riwayat Bantuan', style: TextStyle(fontWeight: FontWeight.bold))),
      floatingActionButton: _messages.isNotEmpty
          ? FloatingActionButton.extended(
              onPressed: _openKirimPesan,
              backgroundColor: AppTheme.primary,
              icon: const Icon(Icons.add_rounded, color: Colors.white),
              label: const Text('Kirim Pesan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
            )
          : null,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _error != null
              ? _buildError()
              : _messages.isEmpty
                  ? _buildEmpty()
                  : RefreshIndicator(
                      onRefresh: _load,
                      child: ListView.separated(
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 80),
                        itemCount: _messages.length,
                        separatorBuilder: (_, _) => const SizedBox(height: 8),
                        itemBuilder: (context, index) => _buildMessageCard(_messages[index]),
                      ),
                    ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.help_outline_rounded, size: 64, color: Colors.grey[300]),
            const SizedBox(height: 16),
            const Text('Belum ada riwayat bantuan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: AppTheme.textSecondary)),
            const SizedBox(height: 8),
            const Text('Kirim pesan ke admin untuk mendapatkan bantuan.', style: TextStyle(fontSize: 13, color: AppTheme.textMuted)),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _openKirimPesan,
              icon: const Icon(Icons.chat_rounded, size: 18),
              label: const Text('Kirim Pesan ke Admin'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.wifi_off_rounded, size: 56, color: Colors.grey[400]),
            const SizedBox(height: 16),
            const Text('Gagal memuat riwayat bantuan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text(
              'Tidak dapat terhubung ke server. Periksa koneksi internetmu, lalu coba lagi.',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary),
            ),
            if (_error != null && _error!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(_error!, textAlign: TextAlign.center, style: const TextStyle(fontSize: 12, color: AppTheme.textMuted)),
            ],
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _load,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMessageCard(BantuanMessage msg) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  msg.subjek ?? 'Pesan Bantuan',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
              ),
              StatusBadge(status: msg.status),
            ],
          ),
          const SizedBox(height: 4),
          if (msg.createdAt != null)
            Text(
              DateFormat('d MMM yyyy, HH:mm', 'id').format(msg.createdAt!),
              style: const TextStyle(fontSize: 11, color: AppTheme.textMuted),
            ),
          const SizedBox(height: 8),
          Text(msg.pesan, style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary, height: 1.5)),
          if (msg.balasan != null) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: const Color(0xFFF0FDFA), borderRadius: BorderRadius.circular(8)),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Balasan Admin — ${msg.dibalasOleh ?? 'Ngekos.in Admin'}',
                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primary),
                  ),
                  if (msg.dibalasAt != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      DateFormat('d MMM yyyy, HH:mm', 'id').format(msg.dibalasAt!),
                      style: const TextStyle(fontSize: 11, color: AppTheme.textMuted),
                    ),
                  ],
                  const SizedBox(height: 6),
                  Text(msg.balasan!, style: const TextStyle(fontSize: 13, color: AppTheme.textPrimary, height: 1.5)),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
