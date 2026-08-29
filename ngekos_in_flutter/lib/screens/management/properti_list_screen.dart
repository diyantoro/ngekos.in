import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../models/properti.dart';
import '../../services/properti_manage_service.dart';
import '../../widgets/status_badge.dart';
import 'properti_form_screen.dart';
import 'kamar_screen.dart';

class PropertiListScreen extends StatefulWidget {
  const PropertiListScreen({super.key, this.isActive = false});

  final bool isActive;

  @override
  State<PropertiListScreen> createState() => _PropertiListScreenState();
}

class _PropertiListScreenState extends State<PropertiListScreen> {
  List<Properti> _propertis = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    if (widget.isActive) _load();
  }

  @override
  void didUpdateWidget(covariant PropertiListScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.isActive && !oldWidget.isActive) {
      _load();
    }
  }

  Future<void> _load() async {
    try {
      final data = await PropertiManageService.getPropertiList();
      setState(() { _propertis = data; _isLoading = false; });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _delete(int id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Properti?'),
        content: const Text('Properti yang dihapus tidak dapat dikembalikan.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Hapus', style: TextStyle(color: AppTheme.error))),
        ],
      ),
    );
    if (confirm == true) {
      try {
        await PropertiManageService.deleteProperti(id);
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
      appBar: AppBar(
        title: const Text('Kelola Properti', style: TextStyle(fontWeight: FontWeight.bold)),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_rounded),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PropertiFormScreen())).then((_) => _load()),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _propertis.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.apartment_rounded, size: 64, color: Colors.grey[300]),
                      const SizedBox(height: 16),
                      const Text('Belum ada properti', style: TextStyle(color: AppTheme.textSecondary)),
                      const SizedBox(height: 12),
                      ElevatedButton.icon(
                        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PropertiFormScreen())).then((_) => _load()),
                        icon: const Icon(Icons.add_rounded),
                        label: const Text('Tambah Properti'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _propertis.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 10),
                    itemBuilder: (context, index) => _buildCard(_propertis[index]),
                  ),
                ),
    );
  }

  Widget _buildCard(Properti properti) {
    return Container(
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppTheme.borderLight)),
      child: Column(
        children: [
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            child: properti.foto != null
                ? CachedNetworkImage(
                    imageUrl: properti.foto!,
                    height: 140,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorWidget: (_, __, ___) => Container(height: 140, color: AppTheme.surfaceGray, child: const Icon(Icons.home_rounded, size: 40, color: AppTheme.primary)),
                  )
                : Container(
                    height: 140,
                    color: const Color(0xFFCCFBF1),
                    child: const Center(child: Icon(Icons.home_rounded, size: 40, color: AppTheme.primary)),
                  ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(child: Text(properti.nama, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16))),
                    StatusBadge(status: properti.status),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.location_on_rounded, size: 14, color: AppTheme.textSecondary),
                    const SizedBox(width: 4),
                    Expanded(child: Text('${properti.alamat}, ${properti.kota}', style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary), maxLines: 1, overflow: TextOverflow.ellipsis)),
                  ],
                ),
                const SizedBox(height: 8),
                Text('${properti.kamarTersedia} kamar tersedia / ${properti.totalKamar} total', style: const TextStyle(fontSize: 12, color: AppTheme.textMuted)),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => KamarScreen(propertiId: properti.id, propertiNama: properti.nama))),
                        icon: const Icon(Icons.king_bed_rounded, size: 16),
                        label: const Text('Kamar', style: TextStyle(fontSize: 12)),
                        style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 8)),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PropertiFormScreen(properti: properti))).then((_) => _load()),
                        icon: const Icon(Icons.edit_rounded, size: 16),
                        label: const Text('Ubah', style: TextStyle(fontSize: 12)),
                        style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 8)),
                      ),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      onPressed: () => _delete(properti.id),
                      icon: const Icon(Icons.delete_outline_rounded, color: AppTheme.error, size: 20),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
