<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Premium Pemilik Kos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 18px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; color: #7c3aed; }
        .header p { margin: 0; color: #6b7280; font-size: 11px; }
        h2 { font-size: 13px; margin: 16px 0 8px; color: #111827; border-bottom: 2px solid #7c3aed; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; font-size: 11px; }
        th { background: #7c3aed; color: #ffffff; font-weight: bold; }
        tr:nth-child(even) td { background: #f9fafb; }
        .ringkasan td:first-child { font-weight: bold; width: 50%; }
        .footer { margin-top: 18px; font-size: 10px; color: #9ca3af; text-align: center; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Premium Pemilik Kos</h1>
        <p>{{ $periode }} ({{ $bulan }})</p>
        <p>Rentang bulan: {{ $periode_trend }}</p>
    </div>

    <h2>Ringkasan</h2>
    <table class="ringkasan">
        <tr><td>Total Properti</td><td>{{ $ringkasan['total_properti'] }}</td></tr>
        <tr><td>Total Kamar</td><td>{{ $ringkasan['total_kamar'] }}</td></tr>
        <tr><td>Kamar Terisi</td><td>{{ $ringkasan['kamar_terisi'] }}</td></tr>
        <tr><td>Penyewaan Aktif</td><td>{{ $ringkasan['penyewaan_aktif'] }}</td></tr>
        <tr><td>Pendapatan</td><td>Rp {{ number_format($ringkasan['pendapatan'], 0, ',', '.') }}</td></tr>
        <tr><td>Pengeluaran</td><td>Rp {{ number_format($ringkasan['pengeluaran'], 0, ',', '.') }}</td></tr>
        <tr><td>Untung Bersih</td><td>Rp {{ number_format($ringkasan['laba_bersih'], 0, ',', '.') }}</td></tr>
        <tr><td>Jumlah Transaksi</td><td>{{ $ringkasan['jumlah_transaksi'] }}</td></tr>
    </table>

    <h2>Tagihan Belum Bayar</h2>
    <table>
        <tr><th>Status</th><th>Nilai (Rp)</th></tr>
        <tr><td>Belum waktunya bayar</td><td>Rp {{ number_format($aging['belum_jatuh_tempo'], 0, ',', '.') }}</td></tr>
        <tr><td>Baru telat, di bawah seminggu</td><td>Rp {{ number_format($aging['telat_1_7'], 0, ',', '.') }}</td></tr>
        <tr><td>Telat sampai sebulan</td><td>Rp {{ number_format($aging['telat_8_30'], 0, ',', '.') }}</td></tr>
        <tr><td>Telat lebih dari sebulan, segera tagih</td><td>Rp {{ number_format($aging['telat_lebih_30'], 0, ',', '.') }}</td></tr>
    </table>

    @if (count($tagihan_belum) > 0)
        <h2>Siapa yang belum bayar (maks 50)</h2>
        <table>
            <tr><th>Penyewa</th><th>Kamar</th><th>Periode</th><th>Jumlah + Denda (Rp)</th><th>Jatuh Tempo</th></tr>
            @foreach ($tagihan_belum as $t)
                <tr>
                    <td>{{ $t['anak_kos_nama'] }}</td>
                    <td>{{ $t['kamar_nama'] }}</td>
                    <td>{{ $t['periode'] }}</td>
                    <td>{{ number_format($t['jumlah'] + $t['denda'], 0, ',', '.') }}</td>
                    <td class="nowrap">{{ $t['jatuh_tempo'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <h2>Kos Pemasukan Terbesar</h2>
    <table>
        <tr><th>#</th><th>Nama</th><th>Total Kamar</th><th>Kamar Terisi</th><th>Pendapatan (Rp)</th></tr>
        @forelse ($top_properti as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p['nama'] }}</td>
                <td>{{ $p['total_kamar'] }}</td>
                <td>{{ $p['kamar_terisi'] }}</td>
                <td>{{ number_format($p['pendapatan'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada data.</td></tr>
        @endforelse
    </table>

    <h2>Naik Turun Tiap Bulan</h2>
    <table>
        <tr><th>Bulan</th><th>Pendapatan (Rp)</th><th>Pengeluaran (Rp)</th><th>Untung Bersih (Rp)</th><th>Kamar Terisi (%)</th></tr>
        @foreach ($trend['labels'] as $i => $label)
            <tr>
                <td>{{ $label }}</td>
                <td>{{ number_format($trend['pendapatan'][$i], 0, ',', '.') }}</td>
                <td>{{ number_format($trend['pengeluaran'][$i], 0, ',', '.') }}</td>
                <td>{{ number_format($trend['laba'][$i], 0, ',', '.') }}</td>
                <td>{{ $trend['okupansi'][$i] }}%</td>
            </tr>
        @endforeach
    </table>

    @if (count($kategori_pengeluaran) > 0)
        <h2>Pengeluaran per Kategori</h2>
        <table>
            <tr><th>Kategori</th><th>Total (Rp)</th></tr>
            @foreach ($kategori_pengeluaran as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td>{{ number_format($item['value'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="footer">Dibuat pada {{ now()->translatedFormat('d F Y H:i') }} &bull; Ngekos.in</div>
</body>
</html>