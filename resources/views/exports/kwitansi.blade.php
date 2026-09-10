<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi {{ $pembayaran->nomor_kwitansi }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .box { border: 2px solid #0d9488; border-radius: 8px; padding: 20px 24px; }
        .header { text-align: center; border-bottom: 2px solid #0d9488; padding-bottom: 10px; margin-bottom: 14px; }
        .header h1 { font-size: 20px; margin: 0; color: #0d9488; letter-spacing: 2px; }
        .header p { margin: 2px 0 0; color: #6b7280; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 6px; vertical-align: top; }
        .label { width: 38%; color: #6b7280; }
        .nilai { font-weight: bold; }
        .total { margin-top: 10px; border-top: 2px dashed #0d9488; padding-top: 10px; }
        .total td { font-size: 14px; }
        .ttd { margin-top: 26px; width: 100%; }
        .ttd td { text-align: center; font-size: 11px; }
        .footer { margin-top: 16px; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="box">
        <div class="header">
            <h1>KWITANSI PEMBAYARAN KOS</h1>
            <p>Ngekos.in &bull; Bukti pembayaran sah yang diverifikasi pemilik/admin</p>
        </div>

        <table>
            <tr><td class="label">Nomor Kwitansi</td><td class="nilai">{{ $pembayaran->nomor_kwitansi }}</td></tr>
            <tr><td class="label">Telah diterima dari</td><td class="nilai">{{ $anakKos?->nama ?? '-' }}</td></tr>
            <tr><td class="label">Kos / Kamar</td><td class="nilai">{{ $properti?->nama ?? '-' }}{{ $kamar ? ' — Kamar '.$kamar->nama : '' }}</td></tr>
            <tr><td class="label">Periode Tagihan</td><td>{{ $tagihan?->periode ?? '-' }}</td></tr>
            <tr><td class="label">Metode</td><td>{{ $pembayaran->labelMetode() }}</td></tr>
            <tr><td class="label">Tanggal Bayar (verifikasi)</td><td>{{ $pembayaran->verified_at?->translatedFormat('d F Y H:i') ?? '-' }}</td></tr>
            <tr><td class="label">Diverifikasi oleh</td><td>{{ $verifikator?->nama ?? '-' }}</td></tr>
        </table>

        <table class="total">
            <tr><td class="label">Jumlah dibayar</td><td class="nilai">Rp {{ number_format((float) $pembayaran->jumlah, 0, ',', '.') }}</td></tr>
        </table>

        <table class="ttd">
            <tr>
                <td>Yang membayar,<br><br><br><br>({{ $anakKos?->nama ?? '....................' }})</td>
                <td>Penerima / Verifikator,<br><br><br><br>({{ $verifikator?->nama ?? '....................' }})</td>
            </tr>
        </table>

        <div class="footer">Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} &bull; {{ $pembayaran->nomor_kwitansi }} &bull; Ngekos.in</div>
    </div>
</body>
</html>
