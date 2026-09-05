import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import '../../config/theme.dart';
import '../../models/properti.dart';
import '../../providers/theme_provider.dart';
import '../../services/katalog_service.dart';
import '../../utils/koordinat.dart';
import '../../widgets/chatbot_widget.dart';
import '../../widgets/tap_feedback.dart';
import '../../widgets/promo_ads_banner.dart';
import '../katalog/detail_kos_screen.dart';
import '../katalog/katalog_screen.dart';
import '../auth/pilih_peran_screen.dart';


class LandingScreen extends StatefulWidget {
  const LandingScreen({super.key});

  @override
  State<LandingScreen> createState() => _LandingScreenState();
}

class _LandingScreenState extends State<LandingScreen> with SingleTickerProviderStateMixin {
  List<Properti> _propertis = [];
  List<String> _kotaList = [];
  int _totalProperti = 0;
  int _totalKamar = 0;
  bool _isLoading = true;
  final _searchController = TextEditingController();
  late final AnimationController _gradientCtrl;

  @override
  void initState() {
    super.initState();
    _loadData();
    _gradientCtrl = AnimationController(vsync: this, duration: const Duration(seconds: 9))
      ..repeat(reverse: true);
  }

  @override
  void dispose() {
    _gradientCtrl.dispose();
    _searchController.dispose();
    super.dispose();
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
    context.watch<ThemeProvider>();
    return Scaffold(
      backgroundColor: AppTheme.bg,
      body: SingleChildScrollView(
        child: Column(
          children: [
            _buildHero(),
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 4, 16, 0),
              child: PromoAdsBanner(),
            ),
            _buildPromoCTACards(),
            _buildStatsRow(),
            _buildKotaChips(),
            _buildLandingMap(),
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
    return AnimatedBuilder(
      animation: _gradientCtrl,
      builder: (context, _) {
        final t = _gradientCtrl.value;
        return Container(
          width: double.infinity,
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: const [Color(0xFF059669), Color(0xFF0D9488), Color(0xFF0891B2)],
              begin: Alignment(-0.4 + t * 0.8, -1),
              end: Alignment(0.4 - t * 0.8, 1),
            ),
          ),
          child: Stack(
            children: [
              Positioned(
                right: -40 + t * 16,
                top: -54,
                child: _glowBlob(190, Colors.white, 0.16),
              ),
              Positioned(
                left: -56 - t * 14,
                bottom: 36,
                child: _glowBlob(170, const Color(0xFF99F6E4), 0.18),
              ),
              Positioned(
                right: -10,
                top: 128,
                child: _glowBlob(110, const Color(0xFFF0ABFC), 0.15),
              ),
              SafeArea(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 20),
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
                                    boxShadow: [
                                      BoxShadow(color: const Color(0xFF0F766E).withValues(alpha: 0.3), blurRadius: 16, offset: const Offset(0, 6)),
                                    ],
                                  ),
                                  child: const Text('Masuk', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF0D9488))),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
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
                      const SizedBox(height: 14),
                      const Text(
                        'Cari Kos',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: Colors.white, height: 1.1),
                      ),
                      ShaderMask(
                        shaderCallback: (bounds) => LinearGradient(
                          begin: Alignment(-1.0 + t * 2.0, 0),
                          end: Alignment(-0.2 + t * 2.0, 0),
                          colors: const [Colors.white, Color(0xFF99F6E4), Color(0xFF67E8F9), Colors.white],
                        ).createShader(bounds),
                        blendMode: BlendMode.srcIn,
                        child: const Text(
                          'Gak Pake Ribet',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: Colors.white, height: 1.1),
                        ),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Temukan kamar kos impianmu, tanya pemilik langsung lewat chat, dan kelola semua dalam satu aplikasi.',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 13, color: Colors.white70, height: 1.4),
                      ),
                      const SizedBox(height: 16),
                      _buildHeroSearch(),
                      const SizedBox(height: 10),
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
            ],
          ),
        );
      },
    );
  }

  Widget _glowBlob(double size, Color color, double opacity) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: RadialGradient(
          colors: [color.withValues(alpha: opacity), color.withValues(alpha: 0)],
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

  void _goSearch() {
    final q = _searchController.text.trim();
    Navigator.push(context, MaterialPageRoute(
      builder: (_) => KatalogScreen(initialSearch: q.isEmpty ? null : q),
    ));
    if (q.isNotEmpty) _searchController.clear();
  }

  Widget _buildHeroSearch() {
    return Container(
      padding: const EdgeInsets.all(5),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(color: Colors.white.withValues(alpha: 0.45), blurRadius: 26, offset: const Offset(0, 10)),
          BoxShadow(color: const Color(0xFF0F766E).withValues(alpha: 0.25), blurRadius: 18, offset: const Offset(0, 8)),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(13),
        child: Row(
          children: [
            const Padding(
              padding: EdgeInsets.only(left: 14),
              child: Icon(Icons.search_rounded, color: Color(0xFF0D9488), size: 22),
            ),
            Expanded(
              child: TextField(
                controller: _searchController,
                style: const TextStyle(fontSize: 14, color: Color(0xFF111827)),
                textInputAction: TextInputAction.search,
                decoration: const InputDecoration(
                  hintText: 'Ketik nama kos, kota, atau lokasi...',
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  hintStyle: TextStyle(fontSize: 14, color: Color(0xFF9CA3AF)),
                ),
                onSubmitted: (_) => _goSearch(),
              ),
            ),
            TapFeedback(
              borderRadius: BorderRadius.circular(12),
              onTap: _goSearch,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(colors: [Color(0xFF059669), Color(0xFF0D9488)]),
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(color: const Color(0xFF14B8A6).withValues(alpha: 0.4), blurRadius: 12, offset: const Offset(0, 4)),
                  ],
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('Cari', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white)),
                    SizedBox(width: 4),
                    Icon(Icons.arrow_forward_rounded, size: 16, color: Colors.white),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 5),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsRow() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      child: Row(
        children: [
          _glassStat(Icons.home_work_rounded, _totalProperti, 'Kos Aktif'),
          const SizedBox(width: 12),
          _glassStat(Icons.bed_rounded, _totalKamar, 'Kamar Tersedia'),
        ],
      ),
    );
  }

  Widget _glassStat(IconData icon, int value, String label) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: AppTheme.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.bdr),
        ),
        child: Column(
          children: [
            Icon(icon, size: 20, color: AppTheme.primary),
            const SizedBox(height: 6),
            _AnimatedCounter(target: value, fontSize: 20),
            const SizedBox(height: 2),
            Text(label, style: TextStyle(fontSize: 11, color: AppTheme.txtMuted)),
          ],
        ),
      ),
    );
  }

  Widget _buildPromoCTACards() {
    Widget card({
      required IconData icon,
      required String title,
      required String desc,
      required List<Color> colors,
      required String buttonLabel,
    }) {
      return Expanded(
        child: TapFeedback(
          borderRadius: BorderRadius.circular(16),
          onTap: _goRegister,
          child: Container(
            constraints: const BoxConstraints(minHeight: 196),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(colors: colors, begin: Alignment.topLeft, end: Alignment.bottomRight),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(color: colors.first.withValues(alpha: 0.35), blurRadius: 14, offset: const Offset(0, 6)),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.22), borderRadius: BorderRadius.circular(12)),
                  child: Icon(icon, color: Colors.white, size: 20),
                ),
                const SizedBox(height: 12),
                Text(
                  title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Colors.white, height: 1.2),
                ),
                const SizedBox(height: 6),
                Text(
                  desc,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(fontSize: 11, color: Colors.white.withValues(alpha: 0.9), height: 1.4),
                ),
                const SizedBox(height: 14),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(buttonLabel, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Colors.white)),
                    const SizedBox(width: 4),
                    const Icon(Icons.arrow_forward_rounded, size: 14, color: Colors.white),
                  ],
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      child: Row(
        children: [
          card(
            icon: Icons.search_rounded,
            title: 'Mulai Cari Kos Hari Ini',
            desc: 'Tanpa biaya, langsung chat pemilik kos.',
            colors: const [Color(0xFF0D9488), Color(0xFF059669)],
            buttonLabel: 'Daftar Gratis',
          ),
          const SizedBox(width: 12),
          card(
            icon: Icons.campaign_rounded,
            title: 'Promosikan Kos Anda',
            desc: 'Kelola kamar dan balas chat pencari kos.',
            colors: const [Color(0xFF7C3AED), Color(0xFFD946EF)],
            buttonLabel: 'Mulai Gratis',
          ),
        ],
      ),
    );
  }

  void _goRegister() {
    Navigator.push(context, MaterialPageRoute(builder: (_) => const PilihPeranScreen()));
  }

  Widget _buildKotaChips() {
    final counts = <String, int>{};
    for (final p in _propertis) {
      final kota = p.kota.trim().isEmpty ? 'Lainnya' : p.kota.trim();
      counts[kota] = (counts[kota] ?? 0) + 1;
    }
    final sorted = counts.entries.toList()..sort((a, b) => b.value.compareTo(a.value));
    final data = sorted.take(8).toList();
    final noData = data.isEmpty;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 24, 16, 0),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.card,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.bdrLight),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 14, offset: const Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(colors: [AppTheme.primary, AppTheme.accent]),
                    borderRadius: BorderRadius.circular(10),
                    boxShadow: [BoxShadow(color: AppTheme.primary.withValues(alpha: 0.35), blurRadius: 10, offset: const Offset(0, 4))],
                  ),
                  child: const Icon(Icons.location_city_rounded, color: Colors.white, size: 18),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Cari per Kota', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppTheme.txt)),
                      Text(noData ? 'Kos akan muncul setelah ada yang terdaftar' : 'Pilih kota untuk lihat kos yang tersedia', style: TextStyle(fontSize: 11.5, color: AppTheme.txtSec)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (noData)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 26),
                decoration: BoxDecoration(
                  color: AppTheme.bg,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppTheme.bdrLight),
                ),
                child: Column(
                  children: [
                    Icon(Icons.donut_large_rounded, size: 40, color: AppTheme.txtMuted.withValues(alpha: 0.5)),
                    const SizedBox(height: 8),
                    Text('Belum ada data kota', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.txtSec)),
                  ],
                ),
              )
            else
              Wrap(
                spacing: 10,
                runSpacing: 10,
                children: data.map((e) => _kotaChip(e.key, e.value)).toList(),
              ),
          ],
        ),
      ),
    );
  }

  Widget _kotaChip(String kota, int jumlah) {
    return TapFeedback(
      borderRadius: BorderRadius.circular(14),
      onTap: () {
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => KatalogScreen(initialKota: kota),
        ));
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: [AppTheme.primary, AppTheme.accent], begin: Alignment.topLeft, end: Alignment.bottomRight),
          borderRadius: BorderRadius.circular(14),
          boxShadow: [BoxShadow(color: AppTheme.primary.withValues(alpha: 0.28), blurRadius: 10, offset: const Offset(0, 4))],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.location_on_rounded, color: Colors.white, size: 16),
            const SizedBox(width: 6),
            Text(kota, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13)),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.22), borderRadius: BorderRadius.circular(20)),
              child: Text('$jumlah kos', style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w600)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLandingMap() {
    final bertitik = _propertis
        .map((p) => MapEntry(p, koordinatProperti(p)))
        .where((e) => e.value != null)
        .toList();
    if (bertitik.isEmpty) return const SizedBox.shrink();

    final latAvg = bertitik.map((e) => e.value!.latitude).reduce((a, b) => a + b) / bertitik.length;
    final lngAvg = bertitik.map((e) => e.value!.longitude).reduce((a, b) => a + b) / bertitik.length;
    final zoom = bertitik.length <= 1
        ? 13.0
        : bertitik.length <= 4
            ? 10.0
            : 8.0;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.card,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.bdrLight),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 14, offset: const Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(colors: [AppTheme.primary, AppTheme.accent]),
                    borderRadius: BorderRadius.circular(10),
                    boxShadow: [BoxShadow(color: AppTheme.primary.withValues(alpha: 0.35), blurRadius: 10, offset: const Offset(0, 4))],
                  ),
                  child: const Icon(Icons.map_rounded, color: Colors.white, size: 18),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Peta Kos', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppTheme.txt)),
                      Text('${bertitik.length} kos di peta · titik mengikuti koordinat atau pusat kota', style: TextStyle(fontSize: 11.5, color: AppTheme.txtSec)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            ClipRRect(
              borderRadius: BorderRadius.circular(14),
              child: SizedBox(
                height: 240,
                child: FlutterMap(
                  options: MapOptions(
                    initialCenter: LatLng(latAvg, lngAvg),
                    initialZoom: zoom,
                    interactionOptions: const InteractionOptions(
                      flags: InteractiveFlag.all & ~InteractiveFlag.rotate,
                    ),
                  ),
                  children: [
                    TileLayer(
                      urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                      userAgentPackageName: 'id.ngekosin.app',
                      maxNativeZoom: 19,
                    ),
                    MarkerLayer(
                      markers: bertitik.map((e) {
                        final p = e.key;
                        return Marker(
                          point: e.value!,
                          width: 34,
                          height: 34,
                          child: GestureDetector(
                            onTap: () => Navigator.push(context, MaterialPageRoute(
                              builder: (_) => DetailKosScreen(propertiId: p.id),
                            )),
                            child: const Icon(Icons.location_pin, color: AppTheme.primary, size: 34),
                          ),
                        );
                      }).toList(),
                    ),
                    const RichAttributionWidget(
                      attributions: [TextSourceAttribution('OpenStreetMap contributors')],
                    ),
                  ],
                ),
              ),
            ),
          ],
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
                    Text('Belum ada kos terdaftar', style: TextStyle(color: AppTheme.txtSec)),
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
                      Icon(Icons.location_on_rounded, size: 12, color: AppTheme.txtSec),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text('${properti.kota}, ${properti.alamat}', style: TextStyle(fontSize: 11, color: AppTheme.txtSec), maxLines: 1, overflow: TextOverflow.ellipsis),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.only(top: 8),
                    decoration: BoxDecoration(border: Border(top: BorderSide(color: AppTheme.bdrLight))),
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
          LayoutBuilder(
            builder: (context, constraints) {
              final maxW = constraints.maxWidth.isFinite ? constraints.maxWidth : 360.0;
              final itemWidth = ((maxW - 12) / 2).clamp(120.0, 600.0).toDouble();
              return Wrap(
                spacing: 12,
                runSpacing: 12,
                children: features.map((f) {
                  return Container(
                    width: itemWidth,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppTheme.surfaceC,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
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
                        Text(
                          f['desc'] as String,
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(fontSize: 11, color: AppTheme.txtSec, height: 1.4),
                        ),
                      ],
                    ),
                  );
                }).toList(),
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

class _AnimatedCounter extends StatelessWidget {
  final int target;
  final double fontSize;
  const _AnimatedCounter({required this.target, this.fontSize = 20});

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: Tween(begin: 0, end: target.toDouble()),
      duration: const Duration(milliseconds: 1200),
      curve: Curves.easeOutCubic,
      builder: (context, value, _) => Text(
        value.round().toString(),
        style: TextStyle(fontSize: fontSize, fontWeight: FontWeight.w800, color: AppTheme.txt),
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
