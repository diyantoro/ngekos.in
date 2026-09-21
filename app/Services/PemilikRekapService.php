<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use Carbon\Carbon;
use InvalidArgumentException;

class PemilikRekapService
{
    public static function data(int $userId, ?string $bulan = null, string $tier = 'basic'): array
    {
        try {
            $periodeMulai = $bulan
                ? Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Format bulan tidak valid (contoh: 2026-03).');
        }

        $periodeAkhir = (clone $periodeMulai)->endOfMonth();

        $propertis = Properti::withCount([
            'kamars as total_kamar',
            'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
        ])->where('pemilik_id', $userId)
            ->orderBy('nama')
            ->get();

        $sewaans = Penyewaan::whereHas('properti', fn ($q) => $q->where('pemilik_id', $userId))
            ->with(['anakKos:id,nama', 'kamar:id,nama', 'kamar.properti:id,nama'])
            ->where('tanggal_masuk', '<=', $periodeAkhir->toDateString())
            ->where(function ($q) use ($periodeMulai) {
                $q->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $periodeMulai->toDateString());
            })
            ->orderBy('tanggal_masuk', 'desc')
            ->get();

        $transaksi = Pembayaran::where('status', 'diverifikasi')
            ->whereBetween('verified_at', [$periodeMulai, $periodeAkhir])
            ->whereHas('tagihan.penyewaan.properti', fn ($q) => $q->where('pemilik_id', $userId))
            ->with(['anakKos:id,nama', 'tagihan:id,periode'])
            ->orderBy('verified_at', 'desc')
            ->get();

        if ($tier === 'basic') {
            $sewaans = $sewaans->take(20);
            $transaksi = $transaksi->take(20);
        }

        $pendapatan = (int) $transaksi->sum(fn ($t) => (float) $t->jumlah);

        $pengeluaran = (int) Pengeluaran::whereHas('properti', fn ($q) => $q->where('pemilik_id', $userId))
            ->whereBetween('tanggal', [$periodeMulai->toDateString(), $periodeAkhir->toDateString()])
            ->sum('jumlah');

        $kategoriPengeluaran = Pengeluaran::whereHas('properti', fn ($q) => $q->where('pemilik_id', $userId))
            ->whereBetween('tanggal', [$periodeMulai->toDateString(), $periodeAkhir->toDateString()])
            ->get(['kategori', 'jumlah'])
            ->groupBy('kategori')
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')))
            ->sortDesc()
            ->take(7)
            ->map(fn ($nilai, $kategori) => ['label' => ucwords(str_replace('_', ' ', (string) $kategori)), 'value' => $nilai])
            ->values()
            ->all();

        return [
            'periode' => $periodeMulai->translatedFormat('F Y'),
            'bulan' => $periodeMulai->format('Y-m'),
            'ringkasan' => [
                'total_properti' => $propertis->count(),
                'total_kamar' => (int) $propertis->sum('total_kamar'),
                'kamar_terisi' => (int) $propertis->sum('kamar_terisi'),
                'penyewaan_aktif' => $sewaans->where('status', 'aktif')->count(),
                'pendapatan' => $pendapatan,
                'pengeluaran' => $pengeluaran,
                'laba_bersih' => $pendapatan - $pengeluaran,
                'jumlah_transaksi' => $transaksi->count(),
            ],
            'kategori_pengeluaran' => $kategoriPengeluaran,
            'propertis' => $propertis->map(fn (Properti $p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'alamat' => $p->alamat,
                'status' => $p->status,
                'total_kamar' => (int) $p->total_kamar,
                'kamar_terisi' => (int) $p->kamar_terisi,
            ])->values()->all(),
            'sewaans' => $sewaans->map(fn ($s) => [
                'anak_kos_nama' => $s->anakKos?->nama ?? '-',
                'kamar_nama' => $s->kamar?->nama,
                'properti_nama' => $s->kamar?->properti?->nama ?? $s->properti?->nama,
                'tanggal_masuk' => $s->tanggal_masuk,
                'tanggal_keluar' => $s->tanggal_keluar,
                'status' => $s->status,
            ])->values()->all(),
            'transaksi' => $transaksi->map(fn (Pembayaran $p) => [
                'anak_kos_nama' => $p->anakKos?->nama ?? '-',
                'periode' => $p->tagihan?->periode,
                'metode' => $p->metode,
                'jumlah' => (float) $p->jumlah,
                'status' => $p->status,
                'verified_at' => $p->verified_at?->toDateTimeString(),
            ])->values()->all(),
        ];
    }
}