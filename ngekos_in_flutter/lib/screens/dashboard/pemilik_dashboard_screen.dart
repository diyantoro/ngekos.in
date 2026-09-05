import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../providers/auth_provider.dart';
import '../../services/dashboard_service.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/greeting_banner.dart';
import '../../widgets/tab_bar_widget.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/promo_ads_banner.dart';
import '../../widgets/trending_kos_section.dart';
import '../../widgets/dashboard_funnel.dart';
import '../../utils/csv_builder.dart';
import '../../utils/csv_saver.dart';

class PemilikDashboardScreen extends StatefulWidget {
  const PemilikDashboardScreen({super.key, this.isActive = false});

  final bool isActive;

  @override
  State<PemilikDashboardScreen> createState() => _PemilikDashboardScreenState();
}

class _PemilikDashboardScreenState extends State<PemilikDashboardScreen> {
  Map<String, dynamic>? _dashboard;
  List<dynamic> _propertis = [];
  List<dynamic> _sewaans = [];
  bool _isLoading = true;
  bool _isExporting = false;
  int _tab = 0;
  final TextEditingController _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    if (widget.isActive) _load();
  }

  @override
  void didUpdateWidget(covariant PemilikDashboardScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.isActive && !oldWidget.isActive) {
      _load();
    }
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final d = await DashboardService.getPemilikDashboard();
      final p = await DashboardService.getPemilikProperti();
      final s = await DashboardService.getPemilikSewaans();
      if (mounted) {
        setState(() {
          _dashboard = d;
          _propertis = p is List ? p : (p['data'] as List? ?? []);
          _sewaans = s;
          _isLoading = false;
        });
      }
    } catch (e) {
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
                      roleLabel: 'Pemilik Kos',
                      description: '$greeting, ${auth.user?.nama ?? 'User'}',
                      icon: const Icon(Icons.apartment_rounded, color: Colors.white, size: 20),
                    ),
                    const SizedBox(height: 16),
                    const PromoAdsBanner(),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: StatCard(label: 'Properti', value: '${_dashboard?['total_properti'] ?? 0}', icon: const Icon(Icons.apartment_rounded), tone: 'cyan')),
                        const SizedBox(width: 12),
                        Expanded(child: StatCard(label: 'Total Kamar', value: '${_dashboard?['total_kamar'] ?? 0}', icon: const Icon(Icons.king_bed_rounded), tone: 'sky')),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: StatCard(label: 'Kamar Terisi', value: '${_dashboard?['kamar_terisi'] ?? 0}', icon: const Icon(Icons.people_rounded), tone: 'emerald')),
                        const SizedBox(width: 12),
                        Expanded(child: StatCard(label: 'Pendapatan', value: AppTheme.formatRupiah(_dashboard?['pendapatan_bulan_ini'] ?? 0), icon: const Icon(Icons.payments_rounded), tone: 'amber')),
                      ],
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: _isExporting ? null : _pilihPeriodeDanEkspor,
                        icon: _isExporting
                            ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                            : const Icon(Icons.download_rounded),
                        label: Text(_isExporting ? 'Membuat rekap...' : 'Ekspor Rekap (CSV)'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.primary,
                          side: BorderSide(color: AppTheme.primary.withValues(alpha: 0.5)),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    _buildChartSection(),
                    const SizedBox(height: 20),
                    const TrendingKosSection(),
                    const SizedBox(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: TabBarWidget(
                            tabs: const ['Properti', 'Penyewaan'],
                            selectedIndex: _tab,
                            onTabChanged: (i) => setState(() => _tab = i),
                          ),
                        ),
                        const SizedBox(width: 8),
                        SizedBox(
                          width: 120,
                          child: TextField(
                            controller: _searchCtrl,
                            decoration: InputDecoration(
                              hintText: 'Cari...',
                              hintStyle: const TextStyle(fontSize: 12),
                              prefixIcon: const Icon(Icons.search_rounded, size: 18),
                              contentPadding: const EdgeInsets.symmetric(vertical: 0),
                              isDense: true,
                              filled: true,
                              fillColor: AppTheme.card,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppTheme.bdr)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: AppTheme.bdr)),
                            ),
                            onChanged: (_) => setState(() {}),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    if (_tab == 0) _buildPropertiTab(),
                    if (_tab == 1) _buildPenyewaanTab(),
                  ],
                ),
              ),
      ),
    );
  }

  Widget _buildPropertiTab() {
    final q = _searchCtrl.text.toLowerCase();
    final filtered = _propertis.where((p) => (p['nama'] ?? '').toString().toLowerCase().contains(q)).toList();
    if (filtered.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Center(child: Text('Belum ada properti', style: TextStyle(color: AppTheme.txtSec))),
      );
    }
    return Column(
      children: filtered.map((p) {
        final total = (p['total_kamar'] ?? 0) as int;
        final terisi = (p['kamar_terisi'] ?? 0) as int;
        final persentase = total > 0 ? (terisi / total * 100).round() : 0;
        final kamars = (p['kamars'] as List? ?? []);
        final fotoUrl = ApiConfig.resolveStorageUrl(p['foto']?.toString());
        return Container(
          clipBehavior: Clip.antiAlias,
          margin: const EdgeInsets.only(bottom: 12),
          decoration: BoxDecoration(
            color: AppTheme.surfaceGrey,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.bdrLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SizedBox(
                height: 140,
                width: double.infinity,
                child: fotoUrl != null
                    ? CachedNetworkImage(
                        imageUrl: fotoUrl,
                        imageRenderMethodForWeb: ImageRenderMethodForWeb.HttpGet,
                        fit: BoxFit.cover,
                        errorWidget: (_, _, _) => Container(
                          color: AppTheme.primary.withValues(alpha: 0.1),
                          child: const Center(child: Icon(Icons.home_rounded, size: 48, color: AppTheme.primary)),
                        ),
                      )
                    : Container(
                        color: AppTheme.primary.withValues(alpha: 0.1),
                        child: const Center(child: Icon(Icons.home_rounded, size: 48, color: AppTheme.primary)),
                      ),
              ),
              Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(p['nama'] ?? '-', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppTheme.txt)),
                        if (p['alamat'] != null)
                          Text(p['alamat'].toString(), style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                      ],
                    ),
                  ),
                  StatusBadge(status: p['status'] ?? ''),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(4),
                      child: LinearProgressIndicator(
                        value: persentase / 100,
                        backgroundColor: AppTheme.surfaceGrey,
                        valueColor: const AlwaysStoppedAnimation(Color(0xFF14B8A6)),
                        minHeight: 8,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text('$terisi/$total terisi', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.txtSec)),
                ],
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.card,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppTheme.bdrLight),
                ),
                child: Row(
                  children: [
                    Expanded(
                      flex: 2,
                      child: Text('Kamar', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.txtMuted, letterSpacing: 0.5)),
                    ),
                    Expanded(
                      flex: 1,
                      child: Text('Harga/Bln', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.txtMuted, letterSpacing: 0.5)),
                    ),
                    Expanded(
                      flex: 1,
                      child: Text('Status', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.txtMuted, letterSpacing: 0.5)),
                    ),
                  ],
                ),
              ),
...kamars.map((k) => Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                decoration: BoxDecoration(border: Border(bottom: BorderSide(color: AppTheme.bdrLight))),
                child: Row(
                  children: [
                    Expanded(
                      flex: 2,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(k['nama'] ?? '-', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.txt)),
                          Text('${k['kapasitas'] ?? 0} orang', style: TextStyle(fontSize: 11, color: AppTheme.txtSec)),
                        ],
                      ),
                    ),
                    Expanded(flex: 1, child: Text(AppTheme.formatRupiah(k['harga_sewa_bulanan'] ?? 0), style: TextStyle(fontSize: 12, color: AppTheme.txtSec))),
                    Expanded(flex: 1, child: Align(alignment: Alignment.centerLeft, child: StatusBadge(status: k['status'] ?? ''))),
                  ],
                ),
              )),
            ],
          ),
        ),
      ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildPenyewaanTab() {
    final q = _searchCtrl.text.toLowerCase();
    final filtered = _sewaans.where((s) => (s['anak_kos_nama'] ?? '').toString().toLowerCase().contains(q)).toList();
    if (filtered.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Center(child: Text('Belum ada data penyewaan', style: TextStyle(color: AppTheme.txtSec))),
      );
    }
    return Column(
      children: filtered.map((s) {
        final belumLunas = (s['tagihan_belum_bayar'] ?? 0) as int;
        final telat = (s['telat'] ?? 0) as int;
        final sisa = (s['sisa_tagihan'] ?? 0) as int;
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppTheme.card,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppTheme.bdrLight),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Text(s['anak_kos_nama'] ?? '-', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppTheme.txt), overflow: TextOverflow.ellipsis),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                          Text(
                            'Kamar ${s['kamar_nama'] ?? "-"}${(s['properti_nama'] ?? '') != '' ? ' · ${s['properti_nama']}' : ''}',
                            style: TextStyle(fontSize: 12, color: AppTheme.txtSec),
                          ),
                          if (s['tanggal_masuk'] != null)
                            Text('Masuk: ${DateFormat('d MMM yyyy').format(DateTime.parse(s['tanggal_masuk']))}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                      ],
                    ),
                  ),
                  StatusBadge(status: s['status'] ?? ''),
                ],
              ),
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: belumLunas > 0 ? const Color(0xFFFFF1F2) : const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Sisa tagihan: ${AppTheme.formatRupiah(sisa)}', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.txt)),
                          Text(
                            belumLunas == 0 ? 'Semua lunas' : '$belumLunas tagihan belum lunas${telat > 0 ? ' ($telat telat)' : ''}',
                            style: TextStyle(fontSize: 11, color: belumLunas > 0 ? AppTheme.rose : AppTheme.accent),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              if (s['tanggal_keluar'] != null)
                Padding(
                  padding: const EdgeInsets.only(top: 6),
                  child: Text('Keluar: ${DateFormat('d MMM yyyy').format(DateTime.parse(s['tanggal_keluar']))}', style: TextStyle(fontSize: 11, color: AppTheme.txtSec, fontStyle: FontStyle.italic)),
                ),
              if (s['status'] == 'aktif')
                Padding(
                  padding: const EdgeInsets.only(top: 10),
                  child: Align(
                    alignment: Alignment.centerRight,
                    child: OutlinedButton.icon(
                      onPressed: () => _checkOut(s),
                      icon: const Icon(Icons.logout_rounded, size: 16),
                      label: const Text('Check-out', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.rose,
                        side: BorderSide(color: AppTheme.rose.withValues(alpha: 0.4)),
                        backgroundColor: const Color(0xFFFFF1F2),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Future<void> _checkOut(Map<String, dynamic> s) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Check-out Penyewa'),
        content: Text('Check-out ${s['anak_kos_nama'] ?? ''} dari kamar ${s['kamar_nama'] ?? ''}? Kamar akan kembali tersedia.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Check-out', style: TextStyle(fontWeight: FontWeight.bold, color: AppTheme.rose)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    try {
      await DashboardService.checkout(s['id']);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Check-out berhasil. Kamar kembali tersedia.'), backgroundColor: AppTheme.success),
        );
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
      }
    }
  }

  Future<void> _pilihPeriodeDanEkspor() async {
    final now = DateTime.now();
    int bulan = now.month;
    int tahun = now.year;

    final terpilih = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        var pBulan = bulan;
        var pTahun = tahun;
        return StatefulBuilder(
          builder: (ctx, setSheet) => Padding(
            padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(ctx).viewInsets.bottom + 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(child: Container(width: 40, height: 4, decoration: BoxDecoration(color: AppTheme.bdr, borderRadius: BorderRadius.circular(2)))),
                const SizedBox(height: 16),
                const Text('Ekspor Rekap', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text('Pilih periode untuk rekap pendapatan & transaksi.', style: TextStyle(fontSize: 13, color: AppTheme.txtSec)),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<int>(
                        initialValue: pBulan,
                        decoration: const InputDecoration(labelText: 'Bulan', border: OutlineInputBorder()),
                        items: List.generate(12, (i) => i + 1)
                            .map((m) => DropdownMenuItem(value: m, child: Text(DateFormat.MMMM().format(DateTime(2000, m)))))
                            .toList(),
                        onChanged: (v) {
                          if (v != null) setSheet(() => pBulan = v);
                        },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<int>(
                        initialValue: pTahun,
                        decoration: const InputDecoration(labelText: 'Tahun', border: OutlineInputBorder()),
                        items: List.generate(now.year - 2019, (i) => now.year - i)
                            .map((t) => DropdownMenuItem(value: t, child: Text('$t')))
                            .toList(),
                        onChanged: (v) {
                          if (v != null) setSheet(() => pTahun = v);
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      bulan = pBulan;
                      tahun = pTahun;
                      Navigator.pop(ctx, true);
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary, minimumSize: const Size(double.infinity, 48)),
                    child: const Text('Ekspor CSV', style: TextStyle(fontWeight: FontWeight.w600)),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );

    if (terpilih != true) return;
    await _eksporRekap(bulan: bulan, tahun: tahun);
  }

  Future<void> _eksporRekap({required int bulan, required int tahun}) async {
    if (mounted) setState(() => _isExporting = true);
    try {
      final bulanStr = '$tahun-${bulan.toString().padLeft(2, '0')}';
      final data = await DashboardService.getPemilikRekap(bulan: bulanStr);
      final csv = _buatCsvRekap(data);
      final namaFile = 'rekap_ngekosin_$bulanStr.csv';
      await saveCsv(csv, namaFile);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Rekap ${data['periode'] ?? bulanStr} berhasil diekspor.'), backgroundColor: AppTheme.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal ekspor: $e'), backgroundColor: AppTheme.error));
      }
    } finally {
      if (mounted) setState(() => _isExporting = false);
    }
  }

  String _buatCsvRekap(Map<String, dynamic> data) {
    final rows = <List<String?>>[];
    final ringkasan = data['ringkasan'] as Map<String, dynamic>? ?? {};
    final propertis = (data['propertis'] as List? ?? []).whereType<Map<String, dynamic>>();
    final sewaans = (data['sewaans'] as List? ?? []).whereType<Map<String, dynamic>>();
    final transaksi = (data['transaksi'] as List? ?? []).whereType<Map<String, dynamic>>();

    rows.add([data['periode'] ?? 'Rekap']);
    rows.add(const [null]);
    rows.add(['RINGKASAN']);
    rows.add(['Total Properti', 'Total Kamar', 'Kamar Terisi', 'Penyewaan Aktif', 'Pendapatan', 'Jumlah Transaksi']);
    rows.add([
      '${ringkasan['total_properti'] ?? 0}',
      '${ringkasan['total_kamar'] ?? 0}',
      '${ringkasan['kamar_terisi'] ?? 0}',
      '${ringkasan['penyewaan_aktif'] ?? 0}',
      AppTheme.formatRupiah(ringkasan['pendapatan'] ?? 0),
      '${ringkasan['jumlah_transaksi'] ?? 0}',
    ]);

    rows.add(const [null]);
    rows.add(['PROPERTI']);
    rows.add(['Nama', 'Alamat', 'Status', 'Total Kamar', 'Kamar Terisi']);
    for (final p in propertis) {
      rows.add([p['nama']?.toString(), p['alamat']?.toString(), p['status']?.toString(), p['total_kamar']?.toString(), p['kamar_terisi']?.toString()]);
    }

    rows.add(const [null]);
    rows.add(['PENYEWAAN']);
    rows.add(['Penghuni', 'Kamar', 'Properti', 'Tanggal Masuk', 'Tanggal Keluar', 'Status']);
    for (final s in sewaans) {
      rows.add([
        s['anak_kos_nama']?.toString(),
        s['kamar_nama']?.toString(),
        s['properti_nama']?.toString(),
        s['tanggal_masuk']?.toString(),
        s['tanggal_keluar']?.toString(),
        s['status']?.toString(),
      ]);
    }

    rows.add(const [null]);
    rows.add(['TRANSAKSI']);
    rows.add(['Penghuni', 'Periode', 'Metode', 'Jumlah', 'Status', 'Tanggal Verifikasi']);
    for (final t in transaksi) {
      rows.add([
        t['anak_kos_nama']?.toString(),
        t['periode']?.toString(),
        t['metode']?.toString(),
        AppTheme.formatRupiah(t['jumlah'] ?? 0),
        t['status']?.toString(),
        t['verified_at']?.toString(),
      ]);
    }

    return buildCsv(rows);
  }

  Widget _buildChartSection() {
    final stages = _parseFunnel(_dashboard?['funnel']);
    if (stages.isEmpty) return const SizedBox.shrink();

    return DashboardFunnel(
      title: 'Grafik Pipeline',
      subtitle: 'Kunjungan → Penyewa → Tagihan → Lunas',
      stages: stages,
    );
  }

  static List<FunnelStage> _parseFunnel(dynamic raw) {
    if (raw is! List) return const [];
    return raw.whereType<Map>().map((f) {
      final label = f['label']?.toString() ?? '';
      final nilaiRaw = f['nilai'];
      return FunnelStage(
        label: label,
        sub: f['sub']?.toString(),
        nilai: nilaiRaw is num
            ? nilaiRaw.toInt()
            : int.tryParse(nilaiRaw?.toString() ?? '') ?? 0,
      );
    }).where((s) => s.label.isNotEmpty).toList();
  }
}
