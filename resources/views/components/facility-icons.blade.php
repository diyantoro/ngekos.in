@props(['fasilitas' => [], 'editable' => false, 'selected' => []])

@php
    // Ikon SVG fallback untuk fasilitas tanpa file PNG.
    $ikonSvg = [
        'WiFi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />',
        'AC' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />',
        'Kamar Mandi Dalam' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'Kamar Mandi Luar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'Kasur' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />',
        'Lemari' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />',
        'Meja & Kursi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6V7.5z" />',
        'Jendela' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />',
        'Dapur' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.38a48.474 48.474 0 00-6-.37c-2.032 0-4.034.126-6 .37m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.17c0 .62-.504 1.124-1.125 1.124H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z" />',
        'Aman 24 Jam' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />',
        'Cuci Setrika' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />',
        'Parkir' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H18.75m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />',
        'Laundry' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />',
        'Ruang Tamu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819" />',
        'Toilet' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.38a48.474 48.474 0 00-6-.37c-2.032 0-4.034.126-6 .37m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.17c0 .62-.504 1.124-1.125 1.124H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z" />',
        'Gym' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />',
        'Kolam Renang' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />',
    ];

    // Daftar fasilitas yang punya file PNG. Key = label fasilitas (nama yang disimpan),
    // value = nama file PNG.
    $ikonPng = [
        'AC' => 'ac.png',
        'Kulkas' => 'kulkas.png',
        'Kasur' => 'kasur.png',
        'Lemari' => 'lemari.png',
        'Meja' => 'meja.png',
        'Meja & Kursi' => 'meja.png',
        'Kursi' => 'kursi.png',
        'Jendela' => 'jendela.png',
        'Bantal' => 'bantal.png',
        'Cermin' => 'cermin.png',
        'Rak Baju' => 'lemari.png',
        'Kamar Mandi Dalam' => 'kamar_mandi_dalam.png',
        'Kamar Mandi Luar' => 'kamar_mandi_luar.png',
        'Shower' => 'shower.png',
        'Ember' => 'ember.png',
        'WiFi' => 'wifi.png',
        'Dapur' => 'dapur.png',
        'Dapur Bersama' => 'dapur.png',
        'Ruang Tamu' => 'ruang_tamu.png',
        'Penjaga Kos' => 'penjaga_kos.png',
        'Dilarang Merokok' => 'dilarang_merokok.png',
        'Dilarang Bawa Tamu lawan jenis' => 'lawan_jenis.png',
        'Akses' => 'akses.png',
        'Parkir Motor' => 'parkir_motor_sepeda.png',
        'Parkir Motor & Sepeda' => 'parkir_motor_sepeda.png',
        'Parkir Mobil' => 'parkir_mobil.png',
        'Parkir' => 'parkir_mobil.png',
        'Tempat Jemuran' => 'tempat_jemuran.png',
        'Ventilasi' => 'jendela.png',
        'Tamu' => 'tamu.png',
        'Lawan Jenis' => 'lawan_jenis.png',
        'Tidak Termasuk Listrik' => 'tidak_listrik.png',
        'Tidak Listrik' => 'tidak_listrik.png',
    ];

    // Kelompok fasilitas (alamat = label dibatasi di form). Key = judul grup.
    $kelompokFasilitas = [
        'Fasilitas Kamar' => [
            'AC', 'Kulkas', 'Kasur', 'Lemari', 'Meja & Kursi',
            'Jendela', 'Bantal', 'Cermin',
        ],
        'Fasilitas Kamar Mandi' => [
            'Kamar Mandi Dalam', 'Kamar Mandi Luar', 'Shower', 'Ember',
        ],
        'Fasilitas Umum' => [
            'WiFi', 'Dapur Bersama', 'Ruang Tamu', 'Penjaga Kos',
            'Dilarang Merokok', 'Laundry',
        ],
        'Fasilitas Parkir' => [
            'Parkir Motor & Sepeda', 'Parkir Mobil',
        ],
        'Peraturan Khusus' => [
            'Dilarang Bawa Tamu lawan jenis', 'Akses',
        ],
    ];

    // Fungsi menentukan apakah item punya PNG. Untuk mempermudah, buat closure.
    $assetFasilitas = function ($nama) use ($ikonPng) {
        return $ikonPng[$nama] ?? null;
    };
@endphp

@if ($editable)
    <div class="space-y-7" x-data="{ selected: @entangle('fasilitasTerpilih').live }">
        @foreach ($kelompokFasilitas as $judul => $items)
            <div>
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                    {{ $judul }}
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach ($items as $nama)
                        @php
                            $png = $assetFasilitas($nama);
                            $svg = $ikonSvg[$nama] ?? null;
                            $checked = in_array($nama, $selected);
                        @endphp
                        <label class="relative cursor-pointer select-none">
                            <input type="checkbox" value="{{ $nama }}" x-model="selected" {!! $checked ? 'checked' : '' !!}
                                class="peer sr-only">
                            <span :class="selected.includes('{{ $nama }}') ? 'border-teal-500 bg-teal-50 ring-teal-200 dark:border-teal-500 dark:bg-teal-500/10 dark:ring-teal-500/30' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600 dark:hover:bg-gray-700'"
                                class="flex items-center gap-2.5 rounded-xl border-2 p-3 transition-all duration-200">
                                <span :class="selected.includes('{{ $nama }}') ? 'bg-teal-600' : 'bg-gray-100 dark:bg-gray-700'"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition-colors duration-200">
                                    @if ($png)
                                        <img src="{{ asset('images/fasilitas/' . $png) }}" alt="{{ $nama }}"
                                            class="h-6 w-6 object-contain">
                                    @elseif ($svg)
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" :class="selected.includes('{{ $nama }}') ? 'text-white' : 'text-gray-400 dark:text-gray-500'">{!! $svg !!}</svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" :class="selected.includes('{{ $nama }}') ? 'text-white' : 'text-gray-400 dark:text-gray-500'"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @endif
                                </span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $nama }}</span>
                                <span x-show="selected.includes('{{ $nama }}')" x-cloak
                                    class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-teal-600 text-white">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="flex flex-wrap gap-2">
        @foreach (array_filter(array_map('trim', explode(',', $fasilitas))) as $f)
            @if (isset($ikonPng[$f]))
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-teal-50 dark:bg-teal-500/10 px-3 py-2 text-xs font-medium text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-200 dark:ring-teal-500/30">
                    <img src="{{ asset('images/fasilitas/' . $ikonPng[$f]) }}" class="h-4 w-4 object-contain" alt="">
                    {{ $f }}
                </span>
            @elseif (isset($ikonSvg[$f]))
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-teal-50 dark:bg-teal-500/10 px-3 py-2 text-xs font-medium text-teal-700 dark:text-teal-300 ring-1 ring-inset ring-teal-200 dark:ring-teal-500/30">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">{!! $ikonSvg[$f] !!}</svg>
                    {{ $f }}
                </span>
            @else
                <span class="inline-flex items-center gap-1 rounded-lg bg-gray-50 dark:bg-gray-700/50 px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-200 dark:ring-gray-700">
                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ $f }}
                </span>
            @endif
        @endforeach
    </div>
@endif