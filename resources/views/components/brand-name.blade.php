@props(['suffixClass' => 'text-brand-700 dark:text-brand-300'])

@php
    $nama = \App\Models\Pengaturan::namaSitus();
    $adaTitik = str_contains($nama, '.');
    $utama = $adaTitik ? str($nama)->beforeLast('.') : $nama;
    $akhiran = $adaTitik ? '.' . str($nama)->afterLast('.') : '';
@endphp
<span {{ $attributes }}>{{ $utama }}<span class="{{ $suffixClass }}">{{ $akhiran }}</span></span>