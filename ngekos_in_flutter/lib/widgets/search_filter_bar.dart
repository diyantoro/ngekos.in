import 'package:flutter/material.dart';
import '../config/theme.dart';

class SearchFilterBar extends StatefulWidget {
  final TextEditingController? searchController;
  final Function(String) onSearch;
  final Function(Map<String, dynamic>)? onFilterChanged;
  final String? selectedKota;
  final int? selectedHargaMax;
  final int? selectedKapasitas;
  final List<String> kotaList;
  final bool showFilter;

  const SearchFilterBar({
    super.key,
    this.searchController,
    required this.onSearch,
    this.onFilterChanged,
    this.selectedKota,
    this.selectedHargaMax,
    this.selectedKapasitas,
    this.kotaList = const [],
    this.showFilter = true,
  });

  @override
  State<SearchFilterBar> createState() => _SearchFilterBarState();
}

class _SearchFilterBarState extends State<SearchFilterBar> {
  late TextEditingController _controller;

  @override
  void initState() {
    super.initState();
    _controller = widget.searchController ?? TextEditingController();
  }

  void _showFilterSheet() {
    String? kota = widget.selectedKota;
    int? hargaMax = widget.selectedHargaMax;
    int? kapasitas = widget.selectedKapasitas;

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(width: 40, height: 4, decoration: BoxDecoration(color: AppTheme.bdr, borderRadius: BorderRadius.circular(2))),
              ),
              const SizedBox(height: 16),
              const Text('Filter Pencarian', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              const Text('Kota', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                initialValue: kota,
                decoration: const InputDecoration(border: OutlineInputBorder(), contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10)),
                hint: const Text('Semua Kota'),
                items: [
                  const DropdownMenuItem(value: null, child: Text('Semua Kota')),
                  ...widget.kotaList.map((k) => DropdownMenuItem(value: k, child: Text(k))),
                ],
                onChanged: (v) => setSheetState(() => kota = v),
              ),
              const SizedBox(height: 16),
              const Text('Harga Maksimal', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 8),
              DropdownButtonFormField<int>(
                initialValue: hargaMax,
                decoration: const InputDecoration(border: OutlineInputBorder(), contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10)),
                hint: const Text('Semua Harga'),
                items: const [
                  DropdownMenuItem(value: null, child: Text('Semua Harga')),
                  DropdownMenuItem(value: 500000, child: Text('Maks Rp 500.000')),
                  DropdownMenuItem(value: 1000000, child: Text('Maks Rp 1.000.000')),
                  DropdownMenuItem(value: 2000000, child: Text('Maks Rp 2.000.000')),
                  DropdownMenuItem(value: 5000000, child: Text('Maks Rp 5.000.000')),
                ],
                onChanged: (v) => setSheetState(() => hargaMax = v),
              ),
              const SizedBox(height: 16),
              const Text('Kapasitas', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
              const SizedBox(height: 8),
              DropdownButtonFormField<int>(
                initialValue: kapasitas,
                decoration: const InputDecoration(border: OutlineInputBorder(), contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10)),
                hint: const Text('Semua'),
                items: const [
                  DropdownMenuItem(value: null, child: Text('Semua')),
                  DropdownMenuItem(value: 1, child: Text('1 Orang')),
                  DropdownMenuItem(value: 2, child: Text('2 Orang')),
                  DropdownMenuItem(value: 3, child: Text('3 Orang')),
                  DropdownMenuItem(value: 4, child: Text('4+ Orang')),
                ],
                onChanged: (v) => setSheetState(() => kapasitas = v),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () {
                        Navigator.pop(ctx);
                        widget.onFilterChanged?.call({'kota': null, 'hargaMax': null, 'kapasitas': null});
                      },
                      child: const Text('Reset'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(ctx);
                        widget.onFilterChanged?.call({'kota': kota, 'hargaMax': hargaMax, 'kapasitas': kapasitas});
                      },
                      child: const Text('Terapkan'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppTheme.card,
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _controller,
              decoration: InputDecoration(
                hintText: 'Cari kos...',
                prefixIcon: Icon(Icons.search_rounded, color: AppTheme.txtSec),
                suffixIcon: _controller.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear_rounded),
                        onPressed: () {
                          _controller.clear();
                          widget.onSearch('');
                          setState(() {});
                        },
                      )
                    : null,
                filled: true,
                fillColor: AppTheme.bg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onSubmitted: widget.onSearch,
              onChanged: (_) => setState(() {}),
            ),
          ),
          if (widget.showFilter) ...[
            const SizedBox(width: 8),
            GestureDetector(
              onTap: _showFilterSheet,
              child: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: AppTheme.surfaceGrey,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(Icons.tune_rounded, color: AppTheme.txtSec, size: 20),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
