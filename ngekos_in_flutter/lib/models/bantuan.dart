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
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      userId: json['user_id'],
      nama: json['nama'] ?? '',
      email: json['email'] ?? '',
      subjek: json['subjek'],
      pesan: json['pesan'] ?? '',
      balasan: json['balasan'],
      dibalasOleh: json['dibalas_oleh'],
      dibalasAt: _parseDate(json['dibalas_at']),
      status: json['status'] ?? 'baru',
      createdAt: _parseDate(json['created_at']),
      updatedAt: _parseDate(json['updated_at']),
    );
  }

  static DateTime? _parseDate(dynamic value) {
    if (value == null || value.toString().isEmpty) return null;
    try {
      return DateTime.parse(value.toString());
    } catch (_) {
      return null;
    }
  }
}
