import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';

import '../config/theme.dart';

/// Peta reusable untuk menampilkan lokasi sebuah kos.
///
/// - Jika `latitude` & `longitude` tersedia, pin lokasi ditampilkan.
/// - Jika tidak, fallback ke koordinat default kota Indonesia (-2.5, 118.0).
class KosMap extends StatelessWidget {
  final double? latitude;
  final double? longitude;
  final String nama;
  final bool interactive;

  const KosMap({
    super.key,
    this.latitude,
    this.longitude,
    this.nama = '',
    this.interactive = false,
  });

  LatLng? get _center {
    if (latitude != null && longitude != null) {
      return LatLng(latitude!, longitude!);
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final center = _center ?? const LatLng(-2.548926, 118.0148634);
    final hasLocation = _center != null;

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
