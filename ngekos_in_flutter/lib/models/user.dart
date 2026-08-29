class User {
  final int id;
  final String nama;
  final String email;
  final String? noHp;
  final String? avatar;
  final String? inisial;
  final String peran;
  final int? pesanBelumDibaca;
  final int bantuanBelumDibaca;

  User({
    required this.id,
    required this.nama,
    required this.email,
    this.noHp,
    this.avatar,
    this.inisial,
    required this.peran,
    this.pesanBelumDibaca,
    this.bantuanBelumDibaca = 0,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      nama: json['nama'],
      email: json['email'],
      noHp: json['no_hp'],
      avatar: json['avatar'],
      inisial: json['inisial'],
      peran: json['peran'],
      pesanBelumDibaca: json['pesan_belum_dibaca'],
      bantuanBelumDibaca: json['bantuan_belum_dibaca'] ?? 0,
    );
  }

  bool get isAnakKos => peran == 'anak_kos';
  bool get isPemilik => peran == 'pemilik';

  User copyWith({int? bantuanBelumDibaca}) {
    return User(
      id: id,
      nama: nama,
      email: email,
      noHp: noHp,
      avatar: avatar,
      inisial: inisial,
      peran: peran,
      pesanBelumDibaca: pesanBelumDibaca,
      bantuanBelumDibaca: bantuanBelumDibaca ?? this.bantuanBelumDibaca,
    );
  }
}
