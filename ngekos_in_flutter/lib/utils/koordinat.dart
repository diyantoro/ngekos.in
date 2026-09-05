import 'package:latlong2/latlong.dart';
import '../models/properti.dart';

const Map<String, LatLng> _pusatKota = {
  'Jakarta': LatLng(-6.2088, 106.8456),
  'Bandung': LatLng(-6.9175, 107.6191),
  'Bogor': LatLng(-6.5971, 106.806),
  'Depok': LatLng(-6.4025, 106.7942),
  'Bekasi': LatLng(-6.2383, 106.9756),
  'Tangerang': LatLng(-6.1751, 106.6302),
  'Yogyakarta': LatLng(-7.7956, 110.3695),
  'Sleman': LatLng(-7.6933, 110.359),
  'Bantul': LatLng(-7.8881, 110.3284),
  'Janti': LatLng(-7.7956, 110.3695),
  'Palagan': LatLng(-7.741, 110.368),
  'Semarang': LatLng(-6.9667, 110.4167),
  'Surabaya': LatLng(-7.2575, 112.7521),
  'Malang': LatLng(-7.9797, 112.6304),
  'Ngawi': LatLng(-7.4039, 111.4483),
  'Solo': LatLng(-7.5755, 110.8263),
  'Surakarta': LatLng(-7.5755, 110.8263),
  'Medan': LatLng(3.5952, 98.6722),
  'Makassar': LatLng(-5.1477, 119.4327),
  'Denpasar': LatLng(-8.6705, 115.2126),
};

LatLng? koordinatKota(String? kota) {
  if (kota == null || kota.trim().isEmpty) return null;
  return _pusatKota[kota.trim()];
}

LatLng? koordinatProperti(Properti p) {
  if (p.latitude != null && p.longitude != null) {
    return LatLng(p.latitude!, p.longitude!);
  }
  return koordinatKota(p.kota);
}