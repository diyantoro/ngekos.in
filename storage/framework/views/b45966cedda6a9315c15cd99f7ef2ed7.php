<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Bulanan Pemilik Kos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 18px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; color: #0d9488; }
        .header p { margin: 0; color: #6b7280; font-size: 11px; }
        h2 { font-size: 13px; margin: 16px 0 8px; color: #111827; border-bottom: 2px solid #0d9488; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; font-size: 11px; }
        th { background: #0d9488; color: #ffffff; font-weight: bold; }
        tr:nth-child(even) td { background: #f9fafb; }
        .ringkasan td:first-child { font-weight: bold; width: 50%; }
        .footer { margin-top: 18px; font-size: 10px; color: #9ca3af; text-align: center; }
        .watermark { position: fixed; top: 42%; left: 0; right: 0; text-align: center; font-size: 44px; font-weight: bold; color: #d1d5db; opacity: 0.3; }
        .trial-banner { text-align: center; font-size: 12px; font-weight: bold; color: #b45309; background: #fef3c7; border: 1px solid #fcd34d; padding: 6px 8px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($watermark ?? false) || ($is_trial ?? false)): ?>
        <div class="watermark">TRIAL FREE</div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="header">
        <h1>Rekap Bulanan Pemilik Kos</h1>
        <p><?php echo e($periode); ?> (<?php echo e($bulan); ?>)</p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($watermark ?? false) || ($is_trial ?? false)): ?>
            <p style="font-weight: bold; color: #b45309;">TRIAL FREE - Laporan Dasar</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($watermark ?? false) || ($is_trial ?? false)): ?>
        <div class="trial-banner">TRIAL FREE - Laporan Dasar. Upgrade ke PRO untuk laporan lengkap tanpa watermark.</div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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

    <h2>Properti</h2>
    <table>
        <tr><th>Nama</th><th>Alamat</th><th>Total Kamar</th><th>Kamar Terisi</th><th>Status</th></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $propertis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($p['nama']); ?></td>
                <td><?php echo e($p['alamat']); ?></td>
                <td><?php echo e($p['total_kamar']); ?></td>
                <td><?php echo e($p['kamar_terisi']); ?></td>
                <td><?php echo e($p['status']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5">Belum ada properti.</td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <h2>Penyewaan Aktif pada Periode</h2>
    <table>
        <tr><th>Penyewa</th><th>Kamar</th><th>Kos</th><th>Masuk</th><th>Keluar</th><th>Status</th></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sewaans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($s['anak_kos_nama']); ?></td>
                <td><?php echo e($s['kamar_nama']); ?></td>
                <td><?php echo e($s['properti_nama']); ?></td>
                <td><?php echo e($s['tanggal_masuk']); ?></td>
                <td><?php echo e($s['tanggal_keluar'] ?? '-'); ?></td>
                <td><?php echo e($s['status']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6">Belum ada penyewaan pada periode ini.</td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <h2>Transaksi Terverifikasi</h2>
    <table>
        <tr><th>Penyewa</th><th>Periode</th><th>Metode</th><th>Jumlah (Rp)</th><th>Diverifikasi</th></tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $transaksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($t['anak_kos_nama']); ?></td>
                <td><?php echo e($t['periode']); ?></td>
                <td><?php echo e($t['metode']); ?></td>
                <td><?php echo e(number_format($t['jumlah'], 0, ',', '.')); ?></td>
                <td><?php echo e($t['verified_at']); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5">Belum ada transaksi pada periode ini.</td></tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <div class="footer">Dibuat pada <?php echo e(now()->translatedFormat('d F Y H:i')); ?> &bull; Ngekos.in</div>
</body>
</html><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\exports\rekap-pemilik.blade.php ENDPATH**/ ?>