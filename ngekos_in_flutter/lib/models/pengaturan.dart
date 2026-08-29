import '../utils/json.dart';

class Pengaturan {
  final String? nama;
  final String? deskripsi;
  final String? email;
  final String? telepon;
  final String? alamat;
  final String? jatuhTempo;
  final int? dendaPerHari;

  Pengaturan({
    this.nama,
    this.deskripsi,
    this.email,
    this.telepon,
    this.alamat,
    this.jatuhTempo,
    this.dendaPerHari,
  });

  factory Pengaturan.fromApi(Map<String, dynamic> json) {
    final situs = json['situs'] as Map<String, dynamic>? ?? json;
    final kos = json['kos'] as Map<String, dynamic>? ?? json;
    return Pengaturan(
      nama: situs['nama'],
      deskripsi: situs['deskripsi'],
      email: situs['email'],
      telepon: situs['telepon'],
      alamat: situs['alamat'],
      jatuhTempo: kos['jatuh_tempo'],
      dendaPerHari: jsonIntOrNull(kos['denda_per_hari']),
    );
  }
}