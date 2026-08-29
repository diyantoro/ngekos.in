import '../utils/json.dart';

class Tagihan {
  final int id;
  final String? periode;
  final String? kamar;
  final String? properti;
  final int jumlah;
  final int denda;
  final DateTime? jatuhTempo;
  final String status;

  Tagihan({
    required this.id,
    this.periode,
    this.kamar,
    this.properti,
    required this.jumlah,
    this.denda = 0,
    this.jatuhTempo,
    required this.status,
  });

  factory Tagihan.fromJson(Map<String, dynamic> json) {
    return Tagihan(
      id: jsonInt(json['id'], 0),
      periode: json['periode'],
      kamar: json['kamar'],
      properti: json['properti'],
      jumlah: jsonInt(json['jumlah'], 0),
      denda: jsonInt(json['denda'], 0),
      jatuhTempo: json['jatuh_tempo'] != null
          ? DateTime.parse(json['jatuh_tempo'])
          : null,
      status: json['status'] ?? '',
    );
  }

  String get formattedJumlah {
    return 'Rp ${jumlah.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}';
  }
}
