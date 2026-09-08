@php
    $nama = \App\Models\Pengaturan::namaSitus();
    $adaTitik = str_contains($nama, '.');
    $utama = $adaTitik ? str($nama)->beforeLast('.') : $nama;
    $akhiran = $adaTitik ? '.' . str($nama)->afterLast('.') : '';
@endphp
<span {{ $attributes }}>{{ $utama }}<span class="text-teal-700 dark:text-teal-400">{{ $akhiran }}</span></span>
