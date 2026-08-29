import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/theme.dart';
import '../../models/properti.dart';
import '../../services/properti_manage_service.dart';
import '../../widgets/facility_chip.dart';

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
  List<String> _selectedFasilitas = [];
  String _jenisHarga = 'bulanan';
  String _status = 'aktif';
  File? _foto;
  bool _isLoading = false;

  static const List<String> _allFasilitas = [
    'WiFi', 'AC', 'Kasur', 'Dapur', 'Kamar Mandi Dalam', 'Laundry', 'Parkir',
    'CCTV', 'Security', 'Listrik', 'Air', 'TV', 'Rak Baju', 'Gym', 'Kolam Renang',
  ];

  bool get isEditing => widget.properti != null;

  @override
  void initState() {
    super.initState();
    if (isEditing) {
      _namaController.text = widget.properti!.nama;
      _kotaController.text = widget.properti!.kota;
      _alamatController.text = widget.properti!.alamat;
      _deskripsiController.text = widget.properti!.deskripsi ?? '';
      _aturanController.text = widget.properti!.aturan ?? '';
      _dendaController.text = (widget.properti!.dendaPerHari ?? 0).toString();
      if (widget.properti!.harga != null) {
        _hargaController.text = widget.properti!.harga.toString();
      }
      _selectedFasilitas = List.from(widget.properti!.fasilitas ?? []);
      _jenisHarga = (widget.properti!.jenisHarga ?? 'bulanan').toLowerCase() == 'harian' ? 'harian' : 'bulanan';
      _status = widget.properti!.status.isEmpty ? 'aktif' : widget.properti!.status;
    }
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
    super.dispose();
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1200, imageQuality: 80);
    if (picked != null) setState(() => _foto = File(picked.path));
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);

    final hargaText = _hargaController.text.trim();
    final harga = hargaText.isEmpty ? null : int.tryParse(hargaText);

    try {
      if (isEditing) {
        await PropertiManageService.updateProperti(
          widget.properti!.id,
          nama: _namaController.text.trim(),
          kota: _kotaController.text.trim(),
          alamat: _alamatController.text.trim(),
          deskripsi: _deskripsiController.text.trim().isNotEmpty ? _deskripsiController.text.trim() : null,
          fasilitas: _selectedFasilitas.isNotEmpty ? _selectedFasilitas : null,
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
          deskripsi: _deskripsiController.text.trim().isNotEmpty ? _deskripsiController.text.trim() : null,
          fasilitas: _selectedFasilitas.isNotEmpty ? _selectedFasilitas : null,
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
      backgroundColor: AppTheme.background,
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
                    border: Border.all(color: AppTheme.border, style: BorderStyle.solid),
                    image: _foto != null ? DecorationImage(image: FileImage(_foto!), fit: BoxFit.cover) : null,
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
              const Text('Fasilitas', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 8),
              GridView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 4, mainAxisSpacing: 8, crossAxisSpacing: 8),
                itemCount: _allFasilitas.length,
                itemBuilder: (context, index) {
                  final f = _allFasilitas[index];
                  final selected = _selectedFasilitas.contains(f);
                  return FacilityChip(
                    label: f,
                    showCheckbox: true,
                    isSelected: selected,
                    onTap: () {
                      setState(() {
                        if (selected) _selectedFasilitas.remove(f);
                        else _selectedFasilitas.add(f);
                      });
                    },
                  );
                },
              ),
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
