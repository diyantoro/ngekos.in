<?php

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <a href="<?php echo e(route('dashboard.pemilik')); ?>" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 inline-flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Kembali ke Dashboard
                </a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Grafik & Analitik</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Keuangan, kamar terisi, tagihan, dan performa tiap kos.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="propertiId"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua Properti</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daftarProperti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>"><?php echo e($p->nama); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <select wire:model.live="periode"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="3">3 bulan</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($maxPeriode >= 6): ?><option value="6">6 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($maxPeriode >= 12): ?><option value="12">12 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($maxPeriode >= 24): ?><option value="24">24 bulan</option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFree && $sisaTrial !== null): ?>
            <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                Masa coba gratis tinggal <strong><?php echo e($sisaTrial); ?> hari</strong>. Upgrade ke PRO untuk limit lebih besar & laporan premium.
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="font-bold hover:underline">Upgrade</a>
            </div>
        <?php elseif($isFree && $sisaTrial === null && $trialHabis): ?>
            <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                Masa coba 7 hari sudah habis atau belum diklaim. Data tidak hilang, tapi halaman Laporan dikunci. Tambah kos/kamar tetap bisa sampai batas paket Free.
                <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="font-bold hover:underline">Upgrade ke PRO</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($terkunci ?? false): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($bisaKlaim ?? false)): ?>
                <div class="max-w-2xl mx-auto rounded-2xl bg-teal-50 dark:bg-teal-500/10 ring-1 ring-teal-200 dark:ring-teal-500/30 p-6 text-center">
                    <p class="text-base font-extrabold text-teal-800 dark:text-teal-200">Gratis 7 hari fitur PRO, sekali per akun</p>
                    <p class="mt-1 text-sm text-teal-700 dark:text-teal-200/80">Tanpa kartu kredit. Buka grafik 12 bulan, laporan premium, dan ekspor tanpa watermark.</p>
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                        <button wire:click="klaimTrial" wire:loading.attr="disabled" class="inline-flex items-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-500 disabled:opacity-50">
                            Klaim Trial 7 Hari
                        </button>
                        <a href="<?php echo e(route('langganan.plans')); ?>" wire:navigate class="inline-flex items-center rounded-xl ring-1 ring-teal-300 dark:ring-teal-500/40 px-5 py-2.5 text-sm font-bold text-teal-700 dark:text-teal-200 hover:bg-teal-100/60 dark:hover:bg-teal-500/10">
                            Upgrade PRO
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="max-w-2xl mx-auto">
                    <?php if (isset($component)) { $__componentOriginalc325dfb0584ae0cb231e66721d030938 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc325dfb0584ae0cb231e66721d030938 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.premium-lock','data' => ['requiredPlan' => 'pro','title' => 'Grafik & Analitik','message' => 'Masa coba 7 hari sudah habis atau belum diklaim. Data tidak hilang, tapi halaman Laporan dikunci.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('premium-lock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['requiredPlan' => 'pro','title' => 'Grafik & Analitik','message' => 'Masa coba 7 hari sudah habis atau belum diklaim. Data tidak hilang, tapi halaman Laporan dikunci.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc325dfb0584ae0cb231e66721d030938)): ?>
<?php $attributes = $__attributesOriginalc325dfb0584ae0cb231e66721d030938; ?>
<?php unset($__attributesOriginalc325dfb0584ae0cb231e66721d030938); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc325dfb0584ae0cb231e66721d030938)): ?>
<?php $component = $__componentOriginalc325dfb0584ae0cb231e66721d030938; ?>
<?php unset($__componentOriginalc325dfb0584ae0cb231e66721d030938); ?>
<?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php else: ?>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pendapatan Periode','value' => 'Rp' . number_format($totalPendapatan, 0, ',', '.'),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pendapatan Periode','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($totalPendapatan, 0, ',', '.')),'tone' => 'emerald','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Pengeluaran Periode','value' => 'Rp' . number_format($totalPengeluaran, 0, ',', '.'),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pengeluaran Periode','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Rp' . number_format($totalPengeluaran, 0, ',', '.')),'tone' => 'rose','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Transaksi Terverifikasi','value' => $totalTransaksi,'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Transaksi Terverifikasi','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalTransaksi),'tone' => 'cyan','icon' => '<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Ekspor Rekap Bulanan</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Unduh rekap ringkasan, properti, penyewaan & transaksi per bulan (PDF / Excel).</p>
            </div>
            <form method="GET" class="flex flex-wrap items-center gap-2" target="_blank" rel="noopener">
                <input type="month" name="bulan" value="<?php echo e(now()->format('Y-m')); ?>"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                <button type="submit" formaction="<?php echo e(route('pemilik.rekap.pdf')); ?>"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    PDF
                </button>
                <button type="submit" formaction="<?php echo e(route('pemilik.rekap.excel')); ?>"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Excel
                </button>
            </form>
        </div>

        <div id="grafik-data"
            data-labels='<?php echo json_encode($bulanLabels, 15, 512) ?>'
            data-pendapatan='<?php echo json_encode($chartPendapatan, 15, 512) ?>'
            data-pengeluaran='<?php echo json_encode($chartPengeluaran, 15, 512) ?>'
            data-laba='<?php echo json_encode($chartLaba, 15, 512) ?>'
            data-okupansi='<?php echo json_encode($chartOkupansi, 15, 512) ?>'
            data-transaksi='<?php echo json_encode($chartTransaksi, 15, 512) ?>'
            data-lunas='<?php echo json_encode($chartLunas, 15, 512) ?>'
            data-belum='<?php echo json_encode($chartBelum, 15, 512) ?>'
            data-kategori='<?php echo json_encode($chartKategori, 15, 512) ?>'
            class="space-y-6">
            <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Uang Masuk vs Uang Keluar</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <?php echo e($propertiId ? ($daftarProperti->firstWhere('id', $propertiId)?->nama ?? '') : 'Semua properti'); ?> · <?php echo e(count($bulanLabels)); ?> bulan terakhir
                </p>
                <div class="relative mt-4 w-full" style="height: 17rem;">
                        <canvas id="grafik-keuangan" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tren Transaksi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah pembayaran terverifikasi per bulan</p>
                    <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-transaksi" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                </div>
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tagihan Lunas vs Belum</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nilai tagihan terbit per bulan (Rp)</p>
                                        <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-lunas" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Tren Kamar Terisi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kamar terisi (%) per bulan</p>
                                        <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-okupansi" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                </div>
                <div class="min-w-0 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden p-4 sm:p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Pengeluaran per Kategori</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Komposisi biaya operasional periode ini</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($chartKategori) > 0): ?>
                                            <div class="relative mt-4 w-full" style="height: 15rem;">
                        <canvas id="grafik-kategori" class="absolute inset-0 h-full w-full"></canvas>
                    </div>
                    <?php else: ?>
                        <p class="py-12 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pengeluaran pada periode ini.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tagihan Belum Bayar</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nilai tagihan yang belum dibayar</p>
                </div>
                <div class="p-5 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-700/40 p-4">
                        <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Belum waktunya bayar</p>
                        <p class="mt-1 text-base font-extrabold text-gray-900 dark:text-gray-100">Rp<?php echo e(number_format($aging['belum_jatuh_tempo'], 0, ',', '.')); ?></p>
                    </div>
                    <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 p-4">
                        <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 uppercase">Baru telat, di bawah seminggu</p>
                        <p class="mt-1 text-base font-extrabold text-amber-700 dark:text-amber-300">Rp<?php echo e(number_format($aging['telat_1_7'], 0, ',', '.')); ?></p>
                    </div>
                    <div class="rounded-xl bg-orange-50 dark:bg-orange-500/10 p-4">
                        <p class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 uppercase">Telat sampai sebulan</p>
                        <p class="mt-1 text-base font-extrabold text-orange-700 dark:text-orange-300">Rp<?php echo e(number_format($aging['telat_8_30'], 0, ',', '.')); ?></p>
                    </div>
                    <div class="rounded-xl bg-rose-50 dark:bg-rose-500/10 p-4">
                        <p class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 uppercase">Telat lebih dari sebulan, segera tagih</p>
                        <p class="mt-1 text-base font-extrabold text-rose-700 dark:text-rose-300">Rp<?php echo e(number_format($aging['telat_lebih_30'], 0, ',', '.')); ?></p>
                    </div>
                </div>
                <div class="px-5 pb-5">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Siapa yang belum bayar (maks 50)</p>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-72 overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tagihanBelum; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="py-2.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($t->penyewaan->anakKos?->nama ?? '-'); ?> · <?php echo e($t->penyewaan->kamar?->nama); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($t->periode); ?> · tempo <?php echo e($t->jatuh_tempo?->translatedFormat('d M Y')); ?></p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-rose-600 dark:text-rose-400">Rp<?php echo e(number_format($t->jumlah + $t->denda, 0, ',', '.')); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="py-8 text-center text-sm text-gray-400">Semua tagihan lunas. Bagus!</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Kos Pemasukan Terbesar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Berdasarkan pembayaran terverifikasi</p>
                    </div>
                </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topProperti; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="px-5 py-3.5 flex items-center gap-3">
                                <span class="shrink-0 h-8 w-8 rounded-lg bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 text-sm font-extrabold flex items-center justify-center"><?php echo e($i + 1); ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"><?php echo e($p['nama']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($p['kamar_terisi']); ?>/<?php echo e($p['total_kamar']); ?> terisi</p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp<?php echo e(number_format($p['pendapatan'], 0, ',', '.')); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="px-5 py-10 text-center text-sm text-gray-400">Belum ada data.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH C:\laragon\www\Ngekos.in\resources\views\livewire\pages\pemilik\grafik.blade.php ENDPATH**/ ?>