import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../widgets/bottom_nav.dart';
import 'katalog/katalog_screen.dart';
import 'chat/chat_list_screen.dart';
import 'akun/akun_screen.dart';
import 'dashboard/anak_kos_dashboard_screen.dart';
import 'dashboard/pemilik_dashboard_screen.dart';
import 'dashboard/admin_dashboard_screen.dart';
import 'dashboard/super_admin_dashboard_screen.dart';
import 'management/properti_list_screen.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    final isPemilik = user?.isPemilik ?? false;
    final isAdmin = user?.peran == 'admin';
    final isSuperAdmin = user?.peran == 'super_admin';
    final showKelola = isPemilik || isAdmin || isSuperAdmin;

    final kelolaActive = showKelola ? _currentIndex == 3 : false;

    Widget dashboardScreen;
    if (isSuperAdmin) {
      dashboardScreen = const SuperAdminDashboardScreen();
    } else if (isAdmin) {
      dashboardScreen = const AdminDashboardScreen();
    } else if (isPemilik) {
      dashboardScreen = PemilikDashboardScreen(isActive: _currentIndex == 0);
    } else {
      dashboardScreen = AnakKosDashboardScreen(isActive: _currentIndex == 0);
    }

    final screens = [
      dashboardScreen,
      const KatalogScreen(),
      if (!isAdmin && !isSuperAdmin) ChatListScreen(isActive: _currentIndex == 2) else const SizedBox.shrink(),
      if (showKelola) PropertiListScreen(isActive: kelolaActive),
      const AkunScreen(),
    ];

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex.clamp(0, screens.length - 1),
        children: screens,
      ),
      bottomNavigationBar: BottomNav(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() => _currentIndex = index);
          if (index == screens.length - 1) {
            context.read<AuthProvider>().refreshUser();
          }
        },
        showKelola: showKelola,
        avatar: user?.avatar,
        inisial: user?.inisial,
        unreadCount: user?.pesanBelumDibaca ?? 0,
      ),
    );
  }
}
