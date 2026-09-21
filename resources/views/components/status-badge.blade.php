@props(['status'])

@php
    $colors = [
        'tersedia' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'terisi' => 'bg-amber-50 text-amber-700 ring-amber-200/80 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800',
        'perbaikan' => 'bg-sky-50 text-sky-700 ring-sky-200/80 dark:bg-sky-900/40 dark:text-sky-300 dark:ring-sky-800',
        'aktif' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'nonaktif' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'selesai' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200/80 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800',
        'menunggu_verifikasi' => 'bg-amber-50 text-amber-700 ring-amber-200/80 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-800',
        'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'check_in' => 'bg-sky-50 text-sky-700 ring-sky-200/80 dark:bg-sky-900/40 dark:text-sky-300 dark:ring-sky-800',
        'diverifikasi' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'batal' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'belum_bayar' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'lunas' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80 dark:bg-emerald-900/40 dark:text-emerald-300 dark:ring-emerald-800',
        'terlambat' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'patungan' => 'bg-sky-50 text-sky-700 ring-sky-200/80 dark:bg-sky-900/40 dark:text-sky-300 dark:ring-sky-800',
        'tunggal' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'keluar' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
        'expired' => 'bg-rose-50 text-rose-700 ring-rose-200/80 dark:bg-rose-900/40 dark:text-rose-300 dark:ring-rose-800',
        'cancelled' => 'bg-gray-100 text-gray-600 ring-gray-200/80 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600',
    ];
    $label = str($status)->replace('_', ' ')->title();
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset transition-colors duration-200 {{ $colors[$status] ?? 'bg-gray-100 text-gray-600 ring-gray-200/80' }}">
    @if (in_array($status, ['tersedia', 'aktif', 'lunas', 'diverifikasi', 'disetujui']))
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
    @elseif (in_array($status, ['terlambat', 'ditolak', 'nonaktif', 'belum_bayar']))
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
    @endif
    {{ $label }}
</span>
