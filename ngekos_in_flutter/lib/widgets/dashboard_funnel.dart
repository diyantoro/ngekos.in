import 'package:flutter/material.dart';
import '../config/theme.dart';

class FunnelStage {
  final String label;
  final String? sub;
  final int nilai;

  const FunnelStage({required this.label, this.sub, required this.nilai});
}

class DashboardFunnel extends StatefulWidget {
  const DashboardFunnel({
    super.key,
    required this.title,
    required this.subtitle,
    required this.stages,
  });

  final String title;
  final String subtitle;
  final List<FunnelStage> stages;

  @override
  State<DashboardFunnel> createState() => _DashboardFunnelState();
}

class _DashboardFunnelState extends State<DashboardFunnel>
    with TickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _grow;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..forward();
    _grow = CurvedAnimation(parent: _controller, curve: Curves.easeOutCubic);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final stages = widget.stages;
    final maxVal = stages
        .map((s) => s.nilai)
        .fold<int>(1, (a, b) => b > a ? b : a);
    final colors = [
      AppTheme.primary,
      const Color(0xFF10B981),
      AppTheme.accent,
      const Color(0xFF0E7490),
    ];

    String fmt(int n) {
      final s = n.toString();
      final buf = StringBuffer();
      for (var i = 0; i < s.length; i++) {
        if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
        buf.write(s[i]);
      }
      return buf.toString();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color:
            AppTheme.isDark ? const Color(0xFF122224) : const Color(0xFFF6FDFB),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.primary.withValues(alpha: 0.22)),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primary.withValues(alpha: 0.12),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            top: -50,
            right: -40,
            child: Container(
              width: 150,
              height: 150,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    AppTheme.primary.withValues(alpha: AppTheme.isDark ? 0.16 : 0.18),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -60,
            left: -30,
            child: Container(
              width: 160,
              height: 160,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    AppTheme.accent
                        .withValues(alpha: AppTheme.isDark ? 0.10 : 0.14),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 4,
                    height: 18,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          AppTheme.primary,
                          AppTheme.primary.withValues(alpha: 0.2),
                        ],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                      borderRadius: BorderRadius.circular(4),
                      boxShadow: [
                        BoxShadow(
                          color: AppTheme.primary.withValues(alpha: 0.6),
                          blurRadius: 8,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(widget.title,
                            style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                color: AppTheme.txt)),
                        if (widget.subtitle.isNotEmpty)
                          Text(widget.subtitle,
                              style: TextStyle(
                                  fontSize: 10, color: AppTheme.txtMuted)),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              if (stages.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 24),
                    child: Text('Belum ada data pipeline',
                        style:
                            TextStyle(fontSize: 12, color: AppTheme.txtMuted)),
                  ),
                )
              else
                AnimatedBuilder(
                  animation: _grow,
                  builder: (context, _) {
                    return Column(
                      children: [
                        for (var i = 0; i < stages.length; i++) ...[
                          if (i > 0) _conversionBadge(stages[i], stages[i - 1]),
                          _stageRow(
                              stages[i], colors[i % colors.length], maxVal,
                              fmt: fmt),
                          const SizedBox(height: 12),
                        ],
                      ],
                    );
                  },
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _conversionBadge(FunnelStage current, FunnelStage prev) {
    final pct = prev.nilai > 0 ? (current.nilai / prev.nilai * 100).round() : null;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8, left: 20),
      child: Row(
        children: [
          Expanded(
            child: Container(
              height: 14,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: AppTheme.isDark
                    ? Colors.white.withValues(alpha: 0.04)
                    : Colors.black.withValues(alpha: 0.03),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.south_rounded,
                      size: 12, color: AppTheme.primary),
                  const SizedBox(width: 2),
                  Text(pct == null ? '-' : '$pct%',
                      style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.txtSec)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _stageRow(FunnelStage stage, Color color, int maxVal,
      {required String Function(int) fmt}) {
    final pct = stage.nilai > 0 ? (stage.nilai / maxVal) : (maxVal <= 0 ? 0.04 : 0.04);
    final width = pct.clamp(0.03, 1.0).toDouble();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width: 24,
              height: 24,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(7),
              ),
              child: Text(
                stage.label.isEmpty ? '' : stage.label.characters.first,
                style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: color),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(stage.label,
                  style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.txt)),
            ),
            if (stage.sub != null)
              Flexible(
                child: Text(stage.sub!,
                    overflow: TextOverflow.ellipsis,
                    maxLines: 1,
                    style: TextStyle(
                        fontSize: 9, color: AppTheme.txtMuted)),
              ),
            const SizedBox(width: 8),
            Text(fmt(stage.nilai),
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.txt)),
          ],
        ),
        const SizedBox(height: 6),
        LayoutBuilder(
          builder: (context, constraints) {
            return AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              curve: Curves.easeOut,
              height: 14,
              width: constraints.maxWidth * width,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(20),
                gradient: LinearGradient(
                  colors: [color, color.withValues(alpha: 0.65)],
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                ),
                boxShadow: [
                  BoxShadow(
                    color: color.withValues(alpha: 0.4),
                    blurRadius: 10,
                  ),
                ],
              ),
            );
          },
        ),
      ],
    );
  }
}