import 'package:flutter/material.dart';
import '../config/theme.dart';

class FacilityChip extends StatelessWidget {
  final String label;
  final IconData? icon;
  final bool showCheckbox;
  final bool isSelected;
  final VoidCallback? onTap;

  const FacilityChip({
    super.key,
    required this.label,
    this.icon,
    this.showCheckbox = false,
    this.isSelected = false,
    this.onTap,
  });

  static final Map<String, IconData> facilityIcons = {
    'wifi': Icons.wifi_rounded,
    'ac': Icons.ac_unit_rounded,
    'kasur': Icons.king_bed_rounded,
    'dapur': Icons.kitchen_rounded,
    'kamar_mandi': Icons.bathtub_rounded,
    'kamar_mandi_dalam': Icons.bathtub_rounded,
    'laundry': Icons.local_laundry_service_rounded,
    'parkir': Icons.local_parking_rounded,
    'cuci': Icons.local_laundry_service_rounded,
    'jemur': Icons.wb_sunny_rounded,
    'gym': Icons.fitness_center_rounded,
    'kolam': Icons.pool_rounded,
    'kolam_renang': Icons.pool_rounded,
    'security': Icons.security_rounded,
    'cctv': Icons.videocam_rounded,
    'listrik': Icons.electrical_services_rounded,
    'air': Icons.water_drop_rounded,
    'gas': Icons.local_fire_department_rounded,
    'internet': Icons.language_rounded,
    'tv': Icons.tv_rounded,
    'rak_baju': Icons.checkroom_rounded,
  };

  static IconData _iconFor(String label) {
    final iconFromKey = iconForKey(label);
    return iconFromKey ?? Icons.home_rounded;
  }

  static IconData? iconForKey(String label) {
    // Sesuaikan format label ("Kamar Mandi Dalam") dengan kunci map
    // ("kamar_mandi_dalam") — huruf kecil & spasi diganti underscore.
    final key = label.trim().toLowerCase().replaceAll(' ', '_');
    return facilityIcons[key] ?? facilityIcons[label.trim().toLowerCase()];
  }

  @override
  Widget build(BuildContext context) {
    if (showCheckbox) {
      return GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFFF0FDFA) : AppTheme.card,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected ? AppTheme.primary : AppTheme.bdr,
              width: isSelected ? 2 : 1,
            ),
          ),
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Stack(
                    children: [
                      Icon(
                        icon ?? _iconFor(label),
                        color: isSelected ? AppTheme.primary : AppTheme.txtSec,
                        size: 26,
                      ),
                      if (isSelected)
                        Positioned(
                          right: -4,
                          bottom: -4,
                          child: Container(
                            decoration: const BoxDecoration(
                              color: AppTheme.primary,
                              shape: BoxShape.circle,
                              border: Border.fromBorderSide(
                                BorderSide(color: Colors.white, width: 1.5),
                              ),
                            ),
                            padding: const EdgeInsets.all(1),
                            child: const Icon(
                              Icons.check_rounded,
                              size: 12,
                              color: Colors.white,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    label,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500,
                      color: isSelected ? AppTheme.primary : AppTheme.txtSec,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ],
          ),
        ),
      );
    }

    final facIcon = iconForKey(label);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDFA),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null || facIcon != null) ...[
            Icon(
              icon ?? facIcon!,
              size: 14,
              color: AppTheme.primary,
            ),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: const TextStyle(fontSize: 13, color: AppTheme.primary),
          ),
        ],
      ),
    );
  }
}