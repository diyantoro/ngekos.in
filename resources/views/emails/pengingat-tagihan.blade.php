<x-mail::message>
# {{ $subjek }}

Halo,

{{ $sapaan }}

<x-mail::panel>
Kos: {{ $namaKos }}{{ $namaKamar !== '-' ? ' · Kamar '.$namaKamar : '' }}
Periode: {{ $tagihan->periode }}
Jatuh tempo: {{ $tagihan->jatuh_tempo?->translatedFormat('d F Y') ?? '-' }}
Sewa: Rp{{ number_format($ringkasan['sewa'], 0, ',', '.') }}
Denda berjalan ({{ $ringkasan['hari_telat'] }} hari × Rp{{ number_format($ringkasan['denda_per_hari'], 0, ',', '.') }}/hari): Rp{{ number_format($ringkasan['denda'], 0, ',', '.') }}
**Total yang harus dibayar: Rp{{ number_format($ringkasan['total'], 0, ',', '.') }}**
@if ($isPatungan && $porsiSaya !== null)
Porsi kamu (patungan): **Rp{{ number_format($porsiSaya, 0, ',', '.') }}**
@endif
</x-mail::panel>

Bayarkan tepat sebesar total di atas (tidak boleh kurang). Denda bertambah setiap hari keterlambatan sesuai aturan kos.

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
