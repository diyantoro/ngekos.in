<?php

namespace App\Http\Controllers;

use App\Services\PemilikLaporanPremiumService;
use App\Services\SubscriptionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

        return $data;
    }

    public function pdf(Request $request): StreamedResponse
    {
        $data = $this->garantikanAkses($request);
        $nama = 'laporan-premium-'.$data['bulan'].'.pdf';

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
        $nama = 'laporan-premium-'.$data['bulan'].'.xlsx';

        $spreadsheet = new Spreadsheet();
        $this->sheetRingkasan($spreadsheet->getActiveSheet(), $data);
        $this->sheetAging($spreadsheet->createSheet(), $data);
        $this->sheetTopProperti($spreadsheet->createSheet(), $data);
        $this->sheetTren($spreadsheet->createSheet(), $data);
        $this->sheetKategori($spreadsheet->createSheet(), $data);

        if (($data['tier'] ?? 'pro') === 'business') {
            $this->sheetRincianTiapKos($spreadsheet->createSheet(), $data);
            $this->sheetTransaksiDetail($spreadsheet->createSheet(), $data);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $nama,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'X-Report-Tier' => $data['tier']],
        );
    }

    private function sheetRingkasan(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Laporan Premium Pemilik Kos');
        $sheet->setCellValue('A2', 'Periode: '.$data['periode'].' ('.$data['bulan'].')');
        $sheet->setCellValue('A3', 'Rentang bulan: '.($data['periode_trend'] ?? ''));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $paket = [
            'Total Properti' => $data['ringkasan']['total_properti'],
            'Total Kamar' => $data['ringkasan']['total_kamar'],
            'Kamar Terisi' => $data['ringkasan']['kamar_terisi'],
            'Penyewaan Aktif' => $data['ringkasan']['penyewaan_aktif'],
            'Pendapatan' => $data['ringkasan']['pendapatan'],
            'Pengeluaran' => $data['ringkasan']['pengeluaran'],
            'Untung Bersih' => $data['ringkasan']['laba_bersih'],
            'Jumlah Transaksi' => $data['ringkasan']['jumlah_transaksi'],
        ];

        $baris = 4;
        foreach ($paket as $label => $nilai) {
            $sheet->setCellValue("A{$baris}", $label);
            $sheet->setCellValue("B{$baris}", 'Rp '.number_format((int) $nilai, 0, ',', '.'));
            $baris++;
        }
    }

    private function sheetAging(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Tagihan Belum Bayar');
        $sheet->setCellValue('A1', 'Status');
        $sheet->setCellValue('B1', 'Nilai (Rp)');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $buckets = [
            'Belum waktunya bayar' => $data['aging']['belum_jatuh_tempo'],
            'Baru telat, di bawah seminggu' => $data['aging']['telat_1_7'],
            'Telat sampai sebulan' => $data['aging']['telat_8_30'],
            'Telat lebih dari sebulan, segera tagih' => $data['aging']['telat_lebih_30'],
        ];

        $baris = 2;
        foreach ($buckets as $label => $nilai) {
            $sheet->setCellValue("A{$baris}", $label);
            $sheet->setCellValue("B{$baris}", number_format((int) $nilai, 0, ',', '.'));
            $baris++;
        }

        $baris += 2;
        $sheet->setCellValue("A{$baris}", 'Siapa yang belum bayar');
        $sheet->getStyle("A{$baris}")->getFont()->setBold(true);
        $baris++;
        $sheet->setCellValue("A{$baris}", 'Penyewa');
        $sheet->setCellValue("B{$baris}", 'Kamar');
        $sheet->setCellValue("C{$baris}", 'Periode');
        $sheet->setCellValue("D{$baris}", 'Jumlah + Denda (Rp)');
        $sheet->setCellValue("E{$baris}", 'Jatuh Tempo');
        $sheet->getStyle("A{$baris}:E{$baris}")->getFont()->setBold(true);
        $baris++;
        foreach ($data['tagihan_belum'] as $t) {
            $sheet->setCellValue("A{$baris}", $t['anak_kos_nama']);
            $sheet->setCellValue("B{$baris}", $t['kamar_nama']);
            $sheet->setCellValue("C{$baris}", $t['periode']);
            $sheet->setCellValue("D{$baris}", number_format((float) ($t['jumlah'] + $t['denda']), 0, ',', '.'));
            $sheet->setCellValue("E{$baris}", $t['jatuh_tempo']);
            $baris++;
        }
    }

    private function sheetTopProperti(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Kos Pemasukan Terbesar');
        $sheet->setCellValue('A1', 'Peringkat');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Total Kamar');
        $sheet->setCellValue('D1', 'Kamar Terisi');
        $sheet->setCellValue('E1', 'Pendapatan Periode (Rp)');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        $baris = 2;
        foreach ($data['top_properti'] as $i => $p) {
            $sheet->setCellValue("A{$baris}", $i + 1);
            $sheet->setCellValue("B{$baris}", $p['nama']);
            $sheet->setCellValue("C{$baris}", $p['total_kamar']);
            $sheet->setCellValue("D{$baris}", $p['kamar_terisi']);
            $sheet->setCellValue("E{$baris}", number_format((int) $p['pendapatan'], 0, ',', '.'));
            $baris++;
        }
    }

    private function sheetTren(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Naik Turun Tiap Bulan');
        $sheet->setCellValue('A1', 'Bulan');
        $sheet->setCellValue('B1', 'Pendapatan (Rp)');
        $sheet->setCellValue('C1', 'Pengeluaran (Rp)');
        $sheet->setCellValue('D1', 'Untung Bersih (Rp)');
        $sheet->setCellValue('E1', 'Kamar Terisi (%)');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        $trend = $data['trend'];
        for ($i = 0; $i < count($trend['labels']); $i++) {
            $baris = $i + 2;
            $sheet->setCellValue("A{$baris}", $trend['labels'][$i]);
            $sheet->setCellValue("B{$baris}", number_format((int) $trend['pendapatan'][$i], 0, ',', '.'));
            $sheet->setCellValue("C{$baris}", number_format((int) $trend['pengeluaran'][$i], 0, ',', '.'));
            $sheet->setCellValue("D{$baris}", number_format((int) $trend['laba'][$i], 0, ',', '.'));
            $sheet->setCellValue("E{$baris}", (int) $trend['okupansi'][$i]);
        }
    }

    private function sheetKategori(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Kategori Pengeluaran');
        $sheet->setCellValue('A1', 'Kategori');
        $sheet->setCellValue('B1', 'Total (Rp)');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        $baris = 2;
        foreach ($data['kategori_pengeluaran'] as $item) {
            $sheet->setCellValue("A{$baris}", $item['label']);
            $sheet->setCellValue("B{$baris}", number_format((int) $item['value'], 0, ',', '.'));
            $baris++;
        }
    }

    private function sheetRincianTiapKos(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Rincian Tiap Kos');
        $sheet->setCellValue('A1', 'Nama Kos');
        $sheet->setCellValue('B1', 'Total Kamar');
        $sheet->setCellValue('C1', 'Kamar Terisi');
        $sheet->setCellValue('D1', 'Tingkat Terisi (%)');
        $sheet->setCellValue('E1', 'Pendapatan (Rp)');
        $sheet->setCellValue('F1', 'Pengeluaran (Rp)');
        $sheet->setCellValue('G1', 'Untung Bersih (Rp)');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $baris = 2;
        foreach ($data['rincian_tiap_kos'] ?? [] as $p) {
            $sheet->setCellValue("A{$baris}", $p['nama']);
            $sheet->setCellValue("B{$baris}", (int) $p['total_kamar']);
            $sheet->setCellValue("C{$baris}", (int) $p['kamar_terisi']);
            $sheet->setCellValue("D{$baris}", (int) $p['tingkat_terisi']);
            $sheet->setCellValue("E{$baris}", number_format((int) $p['pendapatan'], 0, ',', '.'));
            $sheet->setCellValue("F{$baris}", number_format((int) $p['pengeluaran'], 0, ',', '.'));
            $sheet->setCellValue("G{$baris}", number_format((int) $p['untung_bersih'], 0, ',', '.'));
            $baris++;
        }
    }

    private function sheetTransaksiDetail(Worksheet $sheet, array $data): void
    {
        $sheet->setTitle('Daftar Transaksi Detail');
        $sheet->setCellValue('A1', 'Tanggal');
        $sheet->setCellValue('B1', 'Penyewa');
        $sheet->setCellValue('C1', 'Kos');
        $sheet->setCellValue('D1', 'Kamar');
        $sheet->setCellValue('E1', 'Periode');
        $sheet->setCellValue('F1', 'Metode');
        $sheet->setCellValue('G1', 'Jumlah (Rp)');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $baris = 2;
        foreach ($data['transaksi_detail'] ?? [] as $t) {
            $sheet->setCellValue("A{$baris}", $t['tanggal']);
            $sheet->setCellValue("B{$baris}", $t['penyewa']);
            $sheet->setCellValue("C{$baris}", $t['kos']);
            $sheet->setCellValue("D{$baris}", $t['kamar']);
            $sheet->setCellValue("E{$baris}", $t['periode']);
            $sheet->setCellValue("F{$baris}", $t['metode']);
            $sheet->setCellValue("G{$baris}", number_format((float) $t['jumlah'], 0, ',', '.'));
            $baris++;
        }
    }
}