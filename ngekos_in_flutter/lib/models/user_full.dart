class UserFull {
  final int id;
  final String nama;
  final String email;
  final String? noHp;
  final String? avatar;
  final String? inisial;
  final String peran;
  final bool aktif;
  final DateTime? createdAt;

  UserFull({
    required this.id,
    required this.nama,
    required this.email,
    this.noHp,
    this.avatar,
    this.inisial,
    required this.peran,
    this.aktif = true,
    this.createdAt,
  });

  factory UserFull.fromJson(Map<String, dynamic> json) {
    return UserFull(
      id: json['id'],
      nama: json['nama'] ?? '',
      email: json['email'] ?? '',
      noHp: json['no_hp'],
      avatar: json['avatar'],
      inisial: json['inisial'],
      peran: json['peran'] ?? '',
      aktif: json['dinonaktifkan_pada'] == null,
      createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : null,
    );
  }

  bool get isSuperAdmin => peran == 'super_admin';
  bool get isAdmin => peran == 'admin';
  bool get isPemilik => peran == 'pemilik';
  bool get isAnakKos => peran == 'anak_kos';

  String get roleLabel {
    switch (peran) {
      case 'super_admin': return 'Super Admin';
      case 'admin': return 'Admin';
      case 'pemilik': return 'Pemilik Kos';
      case 'anak_kos': return 'Anak Kos';
      default: return peran;
    }
  }
}
