import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import '../../config/theme.dart';
import '../../models/properti.dart';
import '../../providers/theme_provider.dart';
import '../../services/katalog_service.dart';
import '../../widgets/chatbot_widget.dart';
import '../../widgets/tap_feedback.dart';
import '../../widgets/promo_ads_banner.dart';
import '../katalog/detail_kos_screen.dart';
import '../auth/pilih_peran_screen.dart';


class LandingScreen extends StatefulWidget {
  const LandingScreen({super.key});

  @override
  State<LandingScreen> createState() => _LandingScreenState();
}

class _LandingScreenState extends State<LandingScreen> {
  List<Properti> _propertis = [];
  List<String> _kotaList = [];
  int _totalProperti = 0;
  int _totalKamar = 0;
  bool _isLoading = true;
  final _searchController = TextEditingController();
  final PageController _bannerController = PageController();
  Timer? _bannerTimer;
  int _currentBanner = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
    _startBannerAutoScroll();
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _bannerController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _startBannerAutoScroll() {
    _bannerTimer?.cancel();
    _bannerTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (!mounted || !_bannerController.hasClients) return;
      final total = _bannerSlides().length;
      final next = (_currentBanner + 1) % total;
      _bannerController.animateToPage(
        next,
        duration: const Duration(milliseconds: 500),
        curve: Curves.easeInOut,
      );
    });
  }

  List<Map<String, dynamic>> _bannerSlides() {
    return [
      {
        'gradient': const LinearGradient(colors: [Color(0xFF3B82F6), Color(0xFF6366F1)]),
        'badge': 'Promo',
        'title': 'Daftar Gratis!',
        'subtitle': 'Buat akun dan langsung cari kos impianmu.',
        'action': 'Daftar Sekarang',
      },
      {
        'gradient': const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF0D9488)]),
        'badge': 'Pemilik Kos',
        'title': 'Promosikan Kos Anda',
        'subtitle': 'Daftarkan kos, kelola kamar, dan balas pertanyaan pencari kos lewat chat.',
        'action': 'Mulai Gratis',
      },
      {
        'gradient': const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFF97316)]),
        'badge': 'Statistik',
        'title': '$_totalProperti Kos Aktif',
        'subtitle': '$_totalKamar Kamar Tersedia di Ngekos.in',
        'action': '',
      },
    ];
  }


  Future<void> _loadData() async {
    try {
      final katalogData = await KatalogService.getKatalog(page: 1);
      final items = (katalogData['data'] as List).map((e) => Properti.fromJson(e)).toList();
      final kamarTersedia = items.fold<int>(0, (sum, p) => sum + p.kamarTersedia);
      setState(() {
        _propertis = items;
        _totalProperti = katalogData['total'] ?? items.length;
        _totalKamar = kamarTersedia;
        _kotaList = items.map((p) => p.kota).where((k) => k.isNotEmpty).toSet().toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bg,
      body: SingleChildScrollView(
        child: Column(
          children: [
            _buildHero(),
            _buildBanners(),
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 4, 16, 0),
              child: PromoAdsBanner(),
            ),
            _buildKosTerbaru(),
            _buildKenapa(),
            _buildCTA(),
            const SizedBox(height: 80),
          ],
        ),
      ),
      floatingActionButton: const ChatbotWidget(),
    );
  }

  Widget _buildHero() {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.home_rounded, color: Colors.white, size: 24),
                      SizedBox(width: 8),
                      Text('Ngekos.in', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Colors.white)),
                    ],
                  ),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      _HeroToggle(),
                      const SizedBox(width: 8),
                      GestureDetector(
                        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen())),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Text('Masuk', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF0D9488))),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(width: 6, height: 6, decoration: const BoxDecoration(color: Color(0xFF6EE7B7), shape: BoxShape.circle)),
                    const SizedBox(width: 8),
                    Text('$_totalKamar kamar tersedia saat ini', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.white)),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              const Text(
                'Cari Kos\nGak Pake Ribet',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: Colors.white, height: 1.2),
              ),
              const SizedBox(height: 12),
              const Text(
                'Temukan kamar kos impianmu, tanya pemilik langsung lewat chat, dan kelola semua dalam satu aplikasi.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 14, color: Colors.white70, height: 1.5),
              ),
              const SizedBox(height: 24),
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.15), blurRadius: 20, offset: const Offset(0, 8))],
                ),
                child: Row(
                  children: [
                    const Padding(
                      padding: EdgeInsets.only(left: 12),
                      child: Icon(Icons.search_rounded, color: AppTheme.textSecondary, size: 20),
                    ),
                    Expanded(
                      child: TextField(
                        controller: _searchController,
                        decoration: const InputDecoration(
                          hintText: 'Ketik nama kos, kota, atau lokasi...',
                          border: InputBorder.none,
                          enabledBorder: InputBorder.none,
                          focusedBorder: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          hintStyle: TextStyle(fontSize: 14),
                        ),
                        onSubmitted: (v) => Navigator.push(context, MaterialPageRoute(
                          builder: (_) => const PilihPeranScreen(),
                        )),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [Color(0xFF059669), Color(0xFF0D9488)]),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Text('Cari Kos', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                alignment: WrapAlignment.center,
                children: [
                  _buildFilterChip('Semua Kos', Icons.home_work_rounded, null),
                  ..._kotaList.take(4).map((kota) => _buildFilterChip(kota, Icons.location_on_rounded, kota)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, IconData icon, String? kota) {
    return GestureDetector(
      onTap: () {},
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.2),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 14, color: Colors.white),
            const SizedBox(width: 4),
            Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: Colors.white)),
          ],
        ),
      ),
    );
  }

  Widget _buildBanners() {
    final slides = _bannerSlides();
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 0),
      child: Transform.translate(
        offset: const Offset(0, -20),
        child: Column(
          children: [
            SizedBox(
              height: 150,
              child: PageView.builder(
                controller: _bannerController,
                itemCount: slides.length,
                onPageChanged: (i) => setState(() => _currentBanner = i),
                itemBuilder: (context, index) {
                  final slide = slides[index];
                  return _buildBannerItem(
                    gradient: slide['gradient'] as Gradient,
                    badge: slide['badge'] as String,
                    title: slide['title'] as String,
                    subtitle: slide['subtitle'] as String,
                    action: slide['action'] as String,
                    onTap: () {
                      if (slide['badge'] == 'Promo' || slide['badge'] == 'Pemilik Kos') {
                        Navigator.push(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen()));
                      }
                    },
                  );
                },
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(slides.length, (i) {
                final active = i == _currentBanner;
                return AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  margin: const EdgeInsets.symmetric(horizontal: 3),
                  width: active ? 18 : 6,
                  height: 6,
                  decoration: BoxDecoration(
                    color: active ? AppTheme.primary : AppTheme.primary.withValues(alpha: 0.3),
                    borderRadius: BorderRadius.circular(3),
                  ),
                );
              }),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBannerItem({
    required Gradient gradient,
    required String badge,
    required String title,
    required String subtitle,
    required String action,
    required VoidCallback onTap,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 2),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: gradient,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Stack(
            children: [
              Positioned(
                top: -12,
                right: -12,
                child: Container(width: 60, height: 60, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), shape: BoxShape.circle)),
              ),
              Positioned(
                bottom: -20,
                left: -10,
                child: Container(width: 70, height: 70, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.08), shape: BoxShape.circle)),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(4)),
                    child: Text(badge, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white, letterSpacing: 0.5)),
                  ),
                  const SizedBox(height: 8),
                  Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
                  const SizedBox(height: 4),
                  Text(subtitle, maxLines: 2, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 11, color: Colors.white.withValues(alpha: 0.85), height: 1.3)),
                  if (action.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(action, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white)),
                        const Icon(Icons.arrow_forward_rounded, size: 13, color: Colors.white),
                      ],
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildKosTerbaru() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Kos Terbaru', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.txt)),
                    const SizedBox(height: 2),
                    Text('Kos yang baru ditambahkan pemilik', style: TextStyle(fontSize: 12, color: AppTheme.txtSec)),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen())),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('Lihat Semua', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.primary)),
                    Icon(Icons.chevron_right_rounded, color: AppTheme.primary, size: 18),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (_isLoading)
            const Center(child: CircularProgressIndicator(color: AppTheme.primary))
          else if (_propertis.isEmpty)
            Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  children: [
                    Icon(Icons.home_work_outlined, size: 48, color: Colors.grey[300]),
                    const SizedBox(height: 12),
                    const Text('Belum ada kos terdaftar', style: TextStyle(color: AppTheme.textSecondary)),
                  ],
                ),
              ),
            )
          else
            SizedBox(
              height: 260,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                itemCount: _propertis.length,
                itemBuilder: (context, index) => _buildKosCard(_propertis[index]),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildKosCard(Properti properti) {
    return TapFeedback(
      borderRadius: BorderRadius.circular(16),
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => DetailKosScreen(propertiId: properti.id))),
      child: Container(
        width: 220,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          color: AppTheme.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.bdrLight),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                Container(
                  height: 140,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(colors: [Color(0xFFCCFBF1), Color(0xFFD1FAE5), Color(0xFFCFFAFE)]),
                    borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                  ),
                  child: properti.foto != null
                      ? ClipRRect(
                          borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                          child: CachedNetworkImage(
                            imageUrl: properti.foto!,
                            imageRenderMethodForWeb: ImageRenderMethodForWeb.HttpGet,
                            width: double.infinity,
                            height: 140,
                            fit: BoxFit.cover,
                            errorWidget: (_, _, _) => const Center(child: Icon(Icons.home_rounded, size: 40, color: AppTheme.primary)),
                          ),
                        )
                      : const Center(child: Icon(Icons.home_rounded, size: 40, color: AppTheme.primary)),
                ),
                Positioned(
                  top: 10,
                  right: 10,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: properti.kamarTersedia > 0 ? AppTheme.accent : Colors.grey[800],
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      properti.kamarTersedia > 0 ? '${properti.kamarTersedia} Kamar' : 'Penuh',
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(properti.nama, style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.txt), maxLines: 1, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.location_on_rounded, size: 12, color: AppTheme.textSecondary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text('${properti.kota}, ${properti.alamat}', style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary), maxLines: 1, overflow: TextOverflow.ellipsis),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.only(top: 8),
                    decoration: const BoxDecoration(border: Border(top: BorderSide(color: AppTheme.borderLight))),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Mulai dari', style: TextStyle(fontSize: 10, color: AppTheme.txtMuted, fontWeight: FontWeight.w500)),
                        Text('Lihat Detail', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppTheme.primary)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildKenapa() {
    final features = [
      {'title': 'Cari Mudah', 'desc': 'Filter berdasarkan lokasi, harga, dan fasilitas.', 'icon': Icons.search_rounded},
      {'title': 'Chat Langsung', 'desc': 'Tanya pemilik kos langsung dari HP.', 'icon': Icons.chat_rounded},
      {'title': 'Bayar Praktis', 'desc': 'Tagihan bulanan otomatis, bayar lewat transfer.', 'icon': Icons.payment_rounded},
      {'title': 'Kelola Mudah', 'desc': 'Pemilik kelola kamar, chat, dan pembayaran.', 'icon': Icons.apartment_rounded},
    ];

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(top: 32),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(color: AppTheme.card, border: Border.symmetric(horizontal: BorderSide(color: AppTheme.bdrLight))),
      child: Column(
        children: [
          Text('Kenapa Pilih Ngekos.in?', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppTheme.txt)),
          const SizedBox(height: 4),
          Text('Solusi praktis untuk pencari kos dan pemilik kos', style: TextStyle(fontSize: 13, color: AppTheme.txtSec)),
          const SizedBox(height: 24),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              mainAxisExtent: 190,
            ),
            itemCount: features.length,
            itemBuilder: (context, index) {
              final f = features[index];
              return Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceC,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [AppTheme.primary, AppTheme.accent]),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(f['icon'] as IconData, color: Colors.white, size: 20),
                    ),
                    const SizedBox(height: 12),
                    Text(f['title'] as String, style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.txt)),
                    const SizedBox(height: 6),
                    Text(f['desc'] as String, style: TextStyle(fontSize: 11, color: AppTheme.txtSec, height: 1.4)),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildCTA() {
    return Padding(
      padding: const EdgeInsets.all(24),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(32),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Column(
          children: [
            const Text('Siap Cari atau Punya Kos?', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Colors.white)),
            const SizedBox(height: 8),
            const Text('Buat akun gratis sekarang dan mulai cari kos impianmu.', style: TextStyle(fontSize: 14, color: Colors.white70)),
            const SizedBox(height: 24),
            Wrap(
              spacing: 12,
              runSpacing: 12,
              alignment: WrapAlignment.center,
              children: [
                GestureDetector(
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen())),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
                    child: const Text('Daftar Gratis', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF0D9488))),
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen())),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                    ),
                    child: const Text('Lihat Semua Kos', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.white)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _HeroToggle extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final theme = context.watch<ThemeProvider>();
    final isDark = theme.isDark;
    return GestureDetector(
      onTap: theme.toggle,
      child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
        ),
        child: Icon(isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded, size: 18, color: Colors.white),
      ),
    );
  }
}
