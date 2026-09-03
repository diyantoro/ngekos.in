import 'dart:typed_data';
import 'package:image_picker/image_picker.dart';

class PlatformFile {
  final String name;
  final Uint8List bytes;

  const PlatformFile({required this.name, required this.bytes});

  static Future<PlatformFile> fromXFile(XFile xfile) async {
    return PlatformFile(
      name: xfile.name,
      bytes: await xfile.readAsBytes(),
    );
  }
}
