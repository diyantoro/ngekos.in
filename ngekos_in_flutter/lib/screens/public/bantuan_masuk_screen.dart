import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/bantuan.dart';
import '../../services/bantuan_service.dart';
import '../../widgets/status_badge.dart';

class BantuanMasukScreen extends StatefulWidget {
  const BantuanMasukScreen({super.key});

  @override
  State<BantuanMasukScreen> createState() => _BantuanMasukScreenState();
}

class _BantuanMasukScreenState extends State<BantuanMasukScreen> {
  List<BantuanMessage> _messages = [];
  bool _isLoading = true;
  String _tab = 'baru';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final data = await BantuanService.getMasuk();
      setState(() { _messages = data; _isLoading = false; });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  List<BantuanMessage> get _filteredMessages {
    return _messages.where((m) => m.status == _tab).toList();
  }

  Future<void> _balas(int id) async {
    final controller = TextEditingController();
    final result = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Balas Pesan'),
        content: TextField(controller: controller, maxLines: 3, decoration: const InputDecoration(hintText: 'Tulis balasan...')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, controller.text),
            child: const Text('Kirim', style: TextStyle(color: AppTheme.primary)),
          ),
        ],
      ),
    );
    if (result != null && result.isNotEmpty) {
      try {
        await BantuanService.balasBantuan(id, result);
        _load();
      } catch (e) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(title: const Text('Pesan Masuk', style: TextStyle(fontWeight: FontWeight.bold))),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : Column(
              children: [
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                  child: Row(
                    children: ['baru', 'dibaca', 'selesai'].map((tab) {
                      final isSelected = _tab == tab;
                      final count = _messages.where((m) => m.status == tab).length;
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: GestureDetector(
                          onTap: () => setState(() => _tab = tab),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            decoration: BoxDecoration(
                              color: isSelected ? AppTheme.primary : AppTheme.surfaceGray,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              '${tab[0].toUpperCase()}${tab.substring(1)} ($count)',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: isSelected ? Colors.white : AppTheme.textSecondary),
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
                Expanded(
                  child: _filteredMessages.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.inbox_rounded, size: 64, color: Colors.grey[300]),
                              const SizedBox(height: 12),
                              const Text('Tidak ada pesan', style: TextStyle(color: AppTheme.textSecondary)),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _load,
                          child: ListView.separated(
                            padding: const EdgeInsets.all(16),
                            itemCount: _filteredMessages.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 8),
                            itemBuilder: (context, index) => _buildCard(_filteredMessages[index]),
                          ),
                        ),
                ),
              ],
            ),
    );
  }

  Widget _buildCard(BantuanMessage msg) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppTheme.borderLight)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 16,
                backgroundColor: AppTheme.primary.withValues(alpha: 0.1),
                child: Text(msg.nama.isNotEmpty ? msg.nama[0].toUpperCase() : '?', style: const TextStyle(color: AppTheme.primary, fontWeight: FontWeight.bold, fontSize: 12)),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(msg.nama, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                    Text(msg.email, style: const TextStyle(fontSize: 11, color: AppTheme.textMuted)),
                  ],
                ),
              ),
              StatusBadge(status: msg.status),
            ],
          ),
          if (msg.subjek != null && msg.subjek!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(msg.subjek!, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
          ],
          const SizedBox(height: 6),
          Text(msg.pesan, style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary, height: 1.5)),
          if (msg.balasan != null) ...[
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: const Color(0xFFF0FDFA), borderRadius: BorderRadius.circular(8)),
              child: Text('Balasan: ${msg.balasan}', style: const TextStyle(fontSize: 12, color: AppTheme.primary)),
            ),
          ],
          if (msg.status != 'selesai') ...[
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                if (msg.status != 'dibaca')
                  TextButton(
                    onPressed: () async {
                      try {
                        await BantuanService.tandaiDibaca(msg.id);
                        _load();
                      } catch (_) {}
                    },
                    child: const Text('Tandai Dibaca', style: TextStyle(fontSize: 12)),
                  ),
                TextButton(
                  onPressed: () => _balas(msg.id),
                  child: const Text('Balas', style: TextStyle(fontSize: 12, color: AppTheme.primary)),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}
