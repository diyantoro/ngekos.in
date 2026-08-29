import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:ngekos_in/providers/auth_provider.dart';
import 'package:ngekos_in/screens/katalog/detail_kos_screen.dart';
import 'package:ngekos_in/services/api_service.dart';

Map<String, dynamic> _detail() {
  return {
    'id': 1,
    'nama': 'Kos Melati',
    'kota': 'Bandung',
    'alamat': 'Jl. Melati No. 12, Bandung',
    'deskripsi': 'Kos bersih dekat kampus.',
    'fasilitas': const ['WiFi', 'AC'],
    'aturan': 'Jam malam 23.00',
    'denda_per_hari': 5000,
    'jenis_harga': 'bulanan',
    'status': 'aktif',
    'foto': null,
    'total_kamar': 3,
    'kamar_tersedia': 2,
    'pemilik': {'id': 2, 'nama': 'Pemilik Budi', 'no_hp': '081234567002'},
    'kamars': [
      {'id': 1, 'nama': 'A1', 'kapasitas': 1, 'harga_sewa_bulanan': 1000000, 'jenis_harga': 'bulanan', 'status': 'tersedia', 'foto': null},
      {'id': 2, 'nama': 'A2', 'kapasitas': 1, 'harga_sewa_bulanan': 1000000, 'jenis_harga': 'bulanan', 'status': 'terisi', 'foto': null},
      {'id': 3, 'nama': 'A3', 'kapasitas': 2, 'harga_sewa_bulanan': 1200000, 'jenis_harga': 'bulanan', 'status': 'tersedia', 'foto': null},
    ],
  };
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
    ApiService.client = MockClient((request) async {
      return http.Response(jsonEncode(_detail()), 200,
          headers: {'content-type': 'application/json'});
    });
  });

  testWidgets('detail kos renders without exception (guest)', (tester) async {
    await tester.pumpWidget(
      ChangeNotifierProvider<AuthProvider>(
        create: (_) => AuthProvider(),
        child: const MaterialApp(home: DetailKosScreen(propertiId: 1)),
      ),
    );
    await tester.pumpAndSettle(const Duration(milliseconds: 100));

    final e = tester.takeException();
    expect(e, isNull, reason: 'DetailKosScreen threw: $e');
  });
}
