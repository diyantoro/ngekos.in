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
        <p>Paket <?php echo e(strtoupper($tier ?? 'pro')); ?> &bull; <?php echo e($pemilik_nama ?? ''); ?></p>
        <p><?php echo e($periode); ?> (<?php echo e($bulan); ?>)</p>
        <p>Rentang bulan: <?php echo e($periode_trend); ?> (<?php echo e($bulan_count ?? 0); ?> bulan)</p>
    </div>

    <h2>Ringkasan</h2>
    <table class="ringkasan">
        <tr><td>Total Properti</td><td><?php echo e($ringkasan['total_properti']); ?></td></tr>
        <tr><td>Total Kamar</td><td><?php echo e($ringkasan['total_kamar']); ?></td></tr>
        <tr><td>Kamar Terisi</td><td><?php echo e($ringkasan['kamar_terisi']); ?></td></tr>
        <tr><td>Penyewaan Aktif</td><td><?php echo e($ringkasan['penyewaan_aktif']); ?></td></tr>
        <tr><td>Pendapatan</td><td>Rp <?php echo e(number_format($ringkasan['pendapatan'], 0, ',', '.')); ?></td></tr>
        <tr><td>Pengeluaran</td><td>Rp <?php echo e(number_format($ringkasan['pengeluaran'], 0, ',', '.')); ?></td></tr>
        <tr><td>Untung Bersih</td><td>Rp <?php echo e(number_format($ringkasan['laba_bersih'], 0, ',', '.')); ?></td></tr>
        <tr><td>Jumlah Transaksi</td><td><?php echo e($ringkasan['jumlah_transaksi']); ?></td></tr>
    </table>

    <h2>Tagihan Belum Bayar</h2>
    <table>
        <tr><th>Status</th><th>Nilai (Rp)</th></tr>
        <tr><td>Belum waktunya bayar</td><td>Rp <?php echo e(number_format($aging['belum_jatuh_tempo'], 0, ',', '.')); ?></td></tr>
        <tr><td>Baru telat, di bawah seminggu</td><td>Rp <?php echo e(number_format($aging['telat_1_7'], 0, ',', '.')); ?></td></tr>
        <tr><td>Telat sampai sebulan</td><td>Rp <?php echo e(number_format($aging['telat_8_30'], 0, ',', '.')); ?></td></tr>
        <tr><td>Telat lebih dari sebulan, segera tagih</td><td>Rp <?php echo e(number_format($aging['telat_lebih_30'], 0, ',', '.')); ?></td></tr>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($tagihan_belum) > 0): ?>
        <h2>Siapa yang belum bayar (maks 50)</h2>
        <table>
            <tr><th>Penyewa</th><th>Kos</th><th>Kamar</th><th>Periode</th><th>Jumlah + Denda (Rp)</th><th>Jatuh Tempo</th></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tagihan_belum; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($t['anak_kos_nama']); ?></td>
                    <td><?php echo e($t['kos_nama'] ?? '-'); ?></td>
                    <td><?php echo e($t['kamar_nama']); ?></td>
                    <td><?php echo e($t['periode']); ?></td>
                    <td><?php echo e(number_format($t['jumlah'] + $t['denda'], 0, ',', '.')); ?></td>
                    <td class="nowrap"><?php echo e($t['jatuh_tempo']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <h2>Kos Pemasukan Terbesar</h2>
    <table>
        <tr><th>#</th><th>Nama</th><th>Total Kamar</th><th>Kamar Terisi</th><th>Pendapatan (Rp)</th></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $top_properti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($p['nama']); ?></td>
                <td><?php echo e($p['total_kamar']); ?></td>
                <td><?php echo e($p['kamar_terisi']); ?></td>
                <td><?php echo e(number_format($p['pendapatan'], 0, ',', '.')); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5">Belum ada data.</td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <h2>Naik Turun Tiap Bulan</h2>
    <table>
        <tr><th>Bulan</th><th>Pendapatan (Rp)</th><th>Pengeluaran (Rp)</th><th>Untung Bersih (Rp)</th><th>Kamar Terisi (%)</th></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $trend['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($label); ?></td>
                <td><?php echo e(number_format($trend['pendapatan'][$i], 0, ',', '.')); ?></td>
                <td><?php echo e(number_format($trend['pengeluaran'][$i], 0, ',', '.')); ?></td>
                <td><?php echo e(number_format($trend['laba'][$i], 0, ',', '.')); ?></td>
                <td><?php echo e($trend['okupansi'][$i]); ?>%</td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($kategori_pengeluaran) > 0): ?>
        <h2>Pengeluaran per Kategori</h2>
        <table>
            <tr><th>Kategori</th><th>Total (Rp)</th></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategori_pengeluaran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item['label']); ?></td>
                    <td><?php echo e(number_format($item['value'], 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($tier ?? 'pro') === 'business'): ?>
        <h2>Rincian Tiap Kos (Khusus BUSINESS)</h2>
        <table>
            <tr><th>Nama Kos</th><th>Total Kamar</th><th>Terisi</th><th>Okupansi</th><th>Pendapatan (Rp)</th><th>Pengeluaran (Rp)</th><th>Untung Bersih (Rp)</th></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rincian_tiap_kos ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($p['nama']); ?></td>
                    <td><?php echo e($p['total_kamar']); ?></td>
                    <td><?php echo e($p['kamar_terisi']); ?></td>
                    <td><?php echo e($p['tingkat_terisi']); ?>%</td>
                    <td><?php echo e(number_format($p['pendapatan'], 0, ',', '.')); ?></td>
                    <td><?php echo e(number_format($p['pengeluaran'], 0, ',', '.')); ?></td>
                    <td><?php echo e(number_format($p['untung_bersih'], 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7">Belum ada data.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>

        <h2>Daftar Transaksi Detail (Khusus BUSINESS, maks 100)</h2>
        <table>
            <tr><th>Tanggal</th><th>Penyewa</th><th>Kos</th><th>Kamar</th><th>Periode</th><th>Metode</th><th>Jumlah (Rp)</th></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = array_slice($transaksi_detail ?? [], 0, 100); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="nowrap"><?php echo e($t['tanggal']); ?></td>
                    <td><?php echo e($t['penyewa']); ?></td>
                    <td><?php echo e($t['kos']); ?></td>
                    <td><?php echo e($t['kamar']); ?></td>
                    <td><?php echo e($t['periode']); ?></td>
                    <td><?php echo e($t['metode']); ?></td>
                    <td><?php echo e(number_format($t['jumlah'], 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7">Belum ada data.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($transaksi_detail ?? []) > 100): ?>
            <p style="font-size: 10px; color: #9ca3af;">… dan <?php echo e(count($transaksi_detail) - 100); ?> transaksi lainnya — lihat file Excel untuk daftar lengkap.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <h2>Pertumbuhan Bulan ke Bulan (Khusus BUSINESS)</h2>
        <table>
            <tr><th>Bulan</th><th>Pendapatan (Rp)</th><th>± %</th><th>Pengeluaran (Rp)</th><th>± %</th><th>Untung Bersih (Rp)</th><th>± %</th></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pertumbuhan ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($p['bulan']); ?></td>
                    <td><?php echo e(number_format($p['pendapatan'], 0, ',', '.')); ?></td>
                    <td><?php echo e($p['pendapatan_pct']); ?>%</td>
                    <td><?php echo e(number_format($p['pengeluaran'], 0, ',', '.')); ?></td>
                    <td><?php echo e($p['pengeluaran_pct']); ?>%</td>
                    <td><?php echo e(number_format($p['laba'], 0, ',', '.')); ?></td>
                    <td><?php echo e($p['laba_pct']); ?>%</td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7">Belum ada data.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>

        <h2>Metode Pembayaran (Khusus BUSINESS)</h2>
        <table>
            <tr><th>Metode</th><th>Jumlah Transaksi</th><th>Total (Rp)</th></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $metode_pembayaran ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($m['label']); ?></td>
                    <td><?php echo e($m['jumlah_transaksi']); ?></td>
                    <td><?php echo e(number_format($m['total'], 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="3">Belum ada data.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>

        <h2>Top 10 Penyewa (Khusus BUSINESS)</h2>
        <table>
            <tr><th>#</th><th>Penyewa</th><th>Transaksi</th><th>Total Bayar (Rp)</th></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $top_penyewa ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($p['nama']); ?></td>
                    <td><?php echo e($p['jumlah_transaksi']); ?></td>
                    <td><?php echo e(number_format($p['total'], 0, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4">Belum ada data.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </table>
    <?php else: ?>
        <p style="font-size: 10px; color: #9ca3af; text-align: center;">Rincian tiap kos, daftar transaksi detail, pertumbuhan bulanan, metode pembayaran &amp; top penyewa hanya tersedia di paket BUSINESS.</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="footer">Paket <?php echo e(strtoupper($tier ?? 'pro')); ?> &bull; Dibuat pada <?php echo e(now()->translatedFormat('d F Y H:i')); ?> &bull; Ngekos.in</div>
</body>
</html><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\exports\laporan-premium.blade.php ENDPATH**/ ?>