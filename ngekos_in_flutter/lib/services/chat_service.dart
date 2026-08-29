import '../config/api_config.dart';
import '../models/chat.dart';
import 'api_service.dart';

class ChatService {
  static Future<List<Conversation>> getConversations() async {
    final data = await ApiService.get(ApiConfig.chat);
    return (data as List).map((e) => Conversation.fromJson(e)).toList();
  }

  static Future<List<ChatMessage>> getMessages(int propertiId, {int? anakKosId}) async {
    var url = ApiConfig.chatDetail(propertiId);
    if (anakKosId != null) {
      url += '?anak_kos_id=$anakKosId';
    }
    final data = await ApiService.get(url);
    return (data['messages'] as List).map((e) => ChatMessage.fromJson(e)).toList();
  }

  static Future<ChatMessage> sendMessage(int propertiId, String isi, {int? anakKosId}) async {
    final body = <String, dynamic>{
      'isi': isi,
    };
    if (anakKosId != null) {
      body['anak_kos_id'] = anakKosId;
    }
    final data = await ApiService.post(ApiConfig.chatDetail(propertiId), body: body);
    return ChatMessage.fromJson(data);
  }
}
