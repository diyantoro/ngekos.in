import 'dart:io';

import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';

Future<void> saveCsv(String csv, String fileName) async {
  final dir = await getApplicationDocumentsDirectory();
  final file = File('${dir.path}/$fileName');
  await file.writeAsString(csv, flush: true);
  await Share.shareXFiles([XFile(file.path, mimeType: 'text/csv')]);
}
