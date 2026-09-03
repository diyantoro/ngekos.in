import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_dashboard_service.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/greeting_banner.dart';
import '../../widgets/promo_ads_banner.dart';
import '../../widgets/dashboard_line_chart.dart';

class SuperAdminDashboardScreen extends StatefulWidget {
  const SuperAdminDashboardScreen({super.key});

  @override
  State<SuperAdminDashboardScreen> createState() => _SuperAdminDashboardScreenState();
}

class _SuperAdminDashboardScreenState extends State<SuperAdminDashboardScreen> {
  Map<String, dynamic>? _dashboard;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final d = await AdminDashboardService.getSuperAdminDashboard();
      if (mounted) setState(() { _dashboard = d; _isLoading = false; });
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
                      roleLabel: 'Super Admin',
                      description: '$greeting, ${auth.user?.nama ?? 'Super Admin'}',
                      icon: const Icon(Icons.shield_rounded, color: Colors.white, size: 20),
                    ),
                    const SizedBox(height: 16),
                    const PromoAdsBanner(),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: StatCard(label: 'Properti', value: '${_dashboard?['total_properti'] ?? 0}', icon: const Icon(Icons.apartment_rounded), tone: 'teal')),
                        const SizedBox(width: 12),
                        Expanded(child: StatCard(label: 'Pengguna', value: '${_dashboard?['total_user'] ?? 0}', icon: const Icon(Icons.people_rounded), tone: 'sky')),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: StatCard(label: 'Total Kamar', value: '${_dashboard?['total_kamar'] ?? 0}', icon: const Icon(Icons.meeting_room_rounded), tone: 'emerald')),
                        const SizedBox(width: 12),
                        Expanded(child: StatCard(label: 'Kamar Terisi', value: '${_dashboard?['kamar_terisi'] ?? 0}', icon: const Icon(Icons.bed_rounded), tone: 'amber')),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: StatCard(label: 'Penyewaan Aktif', value: '${_dashboard?['penyewaan_aktif'] ?? 0}', icon: const Icon(Icons.event_available_rounded), tone: 'cyan')),
                        const SizedBox(width: 12),
                        Expanded(child: StatCard(label: 'Pendapatan', value: AppTheme.formatRupiah(_dashboard?['pendapatan'] ?? 0), icon: const Icon(Icons.account_balance_wallet_rounded), tone: 'rose')),
                      ],
                    ),
                    const SizedBox(height: 20),
                    _buildChart(),
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
}
