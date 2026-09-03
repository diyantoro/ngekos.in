import '../config/api_config.dart';
import '../utils/json.dart';

class Penyewaan {
  final int id;
  final String? kamar;
  final String? properti;
  final String? propertiFoto;
  final DateTime? tanggalMasuk;
  final DateTime? tanggalKeluar;
  final DateTime? permintaanKeluarPada;
  final String status;

  Penyewaan({
    required this.id,
    this.kamar,
    this.properti,
    this.propertiFoto,
    this.tanggalMasuk,
    this.tanggalKeluar,
    this.permintaanKeluarPada,
    required this.status,
  });

  factory Penyewaan.fromJson(Map<String, dynamic> json) {
    return Penyewaan(
      id: jsonInt(json['id'], 0),
      kamar: json['kamar'],
      properti: json['properti'],
      propertiFoto: ApiConfig.resolveStorageUrl(json['properti_foto']),
      tanggalMasuk: json['tanggal_masuk'] != null
          ? DateTime.parse(json['tanggal_masuk'])
          : null,
      tanggalKeluar: json['tanggal_keluar'] != null
          ? DateTime.parse(json['tanggal_keluar'])
          : null,
      permintaanKeluarPada: json['permintaan_keluar_pada'] != null
          ? DateTime.parse(json['permintaan_keluar_pada'])
          : null,
      status: json['status'] ?? '',
    );
  }
}
