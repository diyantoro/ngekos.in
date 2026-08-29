import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../config/theme.dart';

class BottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;
  final int unreadCount;
  final bool showKelola;
  final String? avatar;
  final String? inisial;

  const BottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
    this.unreadCount = 0,
    this.showKelola = false,
    this.avatar,
    this.inisial,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 12, offset: const Offset(0, -2)),
        ],
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _NavItem(
                icon: Icons.home_rounded,
                label: 'Beranda',
                isSelected: currentIndex == 0,
                onTap: () => onTap(0),
              ),
              _NavItem(
                icon: Icons.search_rounded,
                label: 'Cari Kos',
                isSelected: currentIndex == 1,
                onTap: () => onTap(1),
              ),
              _NavItem(
                icon: Icons.chat_bubble_rounded,
                label: 'Pesan',
                isSelected: currentIndex == 2,
                onTap: () => onTap(2),
                badge: unreadCount,
              ),
              if (showKelola)
                _NavItem(
                  icon: Icons.apartment_rounded,
                  label: 'Kelola',
                  isSelected: currentIndex == 3,
                  onTap: () => onTap(3),
                ),
              _NavItem(
                iconWidget: _buildAvatar(),
                label: 'Akun',
                isSelected: currentIndex == (showKelola ? 4 : 3),
                onTap: () => onTap(showKelola ? 4 : 3),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget? _buildAvatar() {
    if (avatar != null && avatar!.isNotEmpty) {
      return ClipOval(
        child: CachedNetworkImage(
          imageUrl: avatar!,
          width: 24,
          height: 24,
          fit: BoxFit.cover,
          errorWidget: (_, __, ___) => _buildInitialCircle(),
        ),
      );
    }
    return _buildInitialCircle();
  }

  Widget _buildInitialCircle() {
    return Container(
      width: 24,
      height: 24,
      decoration: const BoxDecoration(
        shape: BoxShape.circle,
        gradient: LinearGradient(
          colors: [AppTheme.primary, AppTheme.accent],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Center(
        child: Text(
          inisial ?? '?',
          style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white),
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData? icon;
  final Widget? iconWidget;
  final String label;
  final bool isSelected;
  final VoidCallback onTap;
  final int badge;

  const _NavItem({
    this.icon,
    this.iconWidget,
    required this.label,
    required this.isSelected,
    required this.onTap,
    this.badge = 0,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: SizedBox(
        width: 64,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                if (iconWidget != null)
                  SizedBox(
                    width: 24,
                    height: 24,
                    child: ColorFiltered(
                      colorFilter: isSelected
                          ? const ColorFilter.mode(AppTheme.primary, BlendMode.srcIn)
                          : const ColorFilter.mode(AppTheme.textMuted, BlendMode.srcIn),
                      child: iconWidget!,
                    ),
                  )
                else
                  Icon(
                    icon,
                    size: 24,
                    color: isSelected ? AppTheme.primary : AppTheme.textMuted,
                  ),
                if (badge > 0)
                  Positioned(
                    top: -4,
                    right: -8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                      decoration: const BoxDecoration(
                        color: AppTheme.rose,
                        shape: BoxShape.circle,
                      ),
                      constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                      child: Text(
                        badge > 9 ? '9+' : '$badge',
                        style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: isSelected ? AppTheme.primary : AppTheme.textMuted,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
