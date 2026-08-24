@props(['status'])

@php
    $colors = [
        'tersedia' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'terisi' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'aktif' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'nonaktif' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'selesai' => 'bg-gray-100 text-gray-600 ring-gray-200',
        'menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'menunggu_verifikasi' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'check_in' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'diverifikasi' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'batal' => 'bg-gray-100 text-gray-600 ring-gray-200',
        'belum_bayar' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'lunas' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'terlambat' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];
    $label = str($status)->replace('_', ' ')->title();
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $colors[$status] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
    {{ $label }}
</span>