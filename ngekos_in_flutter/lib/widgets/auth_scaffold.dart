import 'package:flutter/material.dart';
import '../config/theme.dart';

class AuthScaffold extends StatelessWidget {
  final Widget child;

  const AuthScaffold({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
          child: Column(
            children: [
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
              const Text.rich(
                TextSpan(
                  text: 'Ngekos',
                  style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: AppTheme.textPrimary),
                  children: [
                    TextSpan(text: '.in', style: TextStyle(color: AppTheme.primary)),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(color: Colors.grey.withValues(alpha: 0.15), blurRadius: 24, offset: const Offset(0, 8)),
                  ],
                  border: Border.all(color: const Color(0xFFF3F4F6)),
                ),
                child: child,
              ),
              const SizedBox(height: 24),
              const Text(
                '© 2026 Ngekos.in — Sistem Manajemen Kos',
                style: TextStyle(fontSize: 12, color: AppTheme.textMuted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
