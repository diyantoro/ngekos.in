import 'package:flutter/material.dart';

/// Pemetaan ikon fasilitas berbasis gambar PNG (atau Material fallback).
///
/// Kelas ini dipakai oleh katalog/pemilik (mobile). Key dinormalisasi:
/// huruf kecil + hapus semua karakter non-alfanumerik (spasi, titik, garis
/// miring, ampersand, dsb.), sehingga label apa pun dari web, mobile, maupun
/// data lama tetap terpetakan ke gambar yang benar.
class FacilityIcon {
  FacilityIcon._();

  static const String _assetBase = 'assets/images/';

  /// Key ternormalisasi -> nama file PNG.
  static const Map<String, String> _assetByNorm = {
    'ac': 'ac.png',
    'akses': 'akses.png',
    'aman24jam': 'akses.png',
    'bantal': 'bantal.png',
    'cermin': 'cermin.png',
    'cucisetrika': 'akses.png',
    'dapur': 'dapur.png',
    'dapurbersama': 'dapur.png',
    'dilarangmerokok': 'dilarang_merokok.png',
    'dilarangmerokokdikamar': 'dilarang_merokok.png',
    'ember': 'ember.png',
    'embermandi': 'ember.png',
    'gym': 'akses.png',
    'jendela': 'jendela.png',
    'kamarmandidalam': 'kamar_mandi_dalam.png',
    'kmandidalam': 'kamar_mandi_dalam.png',
    'kamarmandiluar': 'kamar_mandi_luar.png',
    'kmandiluar': 'kamar_mandi_luar.png',
    'kasur': 'kasur.png',
    'kipas': 'akses.png',
    'kolamrenang': 'akses.png',
    'kulkas': 'kulkas.png',
    'kursi': 'kursi.png',
    'laundry': 'akses.png',
    'lawanjenis': 'lawan_jenis.png',
    'lawanjenisdilarangkekamar': 'lawan_jenis.png',
    'lemari': 'lemari.png',
    'lemaristorage': 'lemari.png',
    'listrik': 'tidak_listrik.png',
    'meja': 'meja.png',
    'mejakursi': 'meja.png',
    'parkir': 'parkir_mobil.png',
    'parkirmobil': 'parkir_mobil.png',
    'parkirmotor': 'parkir_motor_sepeda.png',
    'parkirmotorsepeda': 'parkir_motor_sepeda.png',
    'penjagakos': 'penjaga_kos.png',
    'rakbaju': 'lemari.png',
    'ruangtamu': 'ruang_tamu.png',
    'rtamu': 'ruang_tamu.png',
    'rjemur': 'tempat_jemuran.png',
    'shower': 'shower.png',
    'tamu': 'tamu.png',
    'tamubolehmenginap': 'tamu.png',
    'tamumenginapdikenakanbiaya': 'tamu.png',
    'tempatjemuran': 'tempat_jemuran.png',
    'tidaktermasuklistrik': 'tidak_listrik.png',
    'tidaklistrik': 'tidak_listrik.png',
    'toilet': 'kamar_mandi_dalam.png',
    'ventilasi': 'jendela.png',
    'wifi': 'wifi.png',
  };

  /// Key ternormalisasi -> ikon Material fallback (untuk fasilitas tanpa PNG).
  static const Map<String, IconData> _materialByNorm = {
    'kloset': Icons.wc_rounded,
    'klosetduduk': Icons.wc_rounded,
    'airpanas': Icons.local_fire_department_rounded,
    'termasuklistrik': Icons.electrical_services_rounded,
    'ventilasi': Icons.wind_power_rounded,
    'peraturankhusus': Icons.rule_rounded,
    'maks2orangkamar': Icons.group_rounded,
    'bolehpasutri': Icons.favorite_rounded,
    'wajibsertakansuratnikahsaatpengajuansewa': Icons.description_rounded,
    'tidakbolehbawaanak': Icons.child_friendly_rounded,
  };

  /// Normalkan label: huruf kecil + hapus semua karakter non-alfanumerik.
  static String _norm(String label) =>
      label.trim().toLowerCase().replaceAll(RegExp(r'[^a-z0-9]'), '');

  /// Path asset jika tersedia, jika tidak null.
  static String? assetFor(String label) {
    final file = _assetByNorm[_norm(label)];
    return file != null ? _assetBase + file : null;
  }

  /// Ikon Material fallback untuk label, jika tidak ada gambar.
  static IconData? materialFor(String label) => _materialByNorm[_norm(label)];
}
