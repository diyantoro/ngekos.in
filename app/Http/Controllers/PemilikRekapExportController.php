<?php

namespace App\Http\Controllers;

use App\Services\PemilikRekapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemilikRekapExportController extends Controller
{
    public function pdf(Request $request): StreamedResponse
    {
        try {
            $data = PemilikRekapService::data($request->user()->id, $request->query('bulan'));
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }

        $nama = 'rekap-pemilik-'.$data['bulan'].'.pdf';

        $pdf = Pdf::loadView('exports.rekap-pemilik', $data)
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $nama,
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function excel(Request $request): StreamedResponse
    {
        try {
            $data = PemilikRekapService::data($request->user()->id, $request->query('bulan'));
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }

        $nama = 'rekap-pemilik-'.$data['bulan'].'.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Rekap Bulanan Pemilik Kos');
        $sheet->setCellValue('A2', 'Periode: '.$data['periode'].' ('.$data['bulan'].')');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $ringkasan = $data['ringkasan'];
        $baris = 4;
        $paket = [
            'Total Properti' => $ringkasan['total_properti'],
            'Total Kamar' => $ringkasan['total_kamar'],
            'Kamar Terisi' => $ringkasan['kamar_terisi'],
            'Penyewaan Aktif' => $ringkasan['penyewaan_aktif'],
            'Pendapatan' => $ringkasan['pendapatan'],
            'Pengeluaran' => $ringkasan['pengeluaran'],
            'Laba Bersih' => $ringkasan['laba_bersih'],
            'Jumlah Transaksi' => $ringkasan['jumlah_transaksi'],
        ];
        foreach ($paket as $label => $nilai) {
            $sheet->setCellValue("A{$baris}", $label);
            $sheet->setCellValue("B{$baris}", "Rp ".number_format((int) $nilai, 0, ',', '.'));
            $baris++;
        }

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Pendapatan & Pengeluaran');
        $sheet->setCellValue('A1', 'Kategori');
        $sheet->setCellValue('B1', 'Total (Rp)');
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $baris = 2;
        foreach ($data['kategori_pengeluaran'] as $item) {
            $sheet->setCellValue("A{$baris}", $item['label']);
            $sheet->setCellValue("B{$baris}", number_format((int) $item['value'], 0, ',', '.'));
            $baris++;
        }

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Properti');
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'Alamat');
        $sheet->setCellValue('C1', 'Total Kamar');
        $sheet->setCellValue('D1', 'Kamar Terisi');
        $sheet->setCellValue('E1', 'Status');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $baris = 2;
        foreach ($data['propertis'] as $p) {
            $sheet->setCellValue("A{$baris}", $p['nama']);
            $sheet->setCellValue("B{$baris}", $p['alamat']);
            $sheet->setCellValue("C{$baris}", $p['total_kamar']);
            $sheet->setCellValue("D{$baris}", $p['kamar_terisi']);
            $sheet->setCellValue("E{$baris}", $p['status']);
            $baris++;
        }

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Penyewaan');
        $sheet->setCellValue('A1', 'Penyewa');
        $sheet->setCellValue('B1', 'Kamar');
        $sheet->setCellValue('C1', 'Kos');
        $sheet->setCellValue('D1', 'Masuk');
        $sheet->setCellValue('E1', 'Keluar');
        $sheet->setCellValue('F1', 'Status');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $baris = 2;
        foreach ($data['sewaans'] as $s) {
            $sheet->setCellValue("A{$baris}", $s['anak_kos_nama']);
            $sheet->setCellValue("B{$baris}", $s['kamar_nama']);
            $sheet->setCellValue("C{$baris}", $s['properti_nama']);
            $sheet->setCellValue("D{$baris}", $s['tanggal_masuk']);
            $sheet->setCellValue("E{$baris}", $s['tanggal_keluar'] ?? '');
            $sheet->setCellValue("F{$baris}", $s['status']);
            $baris++;
        }

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Transaksi');
        $sheet->setCellValue('A1', 'Penyewa');
        $sheet->setCellValue('B1', 'Periode');
        $sheet->setCellValue('C1', 'Metode');
        $sheet->setCellValue('D1', 'Jumlah (Rp)');
        $sheet->setCellValue('E1', 'Status');
        $sheet->setCellValue('F1', 'Diverifikasi');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $baris = 2;
        foreach ($data['transaksi'] as $t) {
            $sheet->setCellValue("A{$baris}", $t['anak_kos_nama']);
            $sheet->setCellValue("B{$baris}", $t['periode']);
            $sheet->setCellValue("C{$baris}", $t['metode']);
            $sheet->setCellValue("D{$baris}", number_format((int) $t['jumlah'], 0, ',', '.'));
            $sheet->setCellValue("E{$baris}", $t['status']);
            $sheet->setCellValue("F{$baris}", $t['verified_at']);
            $baris++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $nama,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}