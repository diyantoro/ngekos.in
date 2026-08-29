import 'dart:async';
import 'package:flutter/material.dart';
import '../config/theme.dart';

class PromoAdsBanner extends StatefulWidget {
  const PromoAdsBanner({super.key});

  @override
  State<PromoAdsBanner> createState() => _PromoAdsBannerState();
}

class _PromoAdsBannerState extends State<PromoAdsBanner> {
  final PageController _controller = PageController();
  Timer? _timer;
  int _current = 0;

  static const List<Map<String, dynamic>> _ads = [
    {
      'brand': 'Biznet',
      'tagline': 'Internet Cepat & Stabil',
      'desc': 'Pasang Biznet Home, kuliah online dan streaming di kos makin lancar.',
      'colors': [Color(0xFF00509E), Color(0xFF0087EA)],
      'icon': Icons.wifi_rounded,
    },
    {
      'brand': 'Shopee',
      'tagline': 'Belanja Online Murah',
      'desc': 'Voucher gratis ongkir dan cashback untuk kebutuhan kos kamu.',
      'colors': [Color(0xFFF53D2D), Color(0xFFEE4D2D)],
      'icon': Icons.shopping_bag_rounded,
    },
    {
      'brand': 'GoFood',
      'tagline': 'Lapar di Kos?',
      'desc': 'Pesan makanan favorit, GoFood antar sampai depan kosmu.',
      'colors': [Color(0xFF00AA13), Color(0xFF00CE25)],
      'icon': Icons.delivery_dining_rounded,
    },
    {
      'brand': 'DANA',
      'tagline': 'Bayar Praktis',
      'desc': 'Top up DANA untuk bayar tagihan kos dan jajan harian.',
      'colors': [Color(0xFF0772B4), Color(0xFF1493FF)],
      'icon': Icons.account_balance_wallet_rounded,
    },
    {
      'brand': 'IndiHome',
      'tagline': 'Internet + TV di Kos',
      'desc': 'Pasang IndiHome, nonton dan internetan bareng teman kos.',
      'colors': [Color(0xFFB01E23), Color(0xFFE31E24)],
      'icon': Icons.live_tv_rounded,
    },
    {
      'brand': 'IKEA',
      'tagline': 'Furnitur Kamar Kos',
      'desc': 'Perabot IKEA harga bersahabat, kamar kos makin nyaman.',
      'colors': [Color(0xFF0058A3), Color(0xFF0F7AC9)],
      'icon': Icons.chair_rounded,
    },
  ];

  @override
  void initState() {
    super.initState();
    _startAutoScroll();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  void _startAutoScroll() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (!mounted || !_controller.hasClients) return;
      final next = (_current + 1) % _ads.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 500),
        curve: Curves.easeInOut,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        SizedBox(
          height: 132,
          child: PageView.builder(
            controller: _controller,
            itemCount: _ads.length,
            onPageChanged: (i) => setState(() => _current = i),
            itemBuilder: (context, index) => _buildAd(_ads[index]),
          ),
        ),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(_ads.length, (i) {
            final active = i == _current;
            return AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: active ? 16 : 6,
              height: 6,
              decoration: BoxDecoration(
                color: active ? AppTheme.primary : AppTheme.primary.withValues(alpha: 0.25),
                borderRadius: BorderRadius.circular(3),
              ),
            );
          }),
        ),
      ],
    );
  }

  Widget _buildAd(Map<String, dynamic> ad) {
    final colors = (ad['colors'] as List).cast<Color>();
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 2),
      child: Container(
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(colors: colors, begin: Alignment.centerLeft, end: Alignment.centerRight),
        ),
        child: Stack(
          children: [
            Positioned(
              top: -24,
              right: -10,
              child: Container(width: 90, height: 90, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.12), shape: BoxShape.circle)),
            ),
            Positioned(
              bottom: -30,
              right: 30,
              child: Container(width: 70, height: 70, decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.08), shape: BoxShape.circle)),
            ),
            Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Row(
                          children: [
                            Text(ad['brand'] as String, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Colors.white)),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                              decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.22), borderRadius: BorderRadius.circular(4)),
                              child: const Text('Iklan', style: TextStyle(fontSize: 8, fontWeight: FontWeight.w700, color: Colors.white, letterSpacing: 0.5)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(ad['tagline'] as String, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.white)),
                        const SizedBox(height: 2),
                        Text(
                          ad['desc'] as String,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(fontSize: 10.5, color: Colors.white.withValues(alpha: 0.88), height: 1.3),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                    ),
                    child: Icon(ad['icon'] as IconData, color: Colors.white, size: 28),
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