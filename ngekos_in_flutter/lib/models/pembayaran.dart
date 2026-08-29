import '../utils/json.dart';

class Pembayaran {
  final int id;
  final String? periode;
  final String? metode;
  final int jumlah;
  final String? bukti;
  final String status;
  final String? diverifikasiOleh;
  final DateTime? verifiedAt;
  final DateTime? createdAt;

  Pembayaran({
    required this.id,
    this.periode,
    this.metode,
    required this.jumlah,
    this.bukti,
    required this.status,
    this.diverifikasiOleh,
    this.verifiedAt,
    this.createdAt,
  });

  factory Pembayaran.fromJson(Map<String, dynamic> json) {
    return Pembayaran(
      id: jsonInt(json['id'], 0),
      periode: json['periode'],
      metode: json['metode'],
      jumlah: jsonInt(json['jumlah'], 0),
      bukti: json['bukti'],
      status: json['status'] ?? '',
      diverifikasiOleh: json['diverifikasi_oleh'],
      verifiedAt: json['verified_at'] != null
          ? DateTime.parse(json['verified_at'])
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
    );
  }

  String get formattedJumlah {
    return 'Rp ${jumlah.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';
  }
}
