<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Data untuk "Laporan Premium" pemilik kos (fitur PRO/BUSINESS).
 *
 * Semua aggregasi diskop ke properti milik pemilik + filter properti tertentu.
 * Method data() hanya boleh dipanggil setelah SubscriptionService::featureCheck
 * mengizinkan fitur advanced_report / export_report (enforcement server-side).
 */
class PemilikLaporanPremiumService
{
    public static function clampPeriode(User $user, int $minta): int
    {
        return SubscriptionService::clampPeriode($user, $minta);
    }

    public static function data(int $userId, ?string $bulan = null, int $bulanCount = 12, ?int $propertiId = null, string $tier = 'pro'): array
    {
        $bulanCount = $tier === 'business'
            ? max(1, min(24, $bulanCount))
            : max(1, min(12, $bulanCount));

        try {
            $periodeMulai = $bulan
                ? Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Format bulan tidak valid (contoh: 2026-03).');
        }

        $scopePropertiId = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId));
        $scopePengeluaran = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where($w->getModel()->getTable().'.id', $propertiId));

        $daftarProperti = Properti::where('pemilik_id', $userId)
            ->withCount([
                'kamars as total_kamar',
                'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
            ])
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'status']);

        $totalKamar = (int) $daftarProperti->when($propertiId, fn ($c) => $c->where('id', $propertiId))->sum('total_kamar');

        $rekapBulan = PemilikRekapService::data($userId, $periodeMulai->format('Y-m'));

        $mulaiTrend = now()->startOfMonth()->subMonths($bulanCount - 1);

        $pendapatanPerBulan = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scopePropertiId)
            ->where('verified_at', '>=', $mulaiTrend)
            ->get(['verified_at', 'jumlah'])
            ->groupBy(fn ($p) => $p->verified_at->format('m/Y'))
            ->map(fn ($rows) => (int) $rows->sum('jumlah'));

        $pengeluaranPerBulan = Pengeluaran::whereHas('properti', $scopePengeluaran)
            ->where('tanggal', '>=', $mulaiTrend->toDateString())
            ->get(['tanggal', 'jumlah'])
            ->groupBy(fn ($p) => $p->tanggal->format('m/Y'))
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')));

        $sewaansRange = Penyewaan::whereHas('properti', $scopePropertiId)
            ->where('tanggal_masuk', '<=', now()->endOfMonth()->toDateString())
            ->where(fn ($w) => $w->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $mulaiTrend->toDateString()))
            ->get(['kamar_id', 'tanggal_masuk', 'tanggal_keluar']);

        $labels = [];
        $pendapatan = [];
        $pengeluaran = [];
        $laba = [];
        $okupansi = [];

        foreach (range($bulanCount - 1, 0) as $i) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $key = $start->format('m/Y');

            $labels[] = $start->translatedFormat('M Y');

            $p = (int) ($pendapatanPerBulan[$key] ?? 0);
            $g = (int) ($pengeluaranPerBulan[$key] ?? 0);
            $pendapatan[] = $p;
            $pengeluaran[] = $g;
            $laba[] = $p - $g;

            $terisi = $sewaansRange
                ->filter(fn ($s) => $s->tanggal_masuk->lte($end) && ($s->tanggal_keluar === null || $s->tanggal_keluar->gte($start)))
                ->pluck('kamar_id')
                ->unique()
                ->count();

            $okupansi[] = $totalKamar > 0 ? (int) round($terisi / $totalKamar * 100) : 0;
        }

        $belum = Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan.properti', $scopePropertiId)
            ->with(['penyewaan.anakKos:id,nama', 'penyewaan.kamar:id,nama'])
            ->orderBy('jatuh_tempo')
            ->limit(50)
            ->get();

        $aging = ['belum_jatuh_tempo' => 0, 'telat_1_7' => 0, 'telat_8_30' => 0, 'telat_lebih_30' => 0];

        foreach ($belum as $t) {
            $nilai = (float) $t->jumlah + (float) $t->denda;
            $hari = $t->jatuh_tempo ? today()->diffInDays($t->jatuh_tempo, false) : 0;

            if ($hari >= 0) {
                $aging['belum_jatuh_tempo'] += $nilai;
            } elseif ($hari >= -7) {
                $aging['telat_1_7'] += $nilai;
            } elseif ($hari >= -30) {
                $aging['telat_8_30'] += $nilai;
            } else {
                $aging['telat_lebih_30'] += $nilai;
            }
        }

        $pendapatanProperti = Pembayaran::where('status', 'diverifikasi')
            ->where('verified_at', '>=', $mulaiTrend)
            ->whereHas('tagihan.penyewaan.properti', $scopePropertiId)
            ->with('tagihan.penyewaan')
            ->get()
            ->groupBy(fn ($p) => $p->tagihan?->penyewaan?->properti_id)
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')));

        $topProperti = $daftarProperti->map(fn ($p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'total_kamar' => (int) $p->total_kamar,
            'kamar_terisi' => (int) $p->kamar_terisi,
            'pendapatan' => (int) ($pendapatanProperti[$p->id] ?? 0),
        ])->sortByDesc('pendapatan')->values()->take(10)->all();

        $rincianTiapKos = [];
        $transaksiDetail = [];

        if ($tier === 'business') {
            $pengeluaranProperti = Pengeluaran::where('tanggal', '>=', $mulaiTrend->toDateString())
                ->whereHas('properti', $scopePengeluaran)
                ->get(['properti_id', 'jumlah'])
                ->groupBy('properti_id')
                ->map(fn ($rows) => (int) round($rows->sum('jumlah')));

            $rincianTiapKos = $daftarProperti->map(function ($p) use ($pendapatanProperti, $pengeluaranProperti) {
                $masuk = (int) ($pendapatanProperti[$p->id] ?? 0);
                $keluar = (int) ($pengeluaranProperti[$p->id] ?? 0);

                return [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'total_kamar' => (int) $p->total_kamar,
                    'kamar_terisi' => (int) $p->kamar_terisi,
                    'tingkat_terisi' => (int) $p->total_kamar > 0 ? (int) round($p->kamar_terisi / $p->total_kamar * 100) : 0,
                    'pendapatan' => $masuk,
                    'pengeluaran' => $keluar,
                    'untung_bersih' => $masuk - $keluar,
                ];
            })->sortByDesc('pendapatan')->values()->all();

            $transaksiDetail = Pembayaran::where('status', 'diverifikasi')
                ->where('verified_at', '>=', $mulaiTrend)
                ->whereHas('tagihan.penyewaan.properti', $scopePropertiId)
                ->with(['anakKos:id,nama', 'tagihan.penyewaan.properti:id,nama', 'tagihan.penyewaan.kamar:id,nama'])
                ->orderBy('verified_at', 'desc')
                ->limit(200)
                ->get()
                ->map(fn (Pembayaran $p) => [
                    'tanggal' => $p->verified_at?->toDateString(),
                    'penyewa' => $p->anakKos?->nama ?? '-',
                    'kos' => $p->tagihan?->penyewaan?->properti?->nama ?? '-',
                    'kamar' => $p->tagihan?->penyewaan?->kamar?->nama ?? '-',
                    'periode' => $p->tagihan?->periode ?? '-',
                    'metode' => $p->metode ?? '-',
                    'jumlah' => (float) $p->jumlah,
                ])->values()->all();
        }

        return [
            'periode' => $periodeMulai->translatedFormat('F Y'),
            'bulan' => $periodeMulai->format('Y-m'),
            'periode_trend' => $mulaiTrend->translatedFormat('M Y').' – '.now()->translatedFormat('M Y'),
            'bulan_count' => $bulanCount,
            'tier' => $tier,
            'properti_terpilih' => $propertiId,
            'daftar_properti' => $daftarProperti->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama])->values()->all(),
            'ringkasan' => $rekapBulan['ringkasan'],
            'kategori_pengeluaran' => $rekapBulan['kategori_pengeluaran'],
            'trend' => [
                'labels' => $labels,
                'pendapatan' => $pendapatan,
                'pengeluaran' => $pengeluaran,
                'laba' => $laba,
                'okupansi' => $okupansi,
            ],
            'aging' => array_map('intval', $aging),
            'tagihan_belum' => $belum->map(fn (Tagihan $t) => [
                'id' => $t->id,
                'anak_kos_nama' => $t->penyewaan?->anakKos?->nama ?? '-',
                'kamar_nama' => $t->penyewaan?->kamar?->nama,
                'periode' => $t->periode,
                'jumlah' => (float) $t->jumlah,
                'denda' => (float) $t->denda,
                'jatuh_tempo' => $t->jatuh_tempo?->toDateString(),
            ])->values()->all(),
            'top_properti' => $topProperti,
            'rincian_tiap_kos' => $rincianTiapKos,
            'transaksi_detail' => $transaksiDetail,
        ];
    }
}