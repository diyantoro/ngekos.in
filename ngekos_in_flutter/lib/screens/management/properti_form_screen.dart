import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:image_cropper/image_cropper.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import '../../config/theme.dart';
import '../../models/properti.dart';
import '../../services/properti_manage_service.dart';
import '../../src/platform_file.dart';
import '../../widgets/facility_icon.dart';
import '../../widgets/kos_map.dart';
import 'pilih_lokasi_screen.dart';

class PropertiFormScreen extends StatefulWidget {
  final Properti? properti;
  const PropertiFormScreen({super.key, this.properti});

  @override
  State<PropertiFormScreen> createState() => _PropertiFormScreenState();
}

class _PropertiFormScreenState extends State<PropertiFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _namaController = TextEditingController();
  final _kotaController = TextEditingController();
  final _alamatController = TextEditingController();
  final _deskripsiController = TextEditingController();
  final _aturanController = TextEditingController();
  final _dendaController = TextEditingController(text: '0');
  final _hargaController = TextEditingController();
  final ukuranKamarController = TextEditingController();
  final depositController = TextEditingController();
  List<String> _selectedFasilitas = [];
  double? _latitude;
  double? _longitude;
  bool _termasukListrik = true;
  String _ukuranKamar = '';
  String _deposit = '';
  String _jenisHarga = 'bulanan';
  String _status = 'aktif';
  PlatformFile? _foto;
  bool _isLoading = false;

  // Kelompok fasilitas sesuai struktur referensi (Mamikos-like).
  static const Map<String, List<String>> _kelompokFasilitas = {
    'Fasilitas Kamar': [
      'Kasur', 'Meja', 'Lemari / Storage', 'Ventilasi', 'Jendela',
      'AC', 'Kursi', 'Bantal', 'Cermin',
    ],
    'Fasilitas Kamar Mandi': [
      'K. Mandi Dalam', 'K. Mandi Luar', 'Kloset Duduk', 'Shower',
      'Ember mandi', 'Air panas',
    ],
    'Fasilitas Umum': [
      'WiFi', 'Kulkas', 'R. Tamu', 'Penjaga Kos', 'R. Jemur', 'Dapur',
    ],
    'Fasilitas Parkir': [
      'Parkir Mobil', 'Parkir Motor & Sepeda',
    ],
    'Peraturan Khusus': [
      'Tamu boleh menginap', 'Tamu menginap dikenakan biaya',
      'Maks. 2 orang/kamar', 'Boleh pasutri',
      'Wajib sertakan surat nikah saat pengajuan sewa',
      'Tidak boleh bawa anak',
      'Dilarang merokok di kamar', 'Lawan jenis dilarang ke kamar',
    ],
  };

  // Normalisasi nama fasilitas agar "kamar_mandi_dalam" == "Kamar Mandi Dalam".
  static String _normalizeFasilitas(String s) =>
      s.trim().toLowerCase().replaceAll(RegExp(r'[ _-]'), '');

  bool get isEditing => widget.properti != null;

  @override
  void initState() {
    super.initState();
    if (isEditing) {
      _namaController.text = widget.properti!.nama;
      _kotaController.text = widget.properti!.kota;
      _alamatController.text = widget.properti!.alamat;
      _latitude = widget.properti!.latitude;
      _longitude = widget.properti!.longitude;
      _deskripsiController.text = widget.properti!.deskripsi ?? '';
      _aturanController.text = widget.properti!.aturan ?? '';
      _dendaController.text = (widget.properti!.dendaPerHari ?? 0).toString();
      if (widget.properti!.harga != null) {
        _hargaController.text = widget.properti!.harga.toString();
      }
      _selectedFasilitas = List.from(widget.properti!.fasilitas ?? []);
      _parseFasilitasTersimpan(_selectedFasilitas);
      ukuranKamarController.text = _ukuranKamar;
      depositController.text = _deposit;
      _jenisHarga = (widget.properti!.jenisHarga ?? 'bulanan').toLowerCase() == 'harian' ? 'harian' : 'bulanan';
      _status = widget.properti!.status.isEmpty ? 'aktif' : widget.properti!.status;
    }
  }

  // Pisahkan item khusus (listrik, spesifikasi, deposit) dari daftar fasilitas
  // saat mode edit, lalu isi kembali ke state masing-masing.
  void _parseFasilitasTersimpan(List<String> list) {
    list.removeWhere((f) {
      final n = _normalizeFasilitas(f);
      if (n == 'tidaktermasuklistrik' || n == 'termasuklistrik') {
        _termasukListrik = n == 'termasuklistrik';
        return true;
      }
      if (f.contains(':')) {
        if (f.trimLeft().toLowerCase().startsWith('spesifikasi')) {
          _ukuranKamar = f.split(':').last.trim().replaceAll(RegExp(r' meter$', caseSensitive: false), '');
          ukuranKamarController.text = _ukuranKamar;
          return true;
        }
        if (f.trimLeft().toLowerCase().startsWith('deposit')) {
          _deposit = f.split(':').last.trim();
          depositController.text = _deposit;
          return true;
        }
      }
      return false;
    });
  }

  @override
  void dispose() {
    _namaController.dispose();
    _kotaController.dispose();
    _alamatController.dispose();
    _deskripsiController.dispose();
    _aturanController.dispose();
    _dendaController.dispose();
    _hargaController.dispose();
    ukuranKamarController.dispose();
    depositController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery);
    if (picked == null) return;

    final cropped = await _cropImage(picked);
    if (cropped == null) return;

    final bytes = await cropped.readAsBytes();
    if (!mounted) return;
    setState(() => _foto = PlatformFile(name: 'foto.jpg', bytes: bytes));
  }

  Future<CroppedFile?> _cropImage(XFile picked) async {
    try {
      final cropper = ImageCropper();
      return await cropper.cropImage(
        sourcePath: picked.path,
        compressFormat: ImageCompressFormat.jpg,
        compressQuality: 90,
        uiSettings: [
          if (kIsWeb)
            WebUiSettings(
              context: context,
              presentStyle: WebPresentStyle.dialog,
              size: const CropperSize(width: 600, height: 600),
              checkCrossOrigin: false,
              checkOrientation: false,
            )
          else
            AndroidUiSettings(
              toolbarTitle: 'Crop Foto Properti',
              toolbarColor: AppTheme.primary,
              toolbarWidgetColor: Colors.white,
              initAspectRatio: CropAspectRatioPreset.original,
              lockAspectRatio: false,
            ),
        ],
      );
    } catch (_) {
      return null;
    }
  }

  Future<void> _pilihLokasi() async {
    final result = await Navigator.push<LatLng>(
      context,
      MaterialPageRoute(
        builder: (_) => PilihLokasiScreen(
          initialLatitude: _latitude,
          initialLongitude: _longitude,
        ),
      ),
    );
    if (result != null) {
      setState(() {
        _latitude = result.latitude;
        _longitude = result.longitude;
      });
    }
  }

  // Gabungkan item checklist + info listrik/spesifikasi/deposit jadi satu daftar
  // yang disimpan ke kolom "fasilitas" pada backend.
  List<String> _buildFasilitasSubmit() {
    final list = List<String>.from(_selectedFasilitas);
    list.add(_termasukListrik ? 'Termasuk listrik' : 'Tidak termasuk listrik');
    final ukuran = ukuranKamarController.text.trim();
    if (ukuran.isNotEmpty) {
      list.add('Spesifikasi: $ukuran meter');
    }
    final deposit = depositController.text.trim();
    if (deposit.isNotEmpty) {
      list.add('Deposit: $deposit');
    }
    return list.where((e) => e.trim().isNotEmpty).toList();
  }

  // Render satu kelompok fasilitas sebagai daftar bilah yang bisa diklik.
  Widget _buildItemIcon(String label) {
    final asset = FacilityIcon.assetFor(label);
    if (asset != null) {
      // Gambar PNG ditampilkan natural tanpa di-tint agar selaras dengan web.
      return Image.asset(
        asset,
        width: 22,
        height: 22,
        errorBuilder: (_, _, _) => const SizedBox(width: 22, height: 22),
      );
    }
    final material = FacilityIcon.materialFor(label);
    return     Icon(
      material ?? Icons.help_outline_rounded,
      size: 22,
      color: _selectedFasilitas.any((s) => _normalizeFasilitas(s) == _normalizeFasilitas(label))
          ? AppTheme.primary
          : AppTheme.txtSec,
    );
  }

  // Render satu kelompok fasilitas sebagai daftar bilah yang bisa diklik.
  Widget _buildFasilitasGroup(String judul, List<String> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(judul, style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.txtSec)),
        const SizedBox(height: 6),
        Container(
          decoration: BoxDecoration(
            color: AppTheme.card,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.bdrLight),
          ),
          child: Column(
            children: items.map((f) {
              final selected = _selectedFasilitas.any(
                (s) => _normalizeFasilitas(s) == _normalizeFasilitas(f),
              );
              return Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () {
                    setState(() {
                      if (selected) {
                        _selectedFasilitas.removeWhere(
                          (s) => _normalizeFasilitas(s) == _normalizeFasilitas(f),
                        );
                      } else {
                        _selectedFasilitas.add(f);
                      }
                    });
                  },
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                    child: Row(
                      children: [
                        SizedBox(
                          width: 22,
                          height: 22,
                          child: Checkbox(
                            value: selected,
                            onChanged: (_) {
                              setState(() {
                                if (selected) {
                                  _selectedFasilitas.removeWhere(
                                    (s) => _normalizeFasilitas(s) == _normalizeFasilitas(f),
                                  );
                                } else {
                                  _selectedFasilitas.add(f);
                                }
                              });
                            },
                            activeColor: AppTheme.primary,
                            visualDensity: VisualDensity.compact,
                          ),
                        ),
                        const SizedBox(width: 10),
                        _buildItemIcon(f),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            f,
                            style: TextStyle(
                              fontSize: 13,
                              color: selected ? AppTheme.primary : AppTheme.txt,
                              fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ),
        const SizedBox(height: 16),
      ],
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    final hargaText = _hargaController.text.trim();
    final harga = hargaText.isEmpty ? null : int.tryParse(hargaText);
    // Biarkan _selectedFasilitas utuh; fasilitas submit dihitung sekali saja.
    final fasilitas = _buildFasilitasSubmit().isNotEmpty ? _buildFasilitasSubmit() : null;

    try {
      if (isEditing) {
        await PropertiManageService.updateProperti(
          widget.properti!.id,
          nama: _namaController.text.trim(),
          kota: _kotaController.text.trim(),
          alamat: _alamatController.text.trim(),
          latitude: _latitude,
          longitude: _longitude,
          deskripsi: _deskripsiController.text.trim().isNotEmpty ? _deskripsiController.text.trim() : null,
          fasilitas: fasilitas,
          aturan: _aturanController.text.trim().isNotEmpty ? _aturanController.text.trim() : null,
          dendaPerHari: int.tryParse(_dendaController.text),
          harga: harga,
          jenisHarga: _jenisHarga,
          status: _status,
          foto: _foto,
        );
      } else {
        await PropertiManageService.createProperti(
          nama: _namaController.text.trim(),
          kota: _kotaController.text.trim(),
          alamat: _alamatController.text.trim(),
          latitude: _latitude,
          longitude: _longitude,
          deskripsi: _deskripsiController.text.trim().isNotEmpty ? _deskripsiController.text.trim() : null,
          fasilitas: fasilitas,
          aturan: _aturanController.text.trim().isNotEmpty ? _aturanController.text.trim() : null,
          dendaPerHari: int.tryParse(_dendaController.text),
          harga: harga,
          jenisHarga: _jenisHarga,
          status: _status,
          foto: _foto,
        );
      }
      if (mounted) {
        Navigator.pop(context);
      } else {
        _isLoading = false;
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: AppTheme.error));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(isEditing ? 'Ubah Properti' : 'Buat Properti', style: const TextStyle(fontWeight: FontWeight.bold))),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              GestureDetector(
                onTap: _pickImage,
                child: Container(
                  height: 160,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: _foto != null ? null : const Color(0xFFCCFBF1),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppTheme.bdr, style: BorderStyle.solid),
                    image: _foto != null ? DecorationImage(image: MemoryImage(_foto!.bytes), fit: BoxFit.cover) : null,
                  ),
                  child: _foto == null
                      ? const Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                          Icon(Icons.camera_alt_rounded, size: 32, color: AppTheme.primary),
                          SizedBox(height: 8),
                          Text('Pilih Foto Properti', style: TextStyle(color: AppTheme.primary, fontSize: 13)),
                        ])
                      : null,
                ),
              ),
              const SizedBox(height: 20),
              const Text('Nama Properti *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 6),
              TextFormField(controller: _namaController, decoration: const InputDecoration(hintText: 'Contoh: Kos Mawar'),
                validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Kota *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
                      const SizedBox(height: 6),
                      TextFormField(controller: _kotaController, decoration: const InputDecoration(hintText: 'Contoh: Jakarta'),
                        validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null),
                    ],
                  )),
                  const SizedBox(width: 12),
                  Expanded(child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Status', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
                      const SizedBox(height: 6),
                      DropdownButtonFormField<String>(
                        initialValue: _status,
                        decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 12)),
                        items: const [
                          DropdownMenuItem(value: 'aktif', child: Text('Aktif')),
                          DropdownMenuItem(value: 'nonaktif', child: Text('Nonaktif')),
                        ],
                        onChanged: (v) => setState(() => _status = v ?? 'aktif'),
                      ),
                    ],
                  )),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Alamat *', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 6),
              TextFormField(controller: _alamatController, decoration: const InputDecoration(hintText: 'Alamat lengkap'),
                validator: (v) => (v == null || v.isEmpty) ? 'Wajib diisi' : null),
              const SizedBox(height: 16),
              const Text('Lokasi di Peta', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 6),
              InkWell(
                onTap: _pilihLokasi,
                borderRadius: BorderRadius.circular(12),
                child: Stack(
                  children: [
                    SizedBox(
                      height: 160,
                      width: double.infinity,
                      child: KosMap(
                        latitude: _latitude,
                        longitude: _longitude,
                        nama: _namaController.text,
                        interactive: false,
                      ),
                    ),
                    Positioned(
                      right: 8,
                      top: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppTheme.card,
                          borderRadius: BorderRadius.circular(8),
                          boxShadow: const [
                            BoxShadow(
                              color: Colors.black12,
                              blurRadius: 6,
                              offset: Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.edit_location_alt_rounded, size: 16, color: AppTheme.primary),
                            const SizedBox(width: 6),
                            Text(
                              _latitude != null ? 'Ubah Lokasi' : 'Pilih Lokasi',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Jenis Harga', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
                      const SizedBox(height: 6),
                      DropdownButtonFormField<String>(
                        initialValue: _jenisHarga,
                        decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 12)),
                        items: const [
                          DropdownMenuItem(value: 'bulanan', child: Text('Bulanan')),
                          DropdownMenuItem(value: 'harian', child: Text('Harian')),
                        ],
                        onChanged: (v) => setState(() => _jenisHarga = v ?? 'bulanan'),
                      ),
                    ],
                  )),
                  const SizedBox(width: 12),
                  Expanded(child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Harga (Rp)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
                      const SizedBox(height: 6),
                      TextFormField(controller: _hargaController, keyboardType: TextInputType.number, decoration: const InputDecoration(hintText: 'Contoh: 750000')),
                    ],
                  )),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Deskripsi', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 6),
              TextFormField(controller: _deskripsiController, maxLines: 3, decoration: const InputDecoration(hintText: 'Deskripsi properti')),
              const SizedBox(height: 16),
              const Text('Denda Terlambat per Hari (Rp)', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 6),
              TextFormField(controller: _dendaController, keyboardType: TextInputType.number, decoration: const InputDecoration(hintText: 'Contoh: 5000')),
              const SizedBox(height: 16),
              const Text('Aturan Kos', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 6),
              TextFormField(controller: _aturanController, maxLines: 2, decoration: const InputDecoration(hintText: 'Contoh: Jam malam 22:00')),
              const SizedBox(height: 16),
              const Text('Spesifikasi Tipe Kamar', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              TextFormField(
                controller: ukuranKamarController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(hintText: 'Ukuran kamar (mÂ²), contoh: 10.5'),
              ),
              const SizedBox(height: 8),
              SwitchListTile(
                title: const Text('Termasuk Listrik', style: TextStyle(fontSize: 14)),
                value: _termasukListrik,
                onChanged: (v) => setState(() => _termasukListrik = v),
                contentPadding: EdgeInsets.zero,
                dense: true,
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: depositController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(hintText: 'Deposit (Rp), contoh: 500000'),
              ),
              const SizedBox(height: 16),
              const Text('Fasilitas', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 8),
              ..._kelompokFasilitas.entries.map((entry) => _buildFasilitasGroup(entry.key, entry.value)),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _submit,
                  style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary, padding: const EdgeInsets.symmetric(vertical: 16)),
                  child: _isLoading
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : Text(isEditing ? 'Simpan Perubahan' : 'Buat Properti', style: const TextStyle(fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
