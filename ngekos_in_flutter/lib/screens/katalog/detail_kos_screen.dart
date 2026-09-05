import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';

import '../../config/theme.dart';
import '../../models/properti.dart';
import '../../models/kamar.dart';
import '../../models/chat.dart';
import '../../providers/auth_provider.dart';
import '../../services/katalog_service.dart';
import '../../services/chat_service.dart';
import '../../services/api_service.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/facility_icon.dart';
import '../../widgets/kos_map.dart';
import '../chat/chat_detail_screen.dart';
import '../auth/pilih_peran_screen.dart';

class DetailKosScreen extends StatefulWidget {
  final int propertiId;
  const DetailKosScreen({super.key, required this.propertiId});

  @override
  State<DetailKosScreen> createState() => _DetailKosScreenState();
}

class _DetailKosScreenState extends State<DetailKosScreen> {
  Properti? _properti;
  bool _isLoading = true;
  final Set<int> _bookedKamarIds = {};
  int? _bookingKamarId;

  String _statusKamar(Kamar kamar) =>
      _bookedKamarIds.contains(kamar.id) ? 'terisi' : kamar.status;

  int get _kamarTersediaCount => (_properti?.kamars ?? [])
      .where((k) => _statusKamar(k) == 'tersedia')
      .length;

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    try {
      final data = await KatalogService.getDetail(widget.propertiId);
      setState(() {
        _properti = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final bolehHubungi = auth.user == null || auth.user!.isAnakKos;
    return Scaffold(
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primary),
            )
          : _properti == null
          ? const Center(child: Text('Gagal memuat data'))
          : CustomScrollView(
              slivers: [
                SliverAppBar(
                  expandedHeight: 250,
                  pinned: true,
                  flexibleSpace: FlexibleSpaceBar(
                    title: Text(
                      _properti!.nama,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    background: _properti!.foto != null
                        ? CachedNetworkImage(
                            imageUrl: _properti!.foto!,
                            imageRenderMethodForWeb: ImageRenderMethodForWeb.HttpGet,
                            fit: BoxFit.cover,
                            errorWidget: (_, _, _) =>
                                Container(color: Colors.grey[200]),
                          )
                        : Container(
                            color: AppTheme.primary.withValues(alpha: 0.1),
                            child: const Icon(
                              Icons.home_rounded,
                              size: 64,
                              color: AppTheme.primary,
                            ),
                          ),
                  ),
                ),
                SliverPadding(
                  padding: const EdgeInsets.all(16),
                  sliver: SliverList(
                    delegate: SliverChildListDelegate([
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on_rounded,
                            color: AppTheme.primary,
                            size: 18,
                          ),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              '${_properti!.alamat}, ${_properti!.kota}',
                              style: TextStyle(
                                fontSize: 14,
                                color: AppTheme.txtSec,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      SizedBox(
                        height: 180,
                        child: KosMap(
                          latitude: _properti!.latitude,
                          longitude: _properti!.longitude,
                          kota: _properti!.kota,
                          nama: _properti!.nama,
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          _InfoChip(
                            label: '$_kamarTersediaCount Tersedia',
                            color: AppTheme.success,
                          ),
                          const SizedBox(width: 8),
                          _InfoChip(
                            label: '${_properti!.totalKamar} Total Kamar',
                            color: AppTheme.primary,
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      _buildInfoGrid(),
                      if (_properti!.deskripsi != null &&
                          _properti!.deskripsi!.isNotEmpty) ...[
                        const SizedBox(height: 20),
                        const Text(
                          'Deskripsi',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _properti!.deskripsi!,
                          style: const TextStyle(fontSize: 14, height: 1.5),
                        ),
                      ],
                      if (_properti!.aturan != null &&
                          _properti!.aturan!.isNotEmpty) ...[
                        const SizedBox(height: 20),
                        const Text(
                          'Aturan Kos',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: const Color(0xFFFCD34D).withValues(alpha: 0.5),
                            ),
                          ),
                          child: Text(
                            _properti!.aturan!,
                            style: const TextStyle(
                              fontSize: 13,
                              height: 1.5,
                              color: Color(0xFF92400E),
                            ),
                          ),
                        ),
                      ],
                      if (_properti!.fasilitas != null &&
                          _properti!.fasilitas!.isNotEmpty) ...[
                        const SizedBox(height: 20),
                        const Text(
                          'Fasilitas',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: AppTheme.card,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppTheme.bdrLight),
                          ),
                          child: Column(
                            children: _properti!.fasilitas!
                                .map(
                                  (f) => Padding(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 12,
                                      vertical: 12,
                                    ),
                                    child: Row(
                                      children: [
                                        _buildDetailFacilityIcon(f),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Text(
                                            f,
                                            style: const TextStyle(fontSize: 13),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                )
                                .toList(),
                          ),
                        ),
                      ],
                      if (_properti!.kamars != null &&
                          _properti!.kamars!.isNotEmpty) ...[
                        const SizedBox(height: 20),
                        const Text(
                          'Daftar Kamar',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        ..._properti!.kamars!.map(
                          (kamar) => Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              color: AppTheme.card,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: AppTheme.bdrLight),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.04),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 110,
                                  height: 120,
                                  decoration: BoxDecoration(
                                    gradient: const LinearGradient(
                                      begin: Alignment.topLeft,
                                      end: Alignment.bottomRight,
                                      colors: [
                                        Color(0xFFF3F4F6),
                                        Color(0xFFF9FAFB),
                                      ],
                                    ),
                                    borderRadius: const BorderRadius.horizontal(
                                      left: Radius.circular(16),
                                    ),
                                  ),
                                  child: kamar.foto != null
                                      ? ClipRRect(
                                          borderRadius:
                                              const BorderRadius.horizontal(
                                                left: Radius.circular(16),
                                              ),
                                          child: CachedNetworkImage(
                                            imageUrl: kamar.foto!,
                                            imageRenderMethodForWeb: ImageRenderMethodForWeb.HttpGet,
                                            fit: BoxFit.cover,
                                            errorWidget: (_, _, _) =>
                                                const Center(
                                                  child: Icon(
                                                    Icons.king_bed_rounded,
                                                    size: 32,
                                                    color: Color(0xFFD1D5DB),
                                                  ),
                                                ),
                                          ),
                                        )
                                      : const Center(
                                          child: Icon(
                                            Icons.king_bed_rounded,
                                            size: 32,
                                            color: Color(0xFFD1D5DB),
                                          ),
                                        ),
                                ),
                                Expanded(
                                  child: Padding(
                                    padding: const EdgeInsets.all(12),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Row(
                                          children: [
                                            Expanded(
                                              child: Text(
                                                kamar.nama,
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 14,
                                                    color: AppTheme.txt,
                                                  ),
                                              ),
                                            ),
                                            StatusBadge(
                                              status: _statusKamar(kamar),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '${kamar.kapasitas} orang',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: AppTheme.txtSec,
                                          ),
                                        ),
                                        const SizedBox(height: 8),
                                        Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text.rich(
                                              TextSpan(
                                                children: [
                                                  TextSpan(
                                                    text:
                                                        'Rp ${AppTheme.formatRupiah(kamar.hargaSewaBulanan).replaceFirst('Rp ', '')}',
                                                    style: const TextStyle(
                                                      fontSize: 15,
                                                      fontWeight:
                                                          FontWeight.w800,
                                                      color: AppTheme.primary,
                                                    ),
                                                  ),
                                                  TextSpan(
                                                    text:
                                                        '/${kamar.jenisHarga == 'harian' ? 'hari' : 'bln'}',
                                                    style: TextStyle(
                                                      fontSize: 10,
                                                      fontWeight:
                                                          FontWeight.w500,
                                                      color: AppTheme.txtMuted,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 8),
                                        if (_statusKamar(kamar) == 'tersedia' &&
                                            bolehHubungi) ...[
                                          SizedBox(
                                            width: double.infinity,
                                            height: 34,
                                            child: ElevatedButton.icon(
                                              onPressed: _bookingKamarId != null
                                                  ? null
                                                  : () => _sewaKamar(kamar),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor:
                                                    AppTheme.accent,
                                                foregroundColor: Colors.white,
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 12,
                                                    ),
                                                shape: RoundedRectangleBorder(
                                                  borderRadius:
                                                      BorderRadius.circular(8),
                                                ),
                                              ),
                                              icon: _bookingKamarId == kamar.id
                                                  ? const SizedBox(
                                                      width: 15,
                                                      height: 15,
                                                      child:
                                                          CircularProgressIndicator(
                                                            strokeWidth: 2,
                                                            color: Colors.white,
                                                          ),
                                                    )
                                                  : const Icon(
                                                      Icons.key_rounded,
                                                      size: 15,
                                                    ),
                                              label: const Text(
                                                'Sewa Kamar',
                                                style: TextStyle(
                                                  fontSize: 12,
                                                  fontWeight: FontWeight.w700,
                                                ),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                      if (_properti!.pemilik != null) ...[
                        const SizedBox(height: 20),
                        const Text(
                          'Pemilik',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppTheme.card,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: AppTheme.bdr),
                          ),
                          child: Row(
                            children: [
                              CircleAvatar(
                                backgroundColor: AppTheme.primary.withValues(
                                  alpha: 0.1,
                                ),
                                child: Text(
                                  _properti!.pemilik!.nama[0].toUpperCase(),
                                  style: const TextStyle(
                                    color: AppTheme.primary,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      _properti!.pemilik!.nama,
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    if (_properti!.pemilik!.noHp != null)
                                      Text(
                                        _properti!.pemilik!.noHp!,
                                        style: TextStyle(
                                          fontSize: 13,
                                          color: AppTheme.txtSec,
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 100),
                    ]),
                  ),
                ),
              ],
            ),
      bottomNavigationBar: _properti != null && bolehHubungi
          ? Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.card,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black12,
                    blurRadius: 8,
                    offset: Offset(0, -2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => _startChat(
                        pesan:
                            'Halo, saya tertarik dengan kos ${_properti!.nama}. Apakah masih tersedia?',
                      ),
                      icon: const Icon(Icons.chat_rounded),
                      label: Text(
                        auth.user == null
                            ? 'Masuk untuk Chat'
                            : 'Hubungi Pemilik',
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primary,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                    ),
                  ),
                ],
              ),
            )
          : null,
    );
  }

  Future<void> _startChat({String? pesan}) async {
    final auth = context.read<AuthProvider>();
    if (auth.user == null) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const PilihPeranScreen()),
      );
      return;
    }
    if (pesan != null) {
      try {
        await ChatService.sendMessage(
          _properti!.id,
          pesan,
          anakKosId: auth.user!.id,
        );
      } catch (_) {}
    }
    if (!mounted) return;
    final pemilik = _properti!.pemilik;
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ChatDetailScreen(
          propertiId: _properti!.id,
          propertiNama: _properti!.nama,
          lawan: pemilik != null
              ? ChatUser(id: pemilik.id, nama: pemilik.nama)
              : null,
          anakKosId: auth.user!.id,
        ),
      ),
    );
  }

  Future<void> _sewaKamar(Kamar kamar) async {
    final auth = context.read<AuthProvider>();
    if (auth.user == null) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const PilihPeranScreen()),
      );
      return;
    }
    if (_bookingKamarId != null) return;

    final now = DateTime.now();
    final firstDate = DateTime(now.year, now.month, now.day);
    final lastDate = DateTime(now.year, now.month + 4, 0);
    DateTime? awalnya = firstDate;

    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppTheme.bdr,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Sewa Kamar ${kamar.nama}',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '${_properti?.nama ?? ''} Â· ${AppTheme.formatRupiah(kamar.hargaSewaBulanan)}/${kamar.jenisHarga == 'harian' ? 'hari' : 'bln'}',
                          style: TextStyle(
                            fontSize: 12,
                            color: AppTheme.txtSec,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.pop(ctx, false),
                    icon: const Icon(Icons.close_rounded),
                    color: AppTheme.txtMuted,
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                'Kamar yang tersedia akan langsung terkunci untukmu — tanpa menunggu konfirmasi. Pilih tanggal kamu berencana masuk (maksimal 3 bulan ke depan).',
                style: TextStyle(fontSize: 12, color: AppTheme.txtMuted),
              ),
              const SizedBox(height: 16),
              Text(
                'Tanggal Masuk',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.txtSec,
                ),
              ),
              const SizedBox(height: 6),
              InkWell(
                onTap: () async {
                  final picked = await showDatePicker(
                    context: ctx,
                    initialDate: awalnya,
                    firstDate: firstDate,
                    lastDate: lastDate,
                    helpText: 'Kapan kamu rencana masuk?',
                    cancelText: 'Batal',
                    confirmText: 'OK',
                    builder: (context, child) => Theme(
                      data: Theme.of(context).copyWith(
                        colorScheme: ColorScheme.fromSeed(
                          seedColor: AppTheme.primary,
                        ),
                      ),
                      child: child!,
                    ),
                  );
                  if (picked != null) {
                    setSheetState(() => awalnya = picked);
                  }
                },
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 14,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0FDFA),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: AppTheme.primary.withValues(alpha: 0.3),
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.calendar_today_rounded,
                        color: AppTheme.primary,
                        size: 18,
                      ),
                      const SizedBox(width: 10),
                      Text(
                        awalnya == null
                            ? 'Pilih tanggal masuk'
                            : DateFormat('EEEE, d MMMM yyyy').format(
                                awalnya!,
                              ),
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.txt,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(ctx, false),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      child: const Text('Batal'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () => Navigator.pop(ctx, true),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.accent,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      child: const Text(
                        'Sewa Sekarang',
                        style: TextStyle(fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );

    if (confirmed != true || awalnya == null || !mounted) return;

    final tanggalMasuk = awalnya!;
    final tanggal =
        '${tanggalMasuk.year.toString().padLeft(4, '0')}-'
        '${tanggalMasuk.month.toString().padLeft(2, '0')}-'
        '${tanggalMasuk.day.toString().padLeft(2, '0')}';

    setState(() => _bookingKamarId = kamar.id);
    try {
      final res = await KatalogService.sewaKamar(
        _properti!.id,
        kamar.id,
        tanggalMasuk: tanggal,
      );
      if (!mounted) return;
      setState(() {
        _bookingKamarId = null;
        _bookedKamarIds.add(kamar.id);
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Kamar berhasil dipesan.'),
          backgroundColor: AppTheme.success,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _bookingKamarId = null);
      final pesan = e is ApiException ? e.message : 'Gagal memesan kamar.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(pesan), backgroundColor: AppTheme.error),
      );
    }
  }

  Widget _buildDetailFacilityIcon(String label) {
    final asset = FacilityIcon.assetFor(label);
    if (asset != null) {
      return Image.asset(
        asset,
        width: 22,
        height: 22,
        errorBuilder: (_, _, _) => const SizedBox(width: 22, height: 22),
      );
    }
    return Icon(
      FacilityIcon.materialFor(label) ?? Icons.check_circle_rounded,
      size: 22,
      color: AppTheme.primary,
    );
  }

  Widget _buildInfoGrid() {
    final kamars = _properti!.kamars ?? [];
    final tersediaKamars = kamars
        .where((k) => _statusKamar(k) == 'tersedia')
        .toList();
    int? termurah;
    if (tersediaKamars.isNotEmpty) {
      termurah = tersediaKamars
          .map((k) => k.hargaSewaBulanan)
          .reduce((a, b) => a < b ? a : b);
    }
    final denda = _properti!.dendaPerHari;
    final kontak = _properti!.pemilik?.noHp;

    Widget tile(
      String label,
      String value, {
      String? suffix,
      Color? valueColor,
    }) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: AppTheme.surfaceGrey,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Text(
              label.toUpperCase(),
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: AppTheme.txtMuted,
                letterSpacing: 0.3,
              ),
            ),
            const SizedBox(height: 6),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: Text.rich(
                TextSpan(
                  children: [
                    TextSpan(
                      text: value,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: valueColor ?? AppTheme.txt,
                      ),
                    ),
                    if (suffix != null)
                      TextSpan(
                        text: suffix,
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                          color: AppTheme.txtMuted,
                        ),
                      ),
                  ],
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      );
    }

    return Row(
      children: [
        Expanded(
          child: tile(
            'Harga Mulai',
            termurah != null
                ? AppTheme.formatRupiah(termurah).replaceFirst('Rp ', '')
                : 'Penuh',
            suffix: termurah != null ? '/bln' : null,
            valueColor: termurah != null
                ? AppTheme.primary
                : AppTheme.txtMuted,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: tile(
            'Denda',
            denda != null && denda > 0
                ? AppTheme.formatRupiah(denda).replaceFirst('Rp ', '')
                : 'Tidak ada',
            suffix: denda != null && denda > 0 ? '/hr' : null,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(child: tile('Kontak', kontak ?? '-')),
      ],
    );
  }
}

class _InfoChip extends StatelessWidget {
  final String label;
  final Color color;

  const _InfoChip({required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }
}
