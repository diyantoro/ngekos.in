import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import '../config/theme.dart';

enum AvatarSize { xs, sm, md, lg, xl }

class UserAvatar extends StatelessWidget {
  final String? avatar;
  final String? inisial;
  final String? nama;
  final AvatarSize size;
  final Color? borderColor;

  const UserAvatar({
    super.key,
    this.avatar,
    this.inisial,
    this.nama,
    this.size = AvatarSize.md,
    this.borderColor,
  });

  double get _dimension {
    switch (size) {
      case AvatarSize.xs: return 24;
      case AvatarSize.sm: return 32;
      case AvatarSize.md: return 40;
      case AvatarSize.lg: return 56;
      case AvatarSize.xl: return 80;
    }
  }

  double get _fontSize {
    switch (size) {
      case AvatarSize.xs: return 10;
      case AvatarSize.sm: return 12;
      case AvatarSize.md: return 14;
      case AvatarSize.lg: return 18;
      case AvatarSize.xl: return 24;
    }
  }

  String get _initial {
    if (inisial != null && inisial!.isNotEmpty) return inisial!;
    if (nama != null && nama!.isNotEmpty) return nama![0].toUpperCase();
    return '?';
  }

  @override
  Widget build(BuildContext context) {
    if (avatar != null && avatar!.isNotEmpty) {
      return Container(
        width: _dimension,
        height: _dimension,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          border: borderColor != null
              ? Border.all(color: borderColor!, width: 2)
              : null,
        ),
        child: ClipOval(
          child: CachedNetworkImage(
            imageUrl: avatar!,
            imageRenderMethodForWeb: ImageRenderMethodForWeb.HttpGet,
            fit: BoxFit.cover,
            width: _dimension,
            height: _dimension,
            errorWidget: (_, _, _) => _buildFallback(),
          ),
        ),
      );
    }

    return _buildFallback();
  }

  Widget _buildFallback() {
    return Container(
      width: _dimension,
      height: _dimension,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: const LinearGradient(
          colors: [AppTheme.primary, AppTheme.accent],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        border: borderColor != null
            ? Border.all(color: borderColor!, width: 2)
            : null,
      ),
      child: Center(
        child: Text(
          _initial,
          style: TextStyle(
            fontSize: _fontSize,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
      ),
    );
  }
}
