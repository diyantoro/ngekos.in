import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../models/properti.dart';
import '../services/katalog_service.dart';
import '../screens/katalog/detail_kos_screen.dart';
import '../screens/katalog/katalog_screen.dart';

class TrendingKosSection extends StatefulWidget {
  const TrendingKosSection({super.key});

  @override
  State<TrendingKosSection> createState() => _TrendingKosSectionState();
}

class _TrendingKosSectionState extends State<TrendingKosSection> {
  List<Properti> _propertis = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final data = await KatalogService.getKatalog(sort: 'trending', perPage: 6);
      final items = (data['data'] as List).map((e) => Properti.fromJson(e as Map<String, dynamic>)).toList();
      if (mounted) {
        setState(() {
          _propertis = items;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 32),
        child: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
      );
    }
    if (_propertis.isEmpty) {
      return const SizedBox.shrink();
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Kos Trending', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppTheme.textPrimary)),
                SizedBox(height: 2),
                Text('Kos paling laris & banyak dicari', style: TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
              ],
            ),
            GestureDetector(
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const KatalogScreen())),
              child: const Row(
                children: [
                  Text('Lihat Semua', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.primary)),
                  Icon(Icons.chevron_right_rounded, color: AppTheme.primary, size: 18),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 250,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            itemCount: _propertis.length,
            itemBuilder: (context, index) => _buildKosCard(_propertis[index]),
          ),
        ),
      ],
    );
  }

  Widget _buildKosCard(Properti properti) {
    final kamarTersedia = properti.kamarTersedia;
    final kamarTerisi = properti.totalKamar - kamarTersedia;
    return GestureDetector(
      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => DetailKosScreen(propertiId: properti.id))),
      child: Container(
        width: 200,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.borderLight),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                Container(
                  height: 132,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(colors: [Color(0xFFFFF7ED), Color(0xFFFFE4D6)]),
                    borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                  ),
                  child: properti.foto != null
                      ? ClipRRect(
                          borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                          child: CachedNetworkImage(
                            imageUrl: properti.foto!,
                            width: double.infinity,
                            height: 132,
                            fit: BoxFit.cover,
                            errorWidget: (_, _, _) => const Center(child: Icon(Icons.home_rounded, size: 36, color: AppTheme.primary)),
                          ),
                        )
                      : const Center(child: Icon(Icons.home_rounded, size: 36, color: AppTheme.primary)),
                ),
                Positioned(
                  top: 8,
                  left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFF97316)]),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.local_fire_department_rounded, size: 12, color: Colors.white),
                        SizedBox(width: 2),
                        Text('Trending', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: Colors.white)),
                      ],
                    ),
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: kamarTersedia > 0 ? AppTheme.accent : Colors.grey[800],
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      kamarTersedia > 0 ? '$kamarTersedia Kamar' : 'Penuh',
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(properti.nama, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.textPrimary), maxLines: 1, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.location_on_rounded, size: 12, color: AppTheme.textSecondary),
                      const SizedBox(width: 3),
                      Expanded(
                        child: Text('${properti.kota}${properti.alamat.isNotEmpty ? ', ${properti.alamat}' : ''}', style: const TextStyle(fontSize: 10.5, color: AppTheme.textSecondary), maxLines: 1, overflow: TextOverflow.ellipsis),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                    decoration: BoxDecoration(
                      color: const Color(0xFFECFDF5),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('$kamarTerisi/${properti.totalKamar} terisi', style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppTheme.primary)),
                        const Icon(Icons.trending_up_rounded, size: 13, color: AppTheme.primary),
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
}