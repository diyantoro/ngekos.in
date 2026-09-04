import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../../config/theme.dart';
import '../../models/properti.dart';
import '../../services/katalog_service.dart';
import '../../widgets/search_filter_bar.dart';
import '../../widgets/tap_feedback.dart';
import 'detail_kos_screen.dart';

class KatalogScreen extends StatefulWidget {
  const KatalogScreen({super.key});

  @override
  State<KatalogScreen> createState() => _KatalogScreenState();
}

class _KatalogScreenState extends State<KatalogScreen> {
  List<Properti> _propertis = [];
  bool _isLoading = true;
  bool _hasMore = true;
  bool _showMap = false;
  int _page = 1;
  String? _search;
  String? _selectedKota;
  int? _selectedHargaMax;
  int? _selectedKapasitas;
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _loadKatalog();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      _loadMore();
    }
  }

  Future<void> _loadKatalog() async {
    setState(() => _isLoading = true);
    try {
      final data = await KatalogService.getKatalog(
        search: _search,
        kota: _selectedKota,
        hargaMax: _selectedHargaMax,
        kapasitas: _selectedKapasitas,
        page: 1,
      );
      final items = (data['data'] as List).map((e) => Properti.fromJson(e)).toList();
      if (!mounted) return;
      setState(() {
        _propertis = items;
        _page = 1;
        _hasMore = data['next_page_url'] != null;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
    }
  }

  Future<void> _loadMore() async {
    if (!_hasMore || _isLoading) return;
    try {
      final data = await KatalogService.getKatalog(
        search: _search,
        kota: _selectedKota,
        hargaMax: _selectedHargaMax,
        kapasitas: _selectedKapasitas,
        page: _page + 1,
      );
      final items = (data['data'] as List).map((e) => Properti.fromJson(e)).toList();
      if (!mounted) return;
      setState(() {
        _propertis.addAll(items);
        _page++;
        _hasMore = data['next_page_url'] != null;
      });
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bg,
      body: SafeArea(
        child: Column(
          children: [
            SearchFilterBar(
              onSearch: (v) { _search = v.isEmpty ? null : v; _loadKatalog(); },
              onFilterChanged: (filters) {
                setState(() {
                  _selectedKota = filters['kota'];
                  _selectedHargaMax = filters['hargaMax'];
                  _selectedKapasitas = filters['kapasitas'];
                });
                _loadKatalog();
              },
              selectedKota: _selectedKota,
              selectedHargaMax: _selectedHargaMax,
              selectedKapasitas: _selectedKapasitas,
            ),
            if (!_isLoading && _propertis.isNotEmpty)
              Align(
                alignment: Alignment.centerRight,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  child: ChoiceChip(
                    avatar: Icon(
                      _showMap ? Icons.view_list_rounded : Icons.map_rounded,
                      size: 18,
                      color: _showMap ? Colors.white : AppTheme.primary,
                    ),
                    label: Text(_showMap ? 'Daftar' : 'Peta'),
                    selected: _showMap,
                    showCheckmark: false,
                    selectedColor: AppTheme.primary,
                    labelStyle: TextStyle(
                      color: _showMap ? Colors.white : AppTheme.textPrimary,
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                    onSelected: (_) => setState(() => _showMap = !_showMap),
                  ),
                ),
              ),
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
                  : _propertis.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.search_off_rounded, size: 64, color: Colors.grey[300]),
                              const SizedBox(height: 16),
                              Text('Tidak ada kos ditemukan', style: TextStyle(fontSize: 16, color: Colors.grey[500])),
                            ],
                          ),
                        )
                      : _showMap
                          ? _MapKatalogView(
                              propertis: _propertis,
                              onTapped: (p) => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => DetailKosScreen(propertiId: p.id),
                                ),
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _loadKatalog,
                              child: ListView.builder(
                                controller: _scrollController,
                                padding: const EdgeInsets.all(16),
                                itemCount: _propertis.length,
                                itemBuilder: (context, index) => _KosCard(
                                  properti: _propertis[index],
                                  onTap: () => Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => DetailKosScreen(propertiId: _propertis[index].id),
                                    ),
                                  ),
                                ),
                              ),
                            ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MapKatalogView extends StatelessWidget {
  final List<Properti> propertis;
  final void Function(Properti) onTapped;

  const _MapKatalogView({required this.propertis, required this.onTapped});

  @override
  Widget build(BuildContext context) {
    final withLocation =
        propertis.where((p) => p.latitude != null && p.longitude != null).toList();

    if (withLocation.isEmpty) {
      return const Center(
        child: Text('Belum ada kos dengan lokasi di peta'),
      );
    }

    final latAvg = withLocation.map((p) => p.latitude!).reduce((a, b) => a + b) / withLocation.length;
    final lngAvg = withLocation.map((p) => p.longitude!).reduce((a, b) => a + b) / withLocation.length;

    return FlutterMap(
      options: MapOptions(
        initialCenter: LatLng(latAvg, lngAvg),
        initialZoom: 12,
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
          markers: withLocation
              .map(
                (p) => Marker(
                  point: LatLng(p.latitude!, p.longitude!),
                  width: 90,
                  height: 60,
                  child: GestureDetector(
                    onTap: () => onTapped(p),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          constraints: const BoxConstraints(maxWidth: 80),
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(6),
                            boxShadow: const [
                              BoxShadow(color: Colors.black26, blurRadius: 4),
                            ],
                          ),
                          child: Text(
                            p.nama,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppTheme.textPrimary),
                          ),
                        ),
                        const Icon(Icons.location_pin, color: AppTheme.primary, size: 32),
                      ],
                    ),
                  ),
                ),
              )
              .toList(),
        ),
        const RichAttributionWidget(
          attributions: [TextSourceAttribution('OpenStreetMap contributors')],
        ),
      ],
    );
  }
}

class _KosCard extends StatelessWidget {
  final Properti properti;
  final VoidCallback onTap;

  const _KosCard({required this.properti, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return TapFeedback(
      borderRadius: BorderRadius.circular(12),
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: AppTheme.card,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppTheme.bdr),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
              child: properti.foto != null
                  ? CachedNetworkImage(
                      imageUrl: properti.foto!,
                      imageRenderMethodForWeb: ImageRenderMethodForWeb.HttpGet,
                      height: 180,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      placeholder: (_, _) => Container(
                        height: 180,
                        color: Colors.grey[200],
                        child: const Center(child: CircularProgressIndicator()),
                      ),
                      errorWidget: (_, _, _) => Container(
                        height: 180,
                        color: Colors.grey[200],
                        child: const Icon(Icons.image_not_supported_rounded, size: 48, color: Colors.grey),
                      ),
                    )
                  : Container(
                      height: 180,
                      color: AppTheme.primary.withValues(alpha: 0.1),
                      child: const Center(
                        child: Icon(Icons.home_rounded, size: 48, color: AppTheme.primary),
                      ),
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    properti.nama,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.location_on_rounded, size: 14, color: AppTheme.textSecondary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          '${properti.alamat}, ${properti.kota}',
                          style: TextStyle(fontSize: 13, color: AppTheme.txtSec),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      _Tag(
                        icon: Icons.king_bed_rounded,
                        label: '${properti.kamarTersedia} kamar tersedia',
                      ),
                      const SizedBox(width: 8),
                      _Tag(
                        icon: Icons.home_work_rounded,
                        label: '${properti.totalKamar} total',
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Tag extends StatelessWidget {
  final IconData icon;
  final String label;

  const _Tag({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: AppTheme.primary.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: AppTheme.primary),
          const SizedBox(width: 4),
          Text(label, style: const TextStyle(fontSize: 11, color: AppTheme.primary)),
        ],
      ),
    );
  }
}
