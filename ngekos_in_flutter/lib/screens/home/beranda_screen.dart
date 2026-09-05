import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../services/dashboard_service.dart';
import '../chat/chat_list_screen.dart';
import '../akun/akun_screen.dart';

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
              backgroundColor: AppTheme.card,
              flexibleSpace: FlexibleSpaceBar(
                background: Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: Theme.of(context).brightness == Brightness.dark
                          ? [AppTheme.darkSurface, AppTheme.darkBackground]
                          : [Colors.white, const Color(0xFFFAFFFE)],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                  padding: const EdgeInsets.fromLTRB(20, 50, 20, 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text(
                        '$greeting,',
                        style: TextStyle(fontSize: 14, color: AppTheme.txtMuted),
                      ),
                      Text(
                        user?.nama ?? 'User',
                        style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.txt),
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
              gradient: AppTheme.primaryGradient,
            ),
            const SizedBox(width: 12),
            _StatCard(
              icon: Icons.receipt_long_rounded,
              label: 'Tagihan',
              value: '${_dashboard?['tagihan_belum_bayar'] ?? 0}',
              color: AppTheme.warning,
              gradient: const LinearGradient(
                colors: [Color(0xFFF59E0B), Color(0xFFF97316)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
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
          gradient: const LinearGradient(
            colors: [Color(0xFF10B981), Color(0xFF06B6D4)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          fullWidth: true,
        ),
        const SizedBox(height: 24),
        Text('Aksi Cepat', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.txt)),
        const SizedBox(height: 12),
        Row(
          children: [
            _QuickAction(
              icon: Icons.search_rounded,
              label: 'Cari Kos',
              gradient: AppTheme.primaryGradient,
              onTap: () {
                // Navigate to katalog - handled by parent MainScreen
              },
            ),
            const SizedBox(width: 12),
            _QuickAction(
              icon: Icons.receipt_rounded,
              label: 'Tagihan',
              gradient: const LinearGradient(
                colors: [Color(0xFFF59E0B), Color(0xFFF97316)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              onTap: () {},
            ),
            const SizedBox(width: 12),
            _QuickAction(
              icon: Icons.chat_rounded,
              label: 'Chat',
              gradient: const LinearGradient(
                colors: [Color(0xFF3B82F6), Color(0xFF6366F1)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              onTap: () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const ChatListScreen()));
              },
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
              gradient: AppTheme.primaryGradient,
            ),
            const SizedBox(width: 12),
            _StatCard(
              icon: Icons.king_bed_rounded,
              label: 'Total Kamar',
              value: '${_dashboard?['total_kamar'] ?? 0}',
              color: AppTheme.accent,
              gradient: const LinearGradient(
                colors: [Color(0xFF10B981), Color(0xFF059669)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _StatCard(
          icon: Icons.people_rounded,
          label: 'Kamar Terisi',
          value: '${_dashboard?['kamar_terisi'] ?? 0}',
          color: AppTheme.success,
          gradient: const LinearGradient(
            colors: [Color(0xFF10B981), Color(0xFF06B6D4)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          fullWidth: true,
        ),
        const SizedBox(height: 24),
        Text('Aksi Cepat', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.txt)),
        const SizedBox(height: 12),
        Row(
          children: [
            _QuickAction(
              icon: Icons.add_home_rounded,
              label: 'Kelola Kos',
              gradient: AppTheme.primaryGradient,
              onTap: () {},
            ),
            const SizedBox(width: 12),
            _QuickAction(
              icon: Icons.chat_rounded,
              label: 'Chat',
              gradient: const LinearGradient(
                colors: [Color(0xFF3B82F6), Color(0xFF6366F1)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              onTap: () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const ChatListScreen()));
              },
            ),
            const SizedBox(width: 12),
            _QuickAction(
              icon: Icons.person_rounded,
              label: 'Akun',
              gradient: const LinearGradient(
                colors: [Color(0xFF8B5CF6), Color(0xFFA855F7)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              onTap: () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const AkunScreen()));
              },
            ),
          ],
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
  final LinearGradient gradient;
  final bool fullWidth;

  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    required this.gradient,
    this.fullWidth = false,
  });

  @override
  Widget build(BuildContext context) {
    final card = Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.bdr),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.08),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              gradient: gradient,
              borderRadius: BorderRadius.circular(10),
              boxShadow: [
                BoxShadow(
                  color: color.withValues(alpha: 0.3),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Icon(icon, color: Colors.white, size: 20),
          ),
          const SizedBox(height: 12),
          Text(value, style: TextStyle(fontSize: fullWidth ? 24 : 20, fontWeight: FontWeight.bold, color: AppTheme.txt)),
          const SizedBox(height: 4),
          Text(label, style: TextStyle(fontSize: 13, color: AppTheme.txtSec)),
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
  final LinearGradient gradient;
  final VoidCallback onTap;

  const _QuickAction({
    required this.icon,
    required this.label,
    required this.gradient,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 16),
          decoration: BoxDecoration(
            color: AppTheme.card,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppTheme.bdr),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  gradient: gradient,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: Colors.white, size: 22),
              ),
              const SizedBox(height: 10),
              Text(label, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.txt)),
            ],
          ),
        ),
      ),
    );
  }
}
