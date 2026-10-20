@props(['kamar', 'properti' => null, 'judul' => 'Spesifikasi & Fasilitas'])

@php
    use App\Services\PenyewaanService;
    use App\Support\AturanKos;
    use App\Support\FacilityHelper;

    $fasilitas = $kamar->daftarFasilitas();
    $kelompok = FacilityHelper::kelompokkan($fasilitas);

    $statusListrik = $kamar->termasuk_listrik !== null
        ? ($kamar->termasuk_listrik ? 'Termasuk Listrik' : 'Tidak Termasuk Listrik')
        : $kelompok['listrik'];

    $peraturan = collect()
        ->merge(AturanKos::fromString($kamar->peraturan ?? ''))
        ->merge($kelompok['peraturan'])
        ->merge(AturanKos::fromString($properti?->aturan ?? ''))
        ->map(fn ($baris) => trim((string) $baris))
        ->filter()
        ->unique(fn ($baris) => mb_strtolower($baris))
        ->values()
        ->all();

    $deposit = (float) ($kamar->deposit ?? 0);
    $hargaBulanan = (float) ($kamar->harga_sewa_bulanan ?? 0);
    $dp = PenyewaanService::dpUntuk($hargaBulanan);

    $adaSpesifikasi = filled($kamar->ukuran) || $statusListrik || $kamar->kapasitas || $hargaBulanan > 0;
@endphp

<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <div class="flex items-center gap-3 border-b border-stone-100 px-4 py-3 dark:border-gray-700">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
        </span>
        <div class="min-w-0">
            <p class="text-sm font-bold text-slate-900 dark:text-gray-100">{{ $judul }}</p>
            <p class="text-[11px] text-slate-500 dark:text-gray-400">Ketuk tiap bagian untuk melihat rinciannya.</p>
        </div>
    </div>

    <div class="divide-y divide-stone-100 dark:divide-gray-700">
        {{-- Spesifikasi tipe kamar --}}
        @if ($adaSpesifikasi)
            <details class="group" open>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 transition hover:bg-stone-50 dark:hover:bg-gray-700/40 [&::-webkit-details-marker]:hidden">
                    <span class="flex items-center gap-2.5 text-sm font-bold text-slate-800 dark:text-gray-100">
                        <svg class="h-4 w-4 shrink-0 text-brand-600 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v16.5h16.5M7.5 16.5V9m0 7.5h9V12m-9-3h6v3" /></svg>
                        Spesifikasi tipe kamar
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 [details[open]_&]:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </summary>
                <div class="space-y-2.5 px-4 pb-4 pt-1">
                    @if (filled($kamar->ukuran))
                        <div class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-gray-200">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v16.5h16.5M7.5 16.5V9m0 7.5h9V12m-9-3h6v3" /></svg>
                            <span><span class="text-slate-500 dark:text-gray-400">Ukuran:</span> <span class="font-semibold">{{ $kamar->ukuran }}</span></span>
                        </div>
                    @endif
                    <div class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-gray-200">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        <span><span class="text-slate-500 dark:text-gray-400">Kapasitas:</span> <span class="font-semibold">{{ $kamar->kapasitas }} orang</span></span>
                    </div>
                    @if ($statusListrik)
                        <div class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-gray-200">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                            <span><span class="text-slate-500 dark:text-gray-400">Listrik:</span> <span class="font-semibold">{{ $statusListrik }}</span></span>
                        </div>
                    @endif
                    <div class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-gray-200">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                        <div class="min-w-0">
                            <span class="text-slate-500 dark:text-gray-400">Harga sewa:</span>
                            <div class="mt-1">
                                <x-harga-tiga-periode :bulanan="$kamar->harga_sewa_bulanan" :mingguan="$kamar->harga_sewa_mingguan" :harian="$kamar->harga_sewa_harian" :asli="($kamar->harga_asli && $kamar->harga_asli > $kamar->harga_sewa_bulanan) ? $kamar->harga_asli : null" varian="rincian" />
                            </div>
                        </div>
                    </div>
                </div>
            </details>
        @endif

        {{-- Fasilitas kamar --}}
        @if ($kelompok['kamar'] !== [])
            <details class="group" open>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 transition hover:bg-stone-50 dark:hover:bg-gray-700/40 [&::-webkit-details-marker]:hidden">
                    <span class="flex items-center gap-2.5 text-sm font-bold text-slate-800 dark:text-gray-100">
                        <svg class="h-4 w-4 shrink-0 text-brand-600 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        Fasilitas kamar
                        <span class="rounded-full bg-stone-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-gray-700 dark:text-gray-300">{{ count($kelompok['kamar']) }}</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 [details[open]_&]:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </summary>
                <ul class="space-y-2 px-4 pb-4 pt-1" role="list">
                    @foreach ($kelompok['kamar'] as $item)
                        <li class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-gray-200">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif

        {{-- Fasilitas kamar mandi --}}
        @if ($kelompok['kamar_mandi'] !== [])
            <details class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 transition hover:bg-stone-50 dark:hover:bg-gray-700/40 [&::-webkit-details-marker]:hidden">
                    <span class="flex items-center gap-2.5 text-sm font-bold text-slate-800 dark:text-gray-100">
                        <svg class="h-4 w-4 shrink-0 text-brand-600 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Fasilitas kamar mandi
                        <span class="rounded-full bg-stone-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-gray-700 dark:text-gray-300">{{ count($kelompok['kamar_mandi']) }}</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 [details[open]_&]:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </summary>
                <ul class="space-y-2 px-4 pb-4 pt-1" role="list">
                    @foreach ($kelompok['kamar_mandi'] as $item)
                        <li class="flex items-start gap-2.5 text-sm text-slate-700 dark:text-gray-200">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif

        {{-- Peraturan khusus --}}
        <details class="group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 transition hover:bg-stone-50 dark:hover:bg-gray-700/40 [&::-webkit-details-marker]:hidden">
                <span class="flex items-center gap-2.5 text-sm font-bold text-slate-800 dark:text-gray-100">
                    <svg class="h-4 w-4 shrink-0 text-brand-600 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                    Peraturan khusus tipe kamar ini
                    @if ($peraturan !== [])
                        <span class="rounded-full bg-stone-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-gray-700 dark:text-gray-300">{{ count($peraturan) }}</span>
                    @endif
                </span>
                <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 [details[open]_&]:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
            </summary>
            <div class="px-4 pb-4 pt-1">
                @if ($peraturan !== [])
                    <ul class="space-y-2" role="list">
                        @foreach ($peraturan as $aturan)
                            <li class="flex items-start gap-2.5 text-sm leading-relaxed text-slate-700 dark:text-gray-200">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>{{ $aturan }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500 dark:text-gray-400">Tidak ada peraturan khusus untuk tipe kamar ini.</p>
                @endif
            </div>
        </details>

        {{-- Deposit --}}
        <details class="group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 transition hover:bg-stone-50 dark:hover:bg-gray-700/40 [&::-webkit-details-marker]:hidden">
                <span class="flex items-center gap-2.5 text-sm font-bold text-slate-800 dark:text-gray-100">
                    <svg class="h-4 w-4 shrink-0 text-brand-600 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" /></svg>
                    Deposit
                </span>
                <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 [details[open]_&]:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
            </summary>
            <div class="px-4 pb-4 pt-1">
                @if ($deposit > 0)
                    <p class="text-sm font-bold text-slate-900 dark:text-gray-100">Rp{{ number_format($deposit, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-gray-400">Dikembalikan di akhir periode sewa jika tidak ditemukan kerusakan pada kamar.</p>
                @elseif ($dp > 0)
                    <p class="text-sm font-bold text-slate-900 dark:text-gray-100">Uang Muka (DP) Rp{{ number_format($dp, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-gray-400">DP dibayar saat booking dan dipotong dari tagihan bulan pertama.</p>
                @else
                    <p class="text-sm text-slate-500 dark:text-gray-400">Tidak ada deposit untuk tipe kamar ini.</p>
                @endif
            </div>
        </details>
    </div>
</div>
