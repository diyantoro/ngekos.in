import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_dashboard_service.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/greeting_banner.dart';
import '../../widgets/dashboard_line_chart.dart';
import '../../widgets/status_badge.dart';


class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  Map<String, dynamic>? _dashboard;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final d = await AdminDashboardService.getAdminDashboard();
      if (mounted) setState(() { _dashboard = d; _isLoading = false; });
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _verify(int id, bool approve) async {
    try {
      await AdminDashboardService.verifyPayment(id, approve: approve);
      _load();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(approve ? 'Pembayaran diverifikasi' : 'Pembayaran ditolak'), backgroundColor: AppTheme.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
      }
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
                      roleLabel: 'Admin',
                      description: '$greeting, ${auth.user?.nama ?? 'Admin'}',
                      icon: const Icon(Icons.admin_panel_settings_rounded, color: Colors.white, size: 20),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(child: StatCard(label: 'Properti Ditugaskan', value: '${_dashboard?['total_tugas'] ?? 0}', icon: const Icon(Icons.apartment_rounded), tone: 'teal')),
                        const SizedBox(width: 12),
                        Expanded(child: StatCard(label: 'Penyewaan Aktif', value: '${_dashboard?['penyewaan_aktif'] ?? 0}', icon: const Icon(Icons.bed_rounded), tone: 'sky')),
                      ],
                    ),
                    const SizedBox(height: 12),
                    StatCard(
                      label: 'Pembayaran Menunggu',
                      value: '${_dashboard?['pembayaran_menunggu'] ?? 0}',
                      icon: const Icon(Icons.pending_actions_rounded),
                      tone: 'amber',
                    ),
                    const SizedBox(height: 20),
                    _buildChart(),
                    const SizedBox(height: 20),
                    const Align(alignment: Alignment.centerLeft, child: Text('Verifikasi Pembayaran', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold))),
                    const SizedBox(height: 12),
                    _buildPembayaranList(),
                    const SizedBox(height: 20),
                    const Align(alignment: Alignment.centerLeft, child: Text('Riwayat Check-out', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold))),
                    const SizedBox(height: 12),
                    _buildCheckoutList(),
                  ],
                ),
              ),
      ),
    );
  }

  Widget _buildChart() {
    final chart = _dashboard?['chart'];
    if (chart is! Map) return const SizedBox.shrink();

    final labels = (chart['labels'] as List? ?? []).cast<String>();
    final pendapatan = (chart['pendapatan'] as List? ?? []).cast<num>().map((e) => e.toDouble()).toList();
    final lunas = (chart['lunas'] as List? ?? []).cast<num>().map((e) => e.toDouble()).toList();
    final belum = (chart['belum'] as List? ?? []).cast<num>().map((e) => e.toDouble()).toList();

    if (labels.isEmpty) return const SizedBox.shrink();

    return Column(
      children: [
        DashboardLineChart(
          title: 'Pendapatan 6 Bulan Terakhir',
          labels: labels,
          yCurrency: true,
          series: [FlLineData(label: 'Pendapatan', values: pendapatan, color: AppTheme.primary)],
        ),
        const SizedBox(height: 12),
        DashboardLineChart(
          title: 'Tagihan: Lunas vs Belum Lunas',
          labels: labels,
          yCurrency: true,
          series: [
            FlLineData(label: 'Lunas', values: lunas, color: AppTheme.accent),
            FlLineData(label: 'Belum Lunas', values: belum, color: AppTheme.rose),
          ],
        ),
      ],
    );
  }

  Widget _buildPembayaranList() {
    final pembayarans = _dashboard?['pembayarans'] as List? ?? [];
    if (pembayarans.isEmpty) {
      return Padding(
        padding: const EdgeInsets.all(32),
        child: Center(child: Text('Tidak ada pembayaran pending', style: TextStyle(color: AppTheme.txtSec))),
      );
    }
    return Column(
      children: pembayarans.map((p) => Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(color: AppTheme.card, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppTheme.bdrLight)),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(p['anak_kos_nama'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                  const SizedBox(height: 2),
                  Text('${p['periode'] ?? "-"} · ${p['metode'] ?? "-"}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                  Text(AppTheme.formatRupiah(p['jumlah'] ?? 0), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                ],
              ),
            ),
            Row(
              children: [
                IconButton(
                  onPressed: () => _verify(p['id'], true),
                  icon: const Icon(Icons.check_circle_rounded, color: AppTheme.success, size: 28),
                ),
                IconButton(
                  onPressed: () => _verify(p['id'], false),
                  icon: const Icon(Icons.cancel_rounded, color: AppTheme.error, size: 28),
                ),
              ],
            ),
          ],
        ),
      )).toList(),
    );
  }

  Widget _buildCheckoutList() {
    final checkouts = _dashboard?['checkouts'] as List? ?? [];
    if (checkouts.isEmpty) {
      return Padding(
        padding: const EdgeInsets.all(32),
        child: Center(child: Text('Belum ada riwayat check-out', style: TextStyle(color: AppTheme.txtSec))),
      );
    }
    return Column(
      children: checkouts.map((c) => Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(color: AppTheme.card, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppTheme.bdrLight)),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(c['anak_kos_nama'] ?? '-', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                ),
                StatusBadge(status: c['status'] ?? ''),
              ],
            ),
            const SizedBox(height: 4),
            Text('Kamar ${c['kamar_nama'] ?? "-"} · ${c['properti_nama'] ?? "-"}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
              if (c['tanggal_keluar'] != null)
                Text('Keluar: ${c['tanggal_keluar']}', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
          ],
        ),
      )).toList(),
    );
  }
}
