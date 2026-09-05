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
      'colors': [Color(0xFF00509E), Color(0xFF0087EA), Color(0xFF41C6FF)],
      'icon': Icons.wifi_rounded,
    },
    {
      'brand': 'Shopee',
      'tagline': 'Belanja Online Murah',
      'desc': 'Voucher gratis ongkir dan cashback untuk kebutuhan kos kamu.',
      'colors': [Color(0xFFEE4D2D), Color(0xFFF53D2D), Color(0xFFFFC23E)],
      'icon': Icons.shopping_bag_rounded,
    },
    {
      'brand': 'GoFood',
      'tagline': 'Lapar di Kos?',
      'desc': 'Pesan makanan favorit, GoFood antar sampai depan kosmu.',
      'colors': [Color(0xFF008A10), Color(0xFF00CE25), Color(0xFF00E0A0)],
      'icon': Icons.delivery_dining_rounded,
    },
    {
      'brand': 'DANA',
      'tagline': 'Bayar Praktis',
      'desc': 'Top up DANA untuk bayar tagihan kos dan jajan harian.',
      'colors': [Color(0xFF0772B4), Color(0xFF1493FF), Color(0xFF57C4FF)],
      'icon': Icons.account_balance_wallet_rounded,
    },
    {
      'brand': 'IndiHome',
      'tagline': 'Internet + TV di Kos',
      'desc': 'Pasang IndiHome, nonton dan internetan bareng teman kos.',
      'colors': [Color(0xFFB01E23), Color(0xFFE31E24), Color(0xFFFF6B6B)],
      'icon': Icons.live_tv_rounded,
    },
    {
      'brand': 'IKEA',
      'tagline': 'Furnitur Kamar Kos',
      'desc': 'Perabot IKEA harga bersahabat, kamar kos makin nyaman.',
      'colors': [Color(0xFF00468F), Color(0xFF0F7AC9), Color(0xFF0FB5D6)],
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
    _timer = Timer.periodic(const Duration(milliseconds: 4500), (timer) {
      if (!mounted || !_controller.hasClients) return;
      final next = (_current + 1) % _ads.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 600),
        curve: Curves.easeInOutCubic,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        SizedBox(
          height: 148,
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
              width: active ? 18 : 6,
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
          borderRadius: BorderRadius.circular(20),
          gradient: LinearGradient(colors: colors, begin: Alignment.topLeft, end: Alignment.bottomRight),
          boxShadow: [
            BoxShadow(
              color: colors.last.withValues(alpha: 0.4),
              blurRadius: 22,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: Stack(
          children: [
            // dot pattern
            Positioned.fill(
              child: IgnorePointer(
                child: CustomPaint(painter: const _DotsPainter()),
              ),
            ),
            // glow orbs
            Positioned(
              top: -40,
              right: -30,
              child: Container(
                width: 130,
                height: 130,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [Colors.white.withValues(alpha: 0.22), Colors.white.withValues(alpha: 0)],
                  ),
                ),
              ),
            ),
            Positioned(
              bottom: -46,
              left: -24,
              child: Container(
                width: 140,
                height: 140,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [Colors.white.withValues(alpha: 0.14), Colors.white.withValues(alpha: 0)],
                  ),
                ),
              ),
            ),
            // rings
            Positioned(
              top: -14,
              right: 76,
              child: Container(width: 30, height: 30, decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(color: Colors.white.withValues(alpha: 0.25)))),
            ),
            Positioned(
              bottom: -18,
              right: 24,
              child: Container(width: 44, height: 44, decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(color: Colors.white.withValues(alpha: 0.18)))),
            ),
            // top sheen
            Positioned(
              top: 0,
              left: 0,
              right: 0,
              child: Container(
                height: 56,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [Colors.white.withValues(alpha: 0.12), Colors.white.withValues(alpha: 0)],
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.18),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.auto_awesome_rounded, size: 10, color: Colors.white),
                              SizedBox(width: 4),
                              Text('IKLAN PARTNER', style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: Colors.white, letterSpacing: 0.6)),
                            ],
                          ),
                        ),
                        const SizedBox(height: 9),
                        Text(ad['brand'] as String, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: 0.2)),
                        const SizedBox(height: 2),
                        Text(ad['tagline'] as String, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Colors.white)),
                        const SizedBox(height: 3),
                        Text(
                          ad['desc'] as String,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(fontSize: 10.5, color: Colors.white.withValues(alpha: 0.9), height: 1.3),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  Container(
                    width: 58,
                    height: 58,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.18),
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.4)),
                      boxShadow: [
                        BoxShadow(color: Colors.white.withValues(alpha: 0.35), blurRadius: 14, offset: const Offset(0, 4)),
                      ],
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

class _DotsPainter extends CustomPainter {
  const _DotsPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = Colors.white.withValues(alpha: 0.08);
    const spacing = 26.0;
    const radius = 1.1;
    for (double y = 0; y < size.height; y += spacing) {
      for (double x = 0; x < size.width; x += spacing) {
        canvas.drawCircle(Offset(x, y), radius, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}