import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../config/theme.dart';
import '../utils/koordinat.dart';

/// Peta reusable untuk menampilkan lokasi sebuah kos.
///
/// - Jika `latitude` & `longitude` tersedia, pin lokasi ditampilkan.
/// - Jika tidak, fallback ke koordinat pusat kota lewat `kota`.
class KosMap extends StatelessWidget {
  final double? latitude;
  final double? longitude;
  final String? kota;
  final String nama;
  final bool interactive;

  const KosMap({
    super.key,
    this.latitude,
    this.longitude,
    this.kota,
    this.nama = '',
    this.interactive = false,
  });

  LatLng? get _center {
    if (latitude != null && longitude != null) {
      return LatLng(latitude!, longitude!);
    }
    return koordinatKota(kota);
  }

  @override
  Widget build(BuildContext context) {
    final center = _center ??
        (interactive ? const LatLng(-2.0, 117.0) : null);
    final hasLocation = center != null;

    if (!hasLocation) {
      return Container(
        height: 180,
        decoration: BoxDecoration(
          color: AppTheme.bg,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppTheme.bdrLight),
        ),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.map_rounded, size: 34),
              const SizedBox(height: 8),
              Text('Lokasi belum diisi',
                  style: TextStyle(
                      fontSize: 12, color: AppTheme.txtMuted)),
            ],
          ),
        ),
      );
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: FlutterMap(
        options: MapOptions(
          initialCenter: center,
          initialZoom: 15,
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
          if (hasLocation)
            MarkerLayer(
              markers: [
                Marker(
                  point: center,
                  width: 45,
                  height: 45,
                  child: const Icon(
                    Icons.location_pin,
                    color: AppTheme.primary,
                    size: 45,
                    shadows: [
                      Shadow(
                        color: Colors.black45,
                        blurRadius: 4,
                        offset: Offset(0, 2),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          RichAttributionWidget(
            attributions: [
              TextSourceAttribution(
                'OpenStreetMap contributors',
                onTap: () {},
              ),
            ],
          ),
        ],
      ),
    );
  }
}
