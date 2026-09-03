import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import '../config/theme.dart';

class FlLineData {
  final String label;
  final List<double> values;
  final Color color;

  const FlLineData({
    required this.label,
    required this.values,
    required this.color,
  });
}

class DashboardLineChart extends StatelessWidget {
  const DashboardLineChart({
    super.key,
    required this.title,
    required this.labels,
    required this.series,
    this.height = 180,
    this.yCurrency = false,
  });

  final String title;
  final List<String> labels;
  final List<FlLineData> series;
  final double height;
  final bool yCurrency;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.borderLight),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.textPrimary)),
          const SizedBox(height: 16),
          SizedBox(
            height: height,
            child: LineChart(
              LineChartData(
                minY: 0,
                lineTouchData: LineTouchData(
                  touchTooltipData: LineTouchTooltipData(
                    tooltipBorderRadius: BorderRadius.circular(12),
                    getTooltipColor: (_) => const Color(0xF2FFFFFF),
                    tooltipBorder: const BorderSide(color: Color(0xFFE5E7EB)),
                    getTooltipItems: (touchedSpots) {
                      final title = labels.isNotEmpty &&
                              touchedSpots.isNotEmpty &&
                              touchedSpots.first.x >= 0 &&
                              touchedSpots.first.x < labels.length
                          ? labels[touchedSpots.first.x.toInt()]
                          : '';
                      return touchedSpots.map((spot) {
                        final line = series.isNotEmpty &&
                                spot.barIndex >= 0 &&
                                spot.barIndex < series.length
                            ? series[spot.barIndex]
                            : null;
                        return LineTooltipItem(
                          '${line?.label ?? ''}\n$title\n${yCurrency ? AppTheme.formatRupiah(spot.y.round()) : spot.y.toStringAsFixed(0)}',
                          const TextStyle(
                            color: AppTheme.textPrimary,
                            fontWeight: FontWeight.w600,
                            fontSize: 11,
                          ),
                        );
                      }).toList();
                    },
                  ),
                ),
                gridData: FlGridData(
                  show: true,
                  drawVerticalLine: false,
                  getDrawingHorizontalLine: (value) =>
                      const FlLine(color: Color(0x0A000000), strokeWidth: 1),
                ),
                titlesData: FlTitlesData(
                  leftTitles: const AxisTitles(
                    sideTitles: SideTitles(showTitles: false),
                  ),
                  rightTitles: const AxisTitles(
                    sideTitles: SideTitles(showTitles: false),
                  ),
                  topTitles: const AxisTitles(
                    sideTitles: SideTitles(showTitles: false),
                  ),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      reservedSize: 28,
                      interval: 1,
                      getTitlesWidget: (value, meta) {
                        final i = value.toInt();
                        if (i < 0 || i >= labels.length) {
                          return const SizedBox.shrink();
                        }
                        return Padding(
                          padding: const EdgeInsets.only(top: 6),
                          child: Text(
                            labels[i],
                            style: const TextStyle(fontSize: 9, color: AppTheme.textMuted),
                          ),
                        );
                      },
                    ),
                  ),
                ),
                borderData: FlBorderData(show: false),
                lineBarsData: series.map((s) {
                  return LineChartBarData(
                    spots: [
                      for (var i = 0; i < s.values.length; i++)
                        FlSpot(i.toDouble(), s.values[i]),
                    ],
                    isCurved: true,
                    curveSmoothness: 0.3,
                    color: s.color,
                    barWidth: 2.5,
                    isStrokeCapRound: true,
                    dotData: const FlDotData(show: false),
                    belowBarData: BarAreaData(
                      show: true,
                      color: s.color.withValues(alpha: 0.08),
                    ),
                    showingIndicators: const [],
                  );
                }).toList(),
              ),
            ),
          ),
          if (series.length > 1)
            Padding(
              padding: const EdgeInsets.only(top: 12),
              child: Row(
                children: series.map((s) {
                  return Padding(
                    padding: const EdgeInsets.only(right: 16),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 10,
                          height: 10,
                          decoration: BoxDecoration(color: s.color, shape: BoxShape.circle),
                        ),
                        const SizedBox(width: 6),
                        Text(s.label, style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary)),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),
        ],
      ),
    );
  }
}
