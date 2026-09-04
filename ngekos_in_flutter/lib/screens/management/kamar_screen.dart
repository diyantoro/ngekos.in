import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/kamar.dart';
import '../../services/properti_manage_service.dart';
import '../../widgets/status_badge.dart';

class KamarScreen extends StatefulWidget {
  final int propertiId;
  final String propertiNama;
  const KamarScreen({super.key, required this.propertiId, required this.propertiNama});

  @override
  State<KamarScreen> createState() => _KamarScreenState();
}

class _KamarScreenState extends State<KamarScreen> {
  List<Kamar> _kamars = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final data = await PropertiManageService.getKamars(widget.propertiId);
      setState(() { _kamars = data; _isLoading = false; });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  void _showForm({Kamar? kamar}) {
    final namaController = TextEditingController(text: kamar?.nama ?? '');
    final kapasitasController = TextEditingController(text: '${kamar?.kapasitas ?? 1}');
    final hargaController = TextEditingController(text: '${kamar?.hargaSewaBulanan ?? 0}');
    final isEdit = kamar != null;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(ctx).viewInsets.bottom + 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(child: Container(width: 40, height: 4, decoration: BoxDecoration(color: AppTheme.border, borderRadius: BorderRadius.circular(2)))),
            const SizedBox(height: 16),
            Text(isEdit ? 'Ubah Kamar' : 'Tambah Kamar', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            TextField(controller: namaController, decoration: const InputDecoration(labelText: 'Nama Kamar', hintText: 'Contoh: Kamar A1')),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(child: TextField(controller: kapasitasController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Kapasitas'))),
                const SizedBox(width: 12),
                Expanded(child: TextField(controller: hargaController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Harga/Bulan (Rp)'))),
              ],
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () async {
                      Navigator.pop(ctx);
                      try {
                        if (isEdit) {
                          await PropertiManageService.updateKamar(
                            widget.propertiId, kamar.id,
                            nama: namaController.text.trim(),
                            kapasitas: int.tryParse(kapasitasController.text) ?? 1,
                            hargaSewaBulanan: int.tryParse(hargaController.text) ?? 0,
                            status: kamar.status,
                          );
                        } else {
                          await PropertiManageService.createKamar(
                            widget.propertiId,
                            nama: namaController.text.trim(),
                            kapasitas: int.tryParse(kapasitasController.text) ?? 1,
                            hargaSewaBulanan: int.tryParse(hargaController.text) ?? 0,
                          );
                        }
                        if (mounted) _load();
                      } catch (e) {
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
                        }
                      }
                    },
                    child: Text(isEdit ? 'Simpan' : 'Tambah'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Kamar - ${widget.propertiNama}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        actions: [
          IconButton(icon: const Icon(Icons.add_rounded), onPressed: () => _showForm()),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          : _kamars.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.king_bed_rounded, size: 64, color: Colors.grey[300]),
                      const SizedBox(height: 12),
                      const Text('Belum ada kamar', style: TextStyle(color: AppTheme.textSecondary)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _kamars.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      final kamar = _kamars[index];
                      return Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppTheme.borderLight)),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(kamar.nama, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                  const SizedBox(height: 4),
                                  Text('${kamar.kapasitas} orang Â· ${kamar.formattedHarga}', style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
                                ],
                              ),
                            ),
                            StatusBadge(status: kamar.status),
                            const SizedBox(width: 8),
                            PopupMenuButton(
                              itemBuilder: (_) => [
                                PopupMenuItem(child: const Text('Ubah'), onTap: () => _showForm(kamar: kamar)),
                                PopupMenuItem(
                                  child: const Text('Hapus', style: TextStyle(color: AppTheme.error)),
                                  onTap: () async {
                                    try {
                                      await PropertiManageService.deleteKamar(widget.propertiId, kamar.id);
                                      if (mounted) _load();
                                    } catch (e) {
                                      if (context.mounted) {
                                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
                                      }
                                    }
                                  },
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
