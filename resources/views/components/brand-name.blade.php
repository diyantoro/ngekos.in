@props(['suffixClass' => 'text-teal-700 dark:text-teal-400'])

@php
    $nama = \App\Models\Pengaturan::namaSitus();
    $adaTitik = str_contains($nama, '.');
    $utama = $adaTitik ? str($nama)->beforeLast('.') : $nama;
    $akhiran = $adaTitik ? '.' . str($nama)->afterLast('.') : '';
@endphp
<span {{ $attributes }}>{{ $utama }}<span class="{{ $suffixClass }}">{{ $akhiran }}</span></span>