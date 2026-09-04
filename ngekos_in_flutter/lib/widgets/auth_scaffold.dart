import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/theme.dart';
import '../providers/theme_provider.dart';

class AuthScaffold extends StatelessWidget {
  final Widget child;

  const AuthScaffold({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
          child: Column(
            children: [
              Align(
                alignment: Alignment.centerRight,
                child: _AuthThemeToggle(),
              ),
              const SizedBox(height: 8),
              Container(
                height: 56,
                width: 56,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: AppTheme.heroGradient,
                ),
                child: const Icon(Icons.home_rounded, color: Colors.white, size: 28),
              ),
              const SizedBox(height: 16),
              Text.rich(
                TextSpan(
                  text: 'Ngekos',
                  style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: AppTheme.txt),
                  children: [
                    TextSpan(text: '.in', style: const TextStyle(color: AppTheme.primary)),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: AppTheme.card,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 24, offset: const Offset(0, 8)),
                  ],
                  border: Border.all(color: AppTheme.bdrLight),
                ),
                child: child,
              ),
              const SizedBox(height: 24),
              Text(
                'Ac 2026 Ngekos.in - Sistem Manajemen Kos',
                style: TextStyle(fontSize: 12, color: AppTheme.txtMuted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _AuthThemeToggle extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final theme = context.watch<ThemeProvider>();
    final isDark = theme.isDark;
    return GestureDetector(
      onTap: theme.toggle,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: AppTheme.surfaceC,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.bdrLight),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
                size: 18, color: AppTheme.primary),
            const SizedBox(width: 6),
            Text(isDark ? 'Gelap' : 'Terang',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.txt)),
          ],
        ),
      ),
    );
  }
}
