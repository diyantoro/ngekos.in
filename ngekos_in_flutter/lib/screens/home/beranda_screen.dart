import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/dashboard_service.dart';

class BerandaScreen extends StatefulWidget {
  const BerandaScreen({super.key});

  @override
  State<BerandaScreen> createState() => _BerandaScreenState();
}

class _BerandaScreenState extends State<BerandaScreen> {
  Map<String, dynamic>? _dashboard;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    try {
      final auth = context.read<AuthProvider>();
      final data = auth.isAnakKos
          ? await DashboardService.getAnakKosDashboard()
          : await DashboardService.getPemilikDashboard();
      setState(() {
        _dashboard = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final hour = DateTime.now().hour;
    final greeting = hour < 12 ? 'Selamat Pagi' : hour < 18 ? 'Selamat Siang' : 'Selamat Malam';

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: _loadDashboard,
        child: CustomScrollView(
          slivers: [
            SliverAppBar(
              expandedHeight: 120,
              floating: true,
              pinned: true,
              backgroundColor: Colors.white,
              flexibleSpace: FlexibleSpaceBar(
                background: Container(
                  color: Colors.white,
                  padding: const EdgeInsets.fromLTRB(20, 50, 20, 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text(
                        '$greeting,',
                        style: TextStyle(fontSize: 14, color: Colors.grey[500]),
                      ),
                      Text(
                        user?.nama ?? 'User',
                        style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.all(16),
              sliver: _isLoading
                  ? const SliverFillRemaining(
                      child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
                    )
                  : auth.isAnakKos
                      ? _buildAnakKosDashboard()
                      : _buildPemilikDashboard(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAnakKosDashboard() {
    return SliverList(
      delegate: SliverChildListDelegate([
        Row(
          children: [
            _StatCard(
              icon: Icons.key_rounded,
              label: 'Penyewaan Aktif',
              value: '${_dashboard?['penyewaan_aktif'] ?? 0}',
              color: AppTheme.primary,
            ),
            const SizedBox(width: 12),
            _StatCard(
              icon: Icons.receipt_long_rounded,
              label: 'Tagihan',
              value: '${_dashboard?['tagihan_belum_bayar'] ?? 0}',
              color: AppTheme.warning,
            ),
          ],
        ),
        const SizedBox(height: 12),
        _StatCard(
          icon: Icons.payment_rounded,
          label: 'Total Dibayar',
          value: NumberFormat.currency(locale: 'id', symbol: 'Rp ', decimalDigits: 0)
              .format(_dashboard?['total_dibayar'] ?? 0),
          color: AppTheme.success,
          fullWidth: true,
        ),
        const SizedBox(height: 24),
        const Text('Aksi Cepat', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        const SizedBox(height: 12),
        Row(
          children: [
            _QuickAction(
              icon: Icons.search_rounded,
              label: 'Cari Kos',
              onTap: () {},
            ),
            const SizedBox(width: 12),
            _QuickAction(
              icon: Icons.receipt_rounded,
              label: 'Tagihan',
              onTap: () {},
            ),
            const SizedBox(width: 12),
            _QuickAction(
              icon: Icons.chat_rounded,
              label: 'Chat',
              onTap: () {},
            ),
          ],
        ),
      ]),
    );
  }

  Widget _buildPemilikDashboard() {
    return SliverList(
      delegate: SliverChildListDelegate([
        Row(
          children: [
            _StatCard(
              icon: Icons.apartment_rounded,
              label: 'Properti',
              value: '${_dashboard?['total_properti'] ?? 0}',
              color: AppTheme.primary,
            ),
            const SizedBox(width: 12),
            _StatCard(
              icon: Icons.king_bed_rounded,
              label: 'Total Kamar',
              value: '${_dashboard?['total_kamar'] ?? 0}',
              color: AppTheme.accent,
            ),
          ],
        ),
        const SizedBox(height: 12),
        _StatCard(
          icon: Icons.people_rounded,
          label: 'Kamar Terisi',
          value: '${_dashboard?['kamar_terisi'] ?? 0}',
          color: AppTheme.success,
          fullWidth: true,
        ),
      ]),
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color color;
  final bool fullWidth;

  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    this.fullWidth = false,
  });

  @override
  Widget build(BuildContext context) {
    final card = Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(height: 12),
          Text(value, style: TextStyle(fontSize: fullWidth ? 24 : 20, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
          const SizedBox(height: 4),
          Text(label, style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
        ],
      ),
    );

    if (fullWidth) return card;

    return Expanded(flex: 1, child: card);
  }
}

class _QuickAction extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _QuickAction({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.border),
          ),
          child: Column(
            children: [
              Icon(icon, color: AppTheme.primary, size: 28),
              const SizedBox(height: 8),
              Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
            ],
          ),
        ),
      ),
    );
  }
}
