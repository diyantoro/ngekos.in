import '../utils/json.dart';

class Kamar {
  final int id;
  final String nama;
  final int kapasitas;
  final int hargaSewaBulanan;
  final String? jenisHarga;
  final String status;
  final String? foto;

  Kamar({
    required this.id,
    required this.nama,
    required this.kapasitas,
    required this.hargaSewaBulanan,
    this.jenisHarga,
    required this.status,
    this.foto,
  });

  factory Kamar.fromJson(Map<String, dynamic> json) {
    return Kamar(
      id: jsonInt(json['id'], 0),
      nama: json['nama'] ?? '',
      kapasitas: jsonInt(json['kapasitas'], 1),
      hargaSewaBulanan: jsonInt(json['harga_sewa_bulanan'], 0),
      jenisHarga: json['jenis_harga'],
      status: json['status'] ?? 'tersedia',
      foto: json['foto'],
    );
  }

  String get formattedHarga {
    return 'Rp ${hargaSewaBulanan.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.')}/bulan';
  }
}
