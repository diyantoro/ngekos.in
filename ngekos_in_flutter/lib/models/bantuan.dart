class BantuanMessage {
  final int id;
  final int? userId;
  final String nama;
  final String email;
  final String? subjek;
  final String pesan;
  final String? balasan;
  final String? dibalasOleh;
  final DateTime? dibalasAt;
  final String status;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  BantuanMessage({
    required this.id,
    this.userId,
    required this.nama,
    required this.email,
    this.subjek,
    required this.pesan,
    this.balasan,
    this.dibalasOleh,
    this.dibalasAt,
    required this.status,
    this.createdAt,
    this.updatedAt,
  });

  factory BantuanMessage.fromJson(Map<String, dynamic> json) {
    return BantuanMessage(
      id: json['id'],
      userId: json['user_id'],
      nama: json['nama'] ?? '',
      email: json['email'] ?? '',
      subjek: json['subjek'],
      pesan: json['pesan'] ?? '',
      balasan: json['balasan'],
      dibalasOleh: json['dibalas_oleh'],
      dibalasAt: json['dibalas_at'] != null ? DateTime.parse(json['dibalas_at']) : null,
      status: json['status'] ?? 'baru',
      createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : null,
      updatedAt: json['updated_at'] != null ? DateTime.parse(json['updated_at']) : null,
    );
  }
}
