import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../models/tagihan.dart';
import '../../models/penyewaan.dart';
import '../../models/pembayaran.dart';
import '../../services/dashboard_service.dart';
import '../../src/platform_file.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/greeting_banner.dart';
import '../../widgets/tab_bar_widget.dart';
import '../../widgets/chatbot_widget.dart';
import '../../widgets/promo_ads_banner.dart';
import '../../widgets/trending_kos_section.dart';

class AnakKosDashboardScreen extends StatefulWidget {
  const AnakKosDashboardScreen({super.key, this.isActive = false});

  final bool isActive;

  @override
  State<AnakKosDashboardScreen> createState() => _AnakKosDashboardScreenState();
}

class _AnakKosDashboardScreenState extends State<AnakKosDashboardScreen> {
  Map<String, dynamic>? _dashboard;
  List<Penyewaan> _penyewaans = [];
  List<Tagihan> _tagihans = [];
  List<Pembayaran> _pembayarans = [];
  bool _isLoading = true;
  int _tab = 0;

  @override
  void initState() {
    super.initState();
    if (widget.isActive) _load();
  }

  @override
  void didUpdateWidget(covariant AnakKosDashboardScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.isActive && !oldWidget.isActive) {
      _load();
    }
  }

  Future<void> _load() async {
    try {
      final d = await DashboardService.getAnakKosDashboard();
      final s = await DashboardService.getPenyewaan();
      final t = await DashboardService.getTagihan();
      final p = await DashboardService.getPembayaran();
      if (mounted) {
        setState(() {
          _dashboard = d;
          _penyewaans = s;
          _tagihans = t;
          _pembayarans = p;
          _isLoading = false;
        });
      }    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final hour = DateTime.now().hour;
    final greeting = hour < 12 ? 'Selamat Pagi' : hour < 18 ? 'Selamat Siang' : 'Selamat Malam';

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: _load,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    GreetingBanner(
                      roleLabel: 'Anak Kos',
                      description: '$greeting, ${auth.user?.nama ?? 'User'}',
                      icon: const Icon(Icons.person_rounded, color: Colors.white, size: 20),
                    ),
                    const SizedBox(height: 16),
                    const PromoAdsBanner(),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: StatCard(label: 'Penyewaan Aktif', value: '${_dashboard?['penyewaan_aktif'] ?? 0}', icon: const Icon(Icons.key_rounded), tone: 'emerald')),
                        const SizedBox(width: 12),
                        Expanded(child: StatCard(label: 'Tagihan', value: '${_dashboard?['tagihan_belum_bayar'] ?? 0}', icon: const Icon(Icons.receipt_long_rounded), tone: 'amber')),
                      ],
                    ),
                    const SizedBox(height: 12),
                    StatCard(
                      label: 'Total Dibayar',
                      value: AppTheme.formatRupiah(_dashboard?['total_dibayar'] ?? 0),
                      icon: const Icon(Icons.payment_rounded),
                      tone: 'cyan',
                    ),
                    const SizedBox(height: 20),
                    const TrendingKosSection(),
                    const SizedBox(height: 20),
                    TabBarWidget(
                      tabs: const ['Sewa Saya', 'Tagihan', 'Pembayaran'],
                      selectedIndex: _tab,
                      onTabChanged: (i) => setState(() => _tab = i),
                    ),
                    const SizedBox(height: 16),
                    if (_tab == 0) _buildSewaTab(),
                    if (_tab == 1) _buildTagihanTab(),
                    if (_tab == 2) _buildPembayaranTab(),
                  ],
                ),
              ),
      ),
      floatingActionButton: const ChatbotWidget(),
    );
  }

  Widget _buildSewaTab() {
    if (_penyewaans.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Column(
          children: [
            Icon(Icons.home_work_outlined, size: 48, color: Color(0xFFD1D5DB)),
            SizedBox(height: 12),
            Text('Belum ada penyewaan aktif', style: TextStyle(color: AppTheme.txtSec)),
          ],
        ),
      );
    }
    return Column(
      children: _penyewaans.map((s) {
        final isAktif = s.status == 'aktif';
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          decoration: BoxDecoration(
            color: AppTheme.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: isAktif ? const Color(0xFF99F6E4) : AppTheme.bdrLight),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        gradient: AppTheme.heroGradient,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: s.propertiFoto != null
                          ? ClipRRect(
                              borderRadius: BorderRadius.circular(12),
                              child: Image.network(
                                s.propertiFoto!,
                                width: 56,
                                height: 56,
                                fit: BoxFit.cover,
                                errorBuilder: (_, _, _) => const Icon(Icons.apartment_rounded, color: Colors.white, size: 28),
                              ),
                            )
                          : const Icon(Icons.apartment_rounded, color: Colors.white, size: 28),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Kamar ${s.kamar ?? "-"} Â· ${s.properti ?? ""}',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppTheme.txt),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Masuk: ${s.tanggalMasuk != null ? DateFormat('d MMM yyyy').format(s.tanggalMasuk!) : '-'}'
                            '${s.tanggalKeluar != null ? ' Â· Keluar: ${DateFormat('d MMM yyyy').format(s.tanggalKeluar!)}' : ''}',
                            style: TextStyle(fontSize: 12, color: AppTheme.txtSec),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    StatusBadge(status: s.status),
                  ],
                ),
              ),
              if (isAktif)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    border: Border(top: BorderSide(color: AppTheme.bdrLight)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      OutlinedButton(
                        onPressed: () => _ajukanKeluar(s),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.rose,
                          side: BorderSide(color: AppTheme.rose.withValues(alpha: 0.4)),
                          backgroundColor: const Color(0xFFFFF1F2),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        child: const Text('Ajukan Check-out', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                      ),
                    ],
                  ),
                ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildTagihanTab() {
    if (_tagihans.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Column(
          children: [
            Icon(Icons.receipt_long_outlined, size: 48, color: Color(0xFFD1D5DB)),
            SizedBox(height: 12),
            Text('Tidak ada tagihan', style: TextStyle(color: AppTheme.txtSec)),
          ],
        ),
      );
    }
    return Column(
      children: _tagihans.map((t) {
        final total = t.jumlah + t.denda;
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppTheme.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppTheme.bdrLight),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(t.periode ?? '-', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppTheme.txt)),
                        ),
                        StatusBadge(status: t.status),
                      ],
                    ),
                    const SizedBox(height: 6),
                    if (t.kamar != null)
                      Text('Kamar ${t.kamar}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                    const SizedBox(height: 2),
                    if (t.jatuhTempo != null)
                      Text('Jatuh tempo: ${DateFormat('d MMM yyyy').format(t.jatuhTempo!)}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                    const SizedBox(height: 8),
                    Text(
                      AppTheme.formatRupiah(total),
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppTheme.txt),
                    ),
                    if (t.denda > 0)
                      Text('termasuk denda ${AppTheme.formatRupiah(t.denda)}', style: const TextStyle(fontSize: 11, color: AppTheme.rose)),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              if (t.status != 'lunas')
                ElevatedButton(
                  onPressed: () => _showBayarModal(t),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.accent,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: const Text('Bayar', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildPembayaranTab() {
    if (_pembayarans.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Column(
          children: [
            Icon(Icons.payments_outlined, size: 48, color: Color(0xFFD1D5DB)),
            SizedBox(height: 12),
            Text('Belum ada pembayaran', style: TextStyle(color: AppTheme.txtSec)),
          ],
        ),
      );
    }
    return Column(
      children: _pembayarans.map((p) => Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.bdrLight),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(p.periode ?? '-', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppTheme.txt)),
                ),
                StatusBadge(status: p.status),
              ],
            ),
            const SizedBox(height: 8),
            Text(p.formattedJumlah, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppTheme.txt)),
            const SizedBox(height: 4),
            Row(
              children: [
                if (p.metode != null)
                  Text('Metode: ${p.metode}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                if (p.metode != null && p.diverifikasiOleh != null)
                  Text('  ·  ', style: TextStyle(fontSize: 12, color: AppTheme.bdr)),
                if (p.diverifikasiOleh != null)
                  Text('Verifikasi: ${p.diverifikasiOleh}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
              ],
            ),
          ],
        ),
      )).toList(),
    );
  }

  Future<void> _ajukanKeluar(Penyewaan s) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Ajukan Check-out'),
        content: Text('Ajukan check-out dari kamar ${s.kamar ?? ""}? Check-out akan langsung diproses dan kamar kembali tersedia.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Ajukan', style: TextStyle(fontWeight: FontWeight.bold, color: AppTheme.rose)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    try {
      await DashboardService.ajukanKeluar(s.id);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Check-out berhasil. Kamar kembali tersedia.'), backgroundColor: AppTheme.success),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
      }
    }
  }

  void _showBayarModal(Tagihan tagihan) {
    final total = tagihan.jumlah + tagihan.denda;
    PlatformFile? bukti;
    var uploading = false;
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(ctx).viewInsets.bottom + 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(child: Container(width: 40, height: 4, decoration: BoxDecoration(color: AppTheme.bdr, borderRadius: BorderRadius.circular(2)))),
              const SizedBox(height: 16),
              Text('Bayar Tagihan ${tagihan.periode ?? ""}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text('Total: ${AppTheme.formatRupiah(total)}', style: TextStyle(fontSize: 14, color: AppTheme.txtSec)),
              const SizedBox(height: 16),
              Text(
                'Transfer tepat sesuai jumlah, lalu unggah bukti transfer (JPG/PNG/PDF, maks 2MB). Admin akan memverifikasi.',
                style: TextStyle(fontSize: 12, color: AppTheme.txtMuted),
              ),
              const SizedBox(height: 12),
              GestureDetector(
                onTap: () async {
                  final picked = await ImagePicker().pickImage(source: ImageSource.gallery);
                  if (picked != null) {
                    final file = await PlatformFile.fromXFile(picked);
                    setSheetState(() => bukti = file);
                  }
                },
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0FDFA),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppTheme.primary.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    children: [
                      Icon(bukti == null ? Icons.upload_file_rounded : Icons.check_circle_rounded, color: AppTheme.primary),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          bukti == null ? 'Pilih Bukti Transfer' : bukti!.name,
                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: bukti == null ? AppTheme.primary : AppTheme.txt),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: bukti == null || uploading
                    ? null
                    : () async {
                        setSheetState(() => uploading = true);
                        try {
                          await DashboardService.bayar(
                            tagihanId: tagihan.id,
                            metode: 'transfer',
                            jumlah: total,
                            bukti: bukti,
                          );
                          if (ctx.mounted) Navigator.pop(ctx);
                          _load();
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Pembayaran terkirim, menunggu verifikasi.'), backgroundColor: AppTheme.success),
                            );
                          }
                        } catch (e) {
                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
                          }
                          setSheetState(() => uploading = false);
                        }
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.accent,
                  minimumSize: const Size(double.infinity, 48),
                ),
                child: uploading
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Kirim Pembayaran', style: TextStyle(fontWeight: FontWeight.w600)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
