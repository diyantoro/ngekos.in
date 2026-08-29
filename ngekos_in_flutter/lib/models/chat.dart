class Conversation {
  final int propertiId;
  final String? propertiNama;
  final String? propertiFoto;
  final int? anakKosId;
  final ChatUser? lawan;
  final String? lastMessage;
  final DateTime? lastMessageAt;
  final int unreadCount;
  final int? lastSenderId;

  Conversation({
    required this.propertiId,
    this.propertiNama,
    this.propertiFoto,
    this.anakKosId,
    this.lawan,
    this.lastMessage,
    this.lastMessageAt,
    this.unreadCount = 0,
    this.lastSenderId,
  });

  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      propertiId: json['properti_id'],
      propertiNama: json['properti_nama'],
      propertiFoto: json['properti_foto'],
      anakKosId: json['anak_kos_id'],
      lawan: json['lawan'] != null ? ChatUser.fromJson(json['lawan']) : null,
      lastMessage: json['last_message'],
      lastMessageAt: json['last_message_at'] != null
          ? DateTime.parse(json['last_message_at'])
          : null,
      unreadCount: json['unread_count'] ?? 0,
      lastSenderId: json['last_sender_id'],
    );
  }
}

class ChatUser {
  final int id;
  final String nama;
  final String? avatar;

  ChatUser({required this.id, required this.nama, this.avatar});

  factory ChatUser.fromJson(Map<String, dynamic> json) {
    return ChatUser(
      id: json['id'],
      nama: json['nama'],
      avatar: json['avatar'],
    );
  }
}

class ChatMessage {
  final int id;
  final int pengirimId;
  final String isi;
  final DateTime? dibacaPada;
  final DateTime createdAt;

  ChatMessage({
    required this.id,
    required this.pengirimId,
    required this.isi,
    this.dibacaPada,
    required this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'],
      pengirimId: json['pengirim_id'],
      isi: json['isi'],
      dibacaPada: json['dibaca_pada'] != null
          ? DateTime.parse(json['dibaca_pada'])
          : null,
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}
