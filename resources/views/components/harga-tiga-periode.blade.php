@props(['bulanan' => null, 'mingguan' => null, 'harian' => null, 'asli' => null, 'varian' => 'kartu'])

@php
    $opsi = [
        'bulanan' => ['nilai' => $bulanan, 'satuan' => 'bln'],
        'mingguan' => ['nilai' => $mingguan, 'satuan' => 'mgg'],
        'harian' => ['nilai' => $harian, 'satuan' => 'hari'],
    ];

    $tersedia = collect($opsi)->filter(fn ($o) => $o['nilai'] !== null && (float) $o['nilai'] > 0);

    $utamaArray = $tersedia->sortBy('nilai')->first();
    $utama = $utamaArray ? $utamaArray['nilai'] : null;
    $satuanUtama = $tersedia->sortBy('nilai')->keys()->first();
    $satuanLabel = $utama ? ($opsi[$satuanUtama]['satuan'] ?? 'bln') : 'bln';

    $lainnya = $tersedia->except([$satuanUtama])->map(fn ($o, $k) => 'Rp'.number_format($o['nilai'], 0, ',', '.').'/'.$o['satuan'])->values()->all();
@endphp

@if ($varian === 'baris')
    @if ($utama)
        <span class="text-sm sm:text-base font-extrabold text-teal-600 dark:text-teal-400">
            @if ($asli && $asli > $utama)
                <span class="block text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp{{ number_format($asli, 0, ',', '.') }}</span>
            @endif
            Rp{{ number_format($utama, 0, ',', '.') }}<span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/{{ $satuanLabel }}</span>
        </span>
        @if ($lainnya !== [])
            <span class="block text-[10px] font-medium text-gray-400 dark:text-gray-500">juga: {{ implode(' · ', $lainnya) }}</span>
        @endif
    @else
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Harga belum diisi</span>
    @endif
@elseif ($varian === 'rincian')
    @if ($utama)
        @if ($asli && $asli > $utama)
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp{{ number_format($asli, 0, ',', '.') }}</p>
        @endif
        <p class="text-base sm:text-lg font-extrabold text-teal-600 dark:text-teal-400">
            Rp{{ number_format($utama, 0, ',', '.') }}
            <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/{{ $satuanLabel }}</span>
        </p>
        @if ($lainnya !== [])
            <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500">juga: {{ implode(' · ', $lainnya) }}</p>
        @endif
    @else
        <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Harga belum diisi</p>
    @endif
@else
    @if ($utama)
        @if ($asli && $asli > $utama)
            <span class="block text-xs font-semibold text-gray-400 dark:text-gray-500 line-through">Rp{{ number_format($asli, 0, ',', '.') }}</span>
        @endif
        <span class="text-sm sm:text-base font-extrabold text-teal-600 dark:text-teal-400">
            Rp{{ number_format($utama, 0, ',', '.') }}<span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">/{{ $satuanLabel }}</span>
        </span>
        @if ($lainnya !== [])
            <span class="block text-[10px] text-gray-400 dark:text-gray-500">juga: {{ implode(' · ', $lainnya) }}</span>
        @endif
    @else
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Harga belum diisi</span>
    @endif
@endif
