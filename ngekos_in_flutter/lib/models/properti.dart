import '../utils/json.dart';
import 'kamar.dart';

class Properti {
  final int id;
  final String nama;
  final String kota;
  final String alamat;
  final String? deskripsi;
  final List<String>? fasilitas;
  final String? aturan;
  final int? dendaPerHari;
  final int? harga;
  final String? jenisHarga;
  final String status;
  final String? foto;
  final int totalKamar;
  final int kamarTersedia;
  final PemilikInfo? pemilik;
  final List<Kamar>? kamars;

  Properti({
    required this.id,
    required this.nama,
    required this.kota,
    required this.alamat,
    this.deskripsi,
    this.fasilitas,
    this.aturan,
    this.dendaPerHari,
    this.harga,
    this.jenisHarga,
    required this.status,
    this.foto,
    required this.totalKamar,
    required this.kamarTersedia,
    this.pemilik,
    this.kamars,
  });

  factory Properti.fromJson(Map<String, dynamic> json) {
    final totalKamar = jsonInt(json['total_kamar'], 0);
    final kamarTerisi = jsonInt(json['kamar_terisi'], 0);
    final kamarTersedia =
        jsonInt(json['kamar_tersedia'], totalKamar - kamarTerisi);
    return Properti(
      id: jsonInt(json['id'], 0),
      nama: json['nama'] ?? '',
      kota: json['kota'] ?? '',
      alamat: json['alamat'] ?? '',
      deskripsi: json['deskripsi'],
      fasilitas: json['fasilitas'] != null
          ? List<String>.from(json['fasilitas'] as List)
          : null,
      aturan: json['aturan'],
      dendaPerHari: jsonIntOrNull(json['denda_per_hari']),
      harga: jsonIntOrNull(json['harga']),
      jenisHarga: json['jenis_harga'],
      status: json['status'] ?? '',
      foto: json['foto'],
      totalKamar: totalKamar,
      kamarTersedia: kamarTersedia,
      pemilik: json['pemilik'] != null
          ? PemilikInfo.fromJson(json['pemilik'] as Map<String, dynamic>)
          : null,
      kamars: json['kamars'] != null
          ? (json['kamars'] as List).map((k) => Kamar.fromJson(k as Map<String, dynamic>)).toList()
          : null,
    );
  }
}

class PemilikInfo {
  final int id;
  final String nama;
  final String? noHp;

  PemilikInfo({required this.id, required this.nama, this.noHp});

  factory PemilikInfo.fromJson(Map<String, dynamic> json) {
    return PemilikInfo(
      id: jsonInt(json['id'], 0),
      nama: json['nama'] ?? '',
      noHp: json['no_hp'],
    );
  }
}
