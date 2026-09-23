<?php

namespace App\Http\Controllers;

use App\Services\PemilikLaporanPremiumService;
use App\Services\SubscriptionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ekspor Laporan Premium (hanya untuk paket dengan fitur export_report).
 *
 * Enforcement ganda: middleware `premium:export_report` di route + featureCheck
 * di method ini, sehingga pemanggilan langsung ke controller tetap terblokir.
 */
class PemilikLaporanPremiumController extends Controller
{
    private function garantikanAkses(Request $request): array
    {
        $user = $request->user();
        $cek = SubscriptionService::featureCheck($user, 'export_report');

        if (! $cek['allowed']) {
            abort(403, $cek['message']);
        }

        $tier = SubscriptionService::reportTier($user);

        try {
            $data = PemilikLaporanPremiumService::data(
                $user->id,
                $request->query('bulan'),
                SubscriptionService::clampPeriode($user, max(1, (int) $request->query('periode', 12))),
                $request->query('properti_id') ? (int) $request->query('properti_id') : null,
                $tier === 'business' ? 'business' : 'pro',
            );
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }

        $data['tier'] = $tier;
        $data['pemilik_nama'] = $user->nama;
        $data['diunduh_pada'] = now()->translatedFormat('d F Y H:i');

        return $data;
    }

    public function pdf(Request $request): StreamedResponse
    {
        $data = $this->garantikanAkses($request);
        $tier = strtoupper($data['tier'] ?? 'pro');
        $nama = "laporan-{$tier}-".$data['bulan'].'.pdf';

        $pdf = Pdf::loadView('exports.laporan-premium', $data)
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $nama,
            ['Content-Type' => 'application/pdf', 'X-Report-Tier' => $data['tier']],
        );
    }

    public function excel(Request $request): StreamedResponse
    {
        $data = $this->garantikanAkses($request);
        $tier = strtoupper($data['tier'] ?? 'pro');
        $nama = "laporan-{$tier}-".$data['bulan'].'.xlsx';

        $spreadsheet = new Spreadsheet();
        $this->sheetRingkasan($spreadsheet->getActiveSheet(), $data);
        $this->sheetAging($spreadsheet->createSheet(), $data);
        $this->sheetTopProperti($spreadsheet->createSheet(), $data);
        $this->sheetTren($spreadsheet->createSheet(), $data);
        $this->sheetKategori($spreadsheet->createSheet(), $data);

        if (($data['tier'] ?? 'pro') === 'business') {
            $this->sheetRincianTiapKos($spreadsheet->createSheet(), $data);
            $this->sheetTransaksiDetail($spreadsheet->createSheet(), $data);
            $this->sheetPertumbuhan($spreadsheet->createSheet(), $data);
            $this->sheetPenyewaMetode($spreadsheet->createSheet(), $data);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $nama,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'X-Report-Tier' => $data['tier']],
        );
    }

    /**
     * Baris judul + meta di tiap sheet. Header tabel selalu di baris 4,
     * data mulai baris 5. Mengembalikan nomor baris awal data (5).
     */
    private function tulisKepala(Worksheet $sheet, string $judulSheet, string $judul, array $meta, array $headers): int
    {
        $sheet->setTitle($judulSheet);
        $sheet->setCellValue('A1', $judul);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0D9488');

        $baris = 2;
        foreach ($meta as $m) {
            $sheet->setCellValue("A{$baris}", $m);
            $sheet->getStyle("A{$baris}")->getFont()->setSize(10)->getColor()->setRGB('6B7280');
            $baris++;
        }

        $kolom = 1;
        foreach ($headers as $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($kolom).'4', $h);
            $kolom++;
        }
        $akhir = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A4:{$akhir}4")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A4:{$akhir}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0D9488');

        return 5;
    }

    private function metaDasar(array $data): array
    {
        $kos = 'Semua kos';
        if (! empty($data['properti_terpilih'])) {
            foreach ($data['daftar_properti'] ?? [] as $p) {
                if ((int) ($p['id'] ?? 0) === (int) $data['properti_terpilih']) {
                    $kos = $p['nama'] ?? $kos;
                    break;
                }
            }
        }

        return [
            'Pemilik: '.($data['pemilik_nama'] ?? '-').'  •  Paket: '.strtoupper($data['tier'] ?? 'pro'),
            'Periode: '.($data['periode_trend'] ?? '').' ('.($data['bulan_count'] ?? 0).' bulan)  •  Kos: '.$kos,
            'Diunduh: '.($data['diunduh_pada'] ?? ''),
        ];
    }

    /**
     * Rapikan sheet tabel: bekukan baris judul, filter otomatis, lebar kolom
     * otomatis, format angka (Rp tanpa teks "Rp" agar bisa di-SUM), dan
     * baris TOTAL dengan rumus tepat di bawah data.
     *
     * @param int[] $kolomUang nomor kolom (1-based) berisi nominal rupiah
     * @param int[] $kolomPersen nomor kolom (1-based) berisi persen
     * @param array $kolomRataRata nomor kolom yang totalnya pakai AVERAGE, sisanya SUM
     */
    private function finalisasi(Worksheet $sheet, int $jumlahKolom, int $barisDataAkhir, array $kolomUang = [], array $kolomPersen = [], array $kolomRataRata = []): int
    {
        $akhir = Coordinate::stringFromColumnIndex($jumlahKolom);
        $barisAwal = 5;

        if ($barisDataAkhir >= $barisAwal) {
            $sheet->freezePane('A5');
            $sheet->setAutoFilter("A4:{$akhir}{$barisDataAkhir}");

            foreach ($kolomUang as $c) {
                $kol = Coordinate::stringFromColumnIndex($c);
                $sheet->getStyle("{$kol}{$barisAwal}:{$kol}{$barisDataAkhir}")
                    ->getNumberFormat()->setFormatCode('#,##0');
            }
            foreach ($kolomPersen as $c) {
                $kol = Coordinate::stringFromColumnIndex($c);
                $sheet->getStyle("{$kol}{$barisAwal}:{$kol}{$barisDataAkhir}")
                    ->getNumberFormat()->setFormatCode('0"%"');
            }

            $barisTotal = $barisDataAkhir + 1;
            $sheet->setCellValue("A{$barisTotal}", 'TOTAL');
            foreach (array_merge($kolomUang, $kolomPersen) as $c) {
                $kol = Coordinate::stringFromColumnIndex($c);
                $fungsi = in_array($c, $kolomRataRata, true) ? 'AVERAGE' : 'SUM';
                $sheet->setCellValue("{$kol}{$barisTotal}", "={$fungsi}({$kol}{$barisAwal}:{$kol}{$barisDataAkhir})");
            }
            $sheet->getStyle("A{$barisTotal}:{$akhir}{$barisTotal}")->getFont()->setBold(true);
            foreach ($kolomUang as $c) {
                $kol = Coordinate::stringFromColumnIndex($c);
                $sheet->getStyle("{$kol}{$barisTotal}")->getNumberFormat()->setFormatCode('#,##0');
            }
            foreach ($kolomPersen as $c) {
                $kol = Coordinate::stringFromColumnIndex($c);
                $sheet->getStyle("{$kol}{$barisTotal}")->getNumberFormat()->setFormatCode('0"%"');
            }

            $barisAkhir = $barisTotal;
        } else {
            $sheet->setCellValue('A5', 'Belum ada data pada periode ini.');
            $sheet->getStyle('A5')->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');
            $barisAkhir = 5;
        }

        foreach (range(1, $jumlahKolom) as $c) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->getPageSetup()->setFitToPage(true);

        return $barisAkhir;
    }

    private function tulisKosong(Worksheet $sheet): void
    {
        $sheet->setCellValue('A5', 'Belum ada data pada periode ini.');
        $sheet->getStyle('A5')->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');
    }

    private function sheetRingkasan(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Ringkasan Laporan Premium — '.strtoupper($data['tier'] ?? 'pro'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0D9488');
        foreach (array_values($this->metaDasar($data)) as $i => $m) {
            $sheet->setCellValue('A'.($i + 2), $m);
            $sheet->getStyle('A'.($i + 2))->getFont()->setSize(10)->getColor()->setRGB('6B7280');
        }

        $trendOkupansi = $data['trend']['okupansi'] ?? [];
        $rataOkupansi = count($trendOkupansi) ? (int) round(array_sum($trendOkupansi) / count($trendOkupansi)) : 0;

        $paket = [
            'Paket' => strtoupper($data['tier'] ?? 'pro'),
            'Jumlah bulan laporan' => (int) ($data['bulan_count'] ?? 0),
            'Total Properti' => (int) $data['ringkasan']['total_properti'],
            'Total Kamar' => (int) $data['ringkasan']['total_kamar'],
            'Kamar Terisi' => (int) $data['ringkasan']['kamar_terisi'],
            'Rata-rata Okupansi (%)' => $rataOkupansi,
            'Penyewaan Aktif' => (int) $data['ringkasan']['penyewaan_aktif'],
            'Pendapatan (Rp)' => (int) $data['ringkasan']['pendapatan'],
            'Pengeluaran (Rp)' => (int) $data['ringkasan']['pengeluaran'],
            'Untung Bersih (Rp)' => (int) $data['ringkasan']['laba_bersih'],
            'Jumlah Transaksi' => (int) $data['ringkasan']['jumlah_transaksi'],
        ];

        $baris = 5;
        foreach ($paket as $label => $nilai) {
            $sheet->setCellValue("A{$baris}", $label);
            $sheet->setCellValue("B{$baris}", $nilai);
            $sheet->getStyle("A{$baris}")->getFont()->setBold(true);
            $baris++;
        }
        $sheet->getStyle('B5:B'.($baris - 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }

    private function sheetAging(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Tagihan Belum Bayar', 'Umur Tagihan Belum Bayar', $this->metaDasar($data), ['Status', 'Nilai (Rp)']);

        $buckets = [
            'Belum waktunya bayar' => (int) $data['aging']['belum_jatuh_tempo'],
            'Baru telat, di bawah seminggu' => (int) $data['aging']['telat_1_7'],
            'Telat sampai sebulan' => (int) $data['aging']['telat_8_30'],
            'Telat lebih dari sebulan, segera tagih' => (int) $data['aging']['telat_lebih_30'],
        ];

        foreach ($buckets as $label => $nilai) {
            $sheet->setCellValue("A{$baris}", $label);
            $sheet->setCellValue("B{$baris}", $nilai);
            $baris++;
        }
        $this->finalisasi($sheet, 2, $baris - 1, [2]);

        $baris += 2;
        $sheet->setCellValue("A{$baris}", 'Siapa yang belum bayar');
        $sheet->getStyle("A{$baris}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0D9488');
        $baris++;

        $headers = ['Penyewa', 'Kos', 'Kamar', 'Periode', 'Jumlah (Rp)', 'Denda (Rp)', 'Total (Rp)', 'Jatuh Tempo'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).$baris, $h);
        }
        $sheet->getStyle("A{$baris}:H{$baris}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$baris}:H{$baris}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0D9488');
        $barisAwal = $baris + 1;
        $baris = $barisAwal;
        foreach ($data['tagihan_belum'] as $t) {
            $total = (float) $t['jumlah'] + (float) $t['denda'];
            $sheet->setCellValue("A{$baris}", $t['anak_kos_nama']);
            $sheet->setCellValue("B{$baris}", $t['kos_nama'] ?? '-');
            $sheet->setCellValue("C{$baris}", $t['kamar_nama']);
            $sheet->setCellValue("D{$baris}", $t['periode']);
            $sheet->setCellValue("E{$baris}", (float) $t['jumlah']);
            $sheet->setCellValue("F{$baris}", (float) $t['denda']);
            $sheet->setCellValue("G{$baris}", $total);
            $sheet->setCellValue("H{$baris}", $t['jatuh_tempo']);
            $baris++;
        }

        if ($baris > $barisAwal) {
            $sheet->freezePane('A'.($barisAwal + 1));
            $sheet->setAutoFilter("A{$barisAwal}:H".($baris - 1));
            foreach ([5, 6, 7] as $c) {
                $kol = Coordinate::stringFromColumnIndex($c);
                $sheet->getStyle("{$kol}{$barisAwal}:{$kol}".($baris - 1))->getNumberFormat()->setFormatCode('#,##0');
            }
            $sheet->setCellValue("A{$baris}", 'TOTAL');
            foreach ([5, 6, 7] as $c) {
                $kol = Coordinate::stringFromColumnIndex($c);
                $sheet->setCellValue("{$kol}{$baris}", "=SUM({$kol}{$barisAwal}:{$kol}".($baris - 1).')');
                $sheet->getStyle("{$kol}{$baris}")->getNumberFormat()->setFormatCode('#,##0');
            }
            $sheet->getStyle("A{$baris}:H{$baris}")->getFont()->setBold(true);
        } else {
            $sheet->setCellValue("A{$barisAwal}", 'Semua tagihan lunas. Bagus!');
            $sheet->getStyle("A{$barisAwal}")->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');
        }

        foreach (range(1, 8) as $c) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->getPageSetup()->setFitToPage(true);
    }

    private function sheetTopProperti(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Kos Pemasukan Terbesar', 'Kos Pemasukan Terbesar', $this->metaDasar($data), ['Peringkat', 'Nama', 'Total Kamar', 'Kamar Terisi', 'Okupansi (%)', 'Pendapatan Periode (Rp)']);

        $no = 1;
        foreach ($data['top_properti'] as $p) {
            $total = (int) $p['total_kamar'];
            $sheet->setCellValue("A{$baris}", $no++);
            $sheet->setCellValue("B{$baris}", $p['nama']);
            $sheet->setCellValue("C{$baris}", $total);
            $sheet->setCellValue("D{$baris}", (int) $p['kamar_terisi']);
            $sheet->setCellValue("E{$baris}", $total > 0 ? (int) round($p['kamar_terisi'] / $total * 100) : 0);
            $sheet->setCellValue("F{$baris}", (int) $p['pendapatan']);
            $baris++;
        }
        $this->finalisasi($sheet, 6, $baris - 1, [6], [5]);
    }

    private function sheetTren(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Naik Turun Tiap Bulan', 'Naik Turun Tiap Bulan', $this->metaDasar($data), ['Bulan', 'Pendapatan (Rp)', 'Pengeluaran (Rp)', 'Untung Bersih (Rp)', 'Kamar Terisi (%)']);

        $trend = $data['trend'];
        for ($i = 0; $i < count($trend['labels']); $i++) {
            $sheet->setCellValue("A{$baris}", $trend['labels'][$i]);
            $sheet->setCellValue("B{$baris}", (int) $trend['pendapatan'][$i]);
            $sheet->setCellValue("C{$baris}", (int) $trend['pengeluaran'][$i]);
            $sheet->setCellValue("D{$baris}", (int) $trend['laba'][$i]);
            $sheet->setCellValue("E{$baris}", (int) $trend['okupansi'][$i]);
            $baris++;
        }
        $this->finalisasi($sheet, 5, $baris - 1, [2, 3, 4], [5], [5]);
    }

    private function sheetKategori(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Kategori Pengeluaran', 'Pengeluaran per Kategori', $this->metaDasar($data), ['Kategori', 'Total (Rp)', 'Porsi (%)']);

        $items = $data['kategori_pengeluaran'] ?? [];
        $grand = array_sum(array_map(fn ($x) => (float) ($x['value'] ?? 0), $items));

        foreach ($items as $item) {
            $nilai = (float) ($item['value'] ?? 0);
            $sheet->setCellValue("A{$baris}", $item['label']);
            $sheet->setCellValue("B{$baris}", $nilai);
            $sheet->setCellValue("C{$baris}", $grand > 0 ? (int) round($nilai / $grand * 100) : 0);
            $baris++;
        }
        $this->finalisasi($sheet, 3, $baris - 1, [2], [3]);
    }

    private function sheetRincianTiapKos(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Rincian Tiap Kos', 'Rincian Tiap Kos (Khusus BUSINESS)', $this->metaDasar($data), ['Nama Kos', 'Total Kamar', 'Kamar Terisi', 'Tingkat Terisi (%)', 'Pendapatan (Rp)', 'Pengeluaran (Rp)', 'Untung Bersih (Rp)']);

        foreach ($data['rincian_tiap_kos'] ?? [] as $p) {
            $sheet->setCellValue("A{$baris}", $p['nama']);
            $sheet->setCellValue("B{$baris}", (int) $p['total_kamar']);
            $sheet->setCellValue("C{$baris}", (int) $p['kamar_terisi']);
            $sheet->setCellValue("D{$baris}", (int) $p['tingkat_terisi']);
            $sheet->setCellValue("E{$baris}", (int) $p['pendapatan']);
            $sheet->setCellValue("F{$baris}", (int) $p['pengeluaran']);
            $sheet->setCellValue("G{$baris}", (int) $p['untung_bersih']);
            $baris++;
        }
        $this->finalisasi($sheet, 7, $baris - 1, [5, 6, 7], [4]);
    }

    private function sheetTransaksiDetail(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Daftar Transaksi Detail', 'Daftar Transaksi Detail (Khusus BUSINESS)', $this->metaDasar($data), ['No', 'Tanggal', 'Penyewa', 'Kos', 'Kamar', 'Periode', 'Metode', 'Jumlah (Rp)']);

        $no = 1;
        foreach ($data['transaksi_detail'] ?? [] as $t) {
            $sheet->setCellValue("A{$baris}", $no++);
            $sheet->setCellValue("B{$baris}", $t['tanggal']);
            $sheet->setCellValue("C{$baris}", $t['penyewa']);
            $sheet->setCellValue("D{$baris}", $t['kos']);
            $sheet->setCellValue("E{$baris}", $t['kamar']);
            $sheet->setCellValue("F{$baris}", $t['periode']);
            $sheet->setCellValue("G{$baris}", $t['metode']);
            $sheet->setCellValue("H{$baris}", (float) $t['jumlah']);
            $baris++;
        }
        $this->finalisasi($sheet, 8, $baris - 1, [8]);
    }

    private function sheetPertumbuhan(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Pertumbuhan Bulanan', 'Pertumbuhan Bulan ke Bulan (Khusus BUSINESS)', $this->metaDasar($data), ['Bulan', 'Pendapatan (Rp)', 'Naik/Turun (%)', 'Pengeluaran (Rp)', 'Naik/Turun (%)', 'Untung Bersih (Rp)', 'Naik/Turun (%)', 'Okupansi (%)']);

        foreach ($data['pertumbuhan'] ?? [] as $p) {
            $sheet->setCellValue("A{$baris}", $p['bulan']);
            $sheet->setCellValue("B{$baris}", (int) $p['pendapatan']);
            $sheet->setCellValue("C{$baris}", (int) $p['pendapatan_pct']);
            $sheet->setCellValue("D{$baris}", (int) $p['pengeluaran']);
            $sheet->setCellValue("E{$baris}", (int) $p['pengeluaran_pct']);
            $sheet->setCellValue("F{$baris}", (int) $p['laba']);
            $sheet->setCellValue("G{$baris}", (int) $p['laba_pct']);
            $sheet->setCellValue("H{$baris}", (int) $p['okupansi']);
            $baris++;
        }
        $this->finalisasi($sheet, 8, $baris - 1, [2, 4, 6], [3, 5, 7, 8]);
    }

    private function sheetPenyewaMetode(Worksheet $sheet, array $data): void
    {
        $baris = $this->tulisKepala($sheet, 'Penyewa & Metode', 'Metode Pembayaran & Top Penyewa (Khusus BUSINESS)', $this->metaDasar($data), ['Metode', 'Jumlah Transaksi', 'Total (Rp)']);

        foreach ($data['metode_pembayaran'] ?? [] as $m) {
            $sheet->setCellValue("A{$baris}", $m['label']);
            $sheet->setCellValue("B{$baris}", (int) $m['jumlah_transaksi']);
            $sheet->setCellValue("C{$baris}", (int) $m['total']);
            $baris++;
        }
        $this->finalisasi($sheet, 3, $baris - 1, [3]);

        $baris += 2;
        $sheet->setCellValue("A{$baris}", 'Top 10 penyewa dengan pembayaran terbesar');
        $sheet->getStyle("A{$baris}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0D9488');
        $baris++;

        $headers = ['Peringkat', 'Penyewa', 'Jumlah Transaksi', 'Total Bayar (Rp)'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).$baris, $h);
        }
        $sheet->getStyle("A{$baris}:D{$baris}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$baris}:D{$baris}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0D9488');
        $barisAwal = $baris + 1;
        $baris = $barisAwal;
        $no = 1;
        foreach ($data['top_penyewa'] ?? [] as $p) {
            $sheet->setCellValue("A{$baris}", $no++);
            $sheet->setCellValue("B{$baris}", $p['nama']);
            $sheet->setCellValue("C{$baris}", (int) $p['jumlah_transaksi']);
            $sheet->setCellValue("D{$baris}", (int) $p['total']);
            $baris++;
        }

        if ($baris > $barisAwal) {
            $sheet->freezePane('A'.($barisAwal + 1));
            $sheet->setAutoFilter("A{$barisAwal}:D".($baris - 1));
            $sheet->getStyle("D{$barisAwal}:D".($baris - 1))->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue("A{$baris}", 'TOTAL');
            $sheet->setCellValue("D{$baris}", "=SUM(D{$barisAwal}:D".($baris - 1).')');
            $sheet->getStyle("D{$baris}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A{$baris}:D{$baris}")->getFont()->setBold(true);
        } else {
            $sheet->setCellValue("A{$barisAwal}", 'Belum ada data pada periode ini.');
            $sheet->getStyle("A{$barisAwal}")->getFont()->setItalic(true)->getColor()->setRGB('9CA3AF');
        }

        foreach (range(1, 4) as $c) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->getPageSetup()->setFitToPage(true);
    }
}