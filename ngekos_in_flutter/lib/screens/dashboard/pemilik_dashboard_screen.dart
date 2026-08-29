import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/dashboard_service.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/greeting_banner.dart';
import '../../widgets/tab_bar_widget.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/promo_ads_banner.dart';
import '../../widgets/trending_kos_section.dart';

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
      if (mounted) setState(() {
        _dashboard = d;
        _propertis = p is List ? p : (p['data'] as List? ?? []);
        _sewaans = s;
        _isLoading = false;
      });
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
      backgroundColor: AppTheme.background,
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
                              fillColor: Colors.white,
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppTheme.border)),
                              enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppTheme.border)),
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
        child: const Center(child: Text('Belum ada properti', style: TextStyle(color: AppTheme.textSecondary))),
      );
    }
    return Column(
      children: filtered.map((p) {
        final total = (p['total_kamar'] ?? 0) as int;
        final terisi = (p['kamar_terisi'] ?? 0) as int;
        final persentase = total > 0 ? (terisi / total * 100).round() : 0;
        final kamars = (p['kamars'] as List? ?? []);
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFFF9FAFB),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.borderLight),
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
                        Text(p['nama'] ?? '-', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppTheme.textPrimary)),
                        if (p['alamat'] != null)
                          Text(p['alamat'].toString(), style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
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
                        backgroundColor: AppTheme.surfaceGray,
                        valueColor: const AlwaysStoppedAnimation(Color(0xFF14B8A6)),
                        minHeight: 8,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text('$terisi/$total terisi', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.textSecondary)),
                ],
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppTheme.borderLight),
                ),
                child: Row(
                  children: [
                    const Expanded(
                      flex: 2,
                      child: Text('Kamar', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.textMuted, letterSpacing: 0.5)),
                    ),
                    const Expanded(
                      flex: 1,
                      child: Text('Harga/Bln', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.textMuted, letterSpacing: 0.5)),
                    ),
                    const Expanded(
                      flex: 1,
                      child: Text('Status', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.textMuted, letterSpacing: 0.5)),
                    ),
                  ],
                ),
              ),
              ...kamars.map((k) => Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: AppTheme.borderLight))),
                child: Row(
                  children: [
                    Expanded(
                      flex: 2,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(k['nama'] ?? '-', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.textPrimary)),
                          Text('${k['kapasitas'] ?? 0} orang', style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
                        ],
                      ),
                    ),
                    Expanded(flex: 1, child: Text('${AppTheme.formatRupiah(k['harga_sewa_bulanan'] ?? 0)}', style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary))),
                    Expanded(flex: 1, child: Align(alignment: Alignment.centerLeft, child: StatusBadge(status: k['status'] ?? ''))),
                  ],
                ),
              )),
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
        child: const Center(child: Text('Belum ada data penyewaan', style: TextStyle(color: AppTheme.textSecondary))),
      );
    }
    return Column(
      children: filtered.map((s) {
        final isAktif = s['status'] == 'aktif';
        final mintaKeluar = s['permintaan_keluar_pada'] != null;
        final belumLunas = (s['tagihan_belum_bayar'] ?? 0) as int;
        final telat = (s['telat'] ?? 0) as int;
        final sisa = (s['sisa_tagihan'] ?? 0) as int;
        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: mintaKeluar && isAktif ? const Color(0xFFFDE68A) : AppTheme.borderLight),
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
                              child: Text(s['anak_kos_nama'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppTheme.textPrimary), overflow: TextOverflow.ellipsis),
                            ),
                            if (mintaKeluar)
                              Container(
                                margin: const EdgeInsets.only(left: 6),
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(color: const Color(0xFFFEF3C7), borderRadius: BorderRadius.circular(4)),
                                child: const Text('Minta keluar', style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFFB45309))),
                              ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Kamar ${s['kamar_nama'] ?? "-"}${(s['properti_nama'] ?? '') != '' ? ' · ${s['properti_nama']}' : ''}',
                          style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                        ),
                        if (s['tanggal_masuk'] != null)
                          Text('Masuk: ${DateFormat('d MMM yyyy').format(DateTime.parse(s['tanggal_masuk']))}', style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
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
                          Text('Sisa tagihan: ${AppTheme.formatRupiah(sisa)}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppTheme.textPrimary)),
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
              if (isAktif)
                Padding(
                  padding: const EdgeInsets.only(top: 10),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      OutlinedButton(
                        onPressed: () => _checkout(s),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.rose,
                          side: BorderSide(color: AppTheme.rose.withValues(alpha: 0.4)),
                          backgroundColor: const Color(0xFFFFF1F2),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        child: Text(mintaKeluar ? 'Setujui Check-out' : 'Check-out', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                      ),
                    ],
                  ),
                ),
              if (!isAktif && s['tanggal_keluar'] != null)
                Padding(
                  padding: const EdgeInsets.only(top: 6),
                  child: Text('Keluar: ${DateFormat('d MMM yyyy').format(DateTime.parse(s['tanggal_keluar']))}', style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary, fontStyle: FontStyle.italic)),
                ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Future<void> _checkout(dynamic s) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Check-out Penyewa'),
        content: Text('Check-out ${s['anak_kos_nama']} dari kamar ${s['kamar_nama']}? Kamar akan kembali tersedia.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Check-out', style: TextStyle(fontWeight: FontWeight.bold, color: AppTheme.rose)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    try {
      await DashboardService.checkout(s['id'] as int);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Check-out berhasil. Kamar kembali tersedia.'), backgroundColor: AppTheme.success));
        _load();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
      }
    }
  }
}
