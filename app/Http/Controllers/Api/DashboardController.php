<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatPesan;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\PesanBantuan;
use App\Models\Pengeluaran;
use App\Services\KwitansiService;
use App\Services\PatunganService;
use App\Services\PembayaranService;
use App\Services\PemilikRekapService;
use App\Services\TagihanService;
use App\Support\GrafikBulan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function anakKos(Request $request): JsonResponse
    {
        $id = $request->user()->id;

        $scopeSewa = fn ($q) => $q->where('anak_kos_id', $id)
            ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $id)->where('status', 'aktif'));

        $penyewaanAktif = Penyewaan::where($scopeSewa)->where('status', 'aktif')->count();

        $tagihanBelumBayar = Tagihan::whereHas('penyewaan', $scopeSewa)
            ->where('status', '!=', 'lunas')
            ->count();

        $totalDibayar = (int) Pembayaran::where('anak_kos_id', $id)
            ->where('status', 'diverifikasi')
            ->sum('jumlah');

        $jumlahFavorit = $request->user()->favorits()->count();

        $pesanBelumDibaca = $request->user()->pesanBelumDibaca();

        $kotaAktif = Penyewaan::where($scopeSewa)
            ->where('status', 'aktif')
            ->with('properti:id,kota')
            ->select(['id', 'properti_id'])
            ->first()?->properti?->kota;

        $propertiTerpakai = Penyewaan::where($scopeSewa)
            ->where('status', 'aktif')
            ->pluck('properti_id');

        $idFavorit = $request->user()->favorits()->pluck('propertis.id');

        $tagihanBerikutnya = Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan', $scopeSewa)
            ->with(['penyewaan.kamar.properti'])
            ->orderBy('jatuh_tempo')
            ->first();

        $rekomendasi = Properti::query()
            ->where('status', 'aktif')
            ->whereNotIn('id', $propertiTerpakai)
            ->whereNotIn('id', $idFavorit)
            ->when($kotaAktif, fn ($q) => $q->where('kota', $kotaAktif))
            ->withCount([
                'kamars as total_kamar',
                'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
            ])
            ->orderByDesc('kamar_terisi')
            ->limit(4)
            ->get()
            ->filter(fn ($p) => $p->total_kamar > $p->kamar_terisi);

        return response()->json([
            'penyewaan_aktif' => $penyewaanAktif,
            'tagihan_belum_bayar' => $tagihanBelumBayar,
            'total_dibayar' => $totalDibayar,
            'jumlah_favorit' => $jumlahFavorit,
            'pesan_belum_dibaca' => $pesanBelumDibaca,
            'tagihan_berikutnya' => $tagihanBerikutnya ? (function () use ($tagihanBerikutnya) {
                TagihanService::sinkronDenda($tagihanBerikutnya);

                return [
                    'id' => $tagihanBerikutnya->id,
                    'periode' => $tagihanBerikutnya->periode,
                    'jumlah' => (float) $tagihanBerikutnya->jumlah,
                    'denda' => (float) $tagihanBerikutnya->denda,
                    'hari_telat' => TagihanService::hariTelat($tagihanBerikutnya),
                    'denda_per_hari' => TagihanService::dendaPerHari($tagihanBerikutnya),
                    'total' => (float) $tagihanBerikutnya->jumlah + (float) $tagihanBerikutnya->denda,
                    'jatuh_tempo' => $tagihanBerikutnya->jatuh_tempo?->toDateString(),
                    'properti' => $tagihanBerikutnya->penyewaan?->kamar?->properti?->nama,
                    'kamar' => $tagihanBerikutnya->penyewaan?->kamar?->nama,
                ];
            })() : null,
            'rekomendasi' => $rekomendasi->values()->map(fn (Properti $p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'kota' => $p->kota,
                'alamat' => $p->alamat,
                'harga' => $p->harga !== null ? (float) $p->harga : null,
                'jenis_harga' => $p->jenis_harga,
                'foto' => $p->foto ? '/storage/'.$p->foto : null,
                'total_kamar' => (int) $p->total_kamar,
                'kamar_terisi' => (int) $p->kamar_terisi,
            ])->all(),
        ]);
    }

    public function anakKosPenyewaan(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        $sewaans = Penyewaan::with(['kamar:id,nama,harga_sewa_bulanan', 'properti:id,nama,foto', 'anggotas.user:id,nama'])
            ->where(fn ($q) => $q->where('anak_kos_id', $uid)
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $uid)->where('status', 'aktif')))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'kamar' => $s->kamar?->nama,
                'properti' => $s->properti?->nama,
                'properti_foto' => $s->properti?->foto ? '/storage/'.$s->properti->foto : null,
                'tanggal_masuk' => $s->tanggal_masuk,
                'tanggal_keluar' => $s->tanggal_keluar,
                'status' => $s->status,
                'permintaan_keluar_pada' => $s->permintaan_keluar_pada,
                'mode_hunian' => $s->mode_hunian ?? 'tunggal',
                'is_utama' => $s->anak_kos_id === $uid,
                'ktp_path' => null,
                'butuh_ktp' => $s->anak_kos_id === $uid
                    ? ! $s->ktp_path
                    : ! $s->anggotas->firstWhere('user_id', $uid)?->ktp_path,
                'anggotas' => $s->anggotas->map(fn ($a) => [
                    'user_id' => $a->user_id,
                    'nama' => $a->user?->nama,
                    'porsi_persen' => (int) $a->porsi_persen,
                    'status' => $a->status,
                ])->values(),
            ]);

        return response()->json($sewaans);
    }

    public function anakKosTagihan(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        $tagihans = Tagihan::select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo', 'status', 'created_at'])
            ->with([
                'penyewaan:id,properti_id,kamar_id,mode_hunian',
                'penyewaan.kamar:id,nama',
                'penyewaan.properti:id,nama,denda_per_hari',
                'penyewaan.anggotas:id,penyewaan_id,user_id,status',
                'pembayarans:id,tagihan_id,anak_kos_id,jumlah,status',
            ])
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $uid)
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $uid)->where('status', 'aktif')))
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get()
            ->map(function ($t) use ($uid) {
                $porsi = null;
                if ($t->penyewaan) {
                    // Read-only: hitung denda berjalan in-memory tanpa UPDATE per baris.
                    if ($t->status !== 'lunas') {
                        $t->setAttribute('denda', TagihanService::dendaBerjalan($t));
                    }
                    $porsi = PatunganService::porsiTagihan($t->penyewaan, $t);
                    $sudah = (float) $t->pembayarans
                        ->where('status', 'diverifikasi')
                        ->where('anak_kos_id', $uid)
                        ->sum('jumlah');
                }

                return [
                    'id' => $t->id,
                    'periode' => $t->periode,
                    'kamar' => $t->penyewaan?->kamar?->nama,
                    'properti' => $t->penyewaan?->properti?->nama,
                    'jumlah' => (float) $t->jumlah,
                    'denda' => (float) $t->denda,
                    'hari_telat' => TagihanService::hariTelat($t),
                    'denda_per_hari' => TagihanService::dendaPerHari($t),
                    'total' => (float) $t->jumlah + (float) $t->denda,
                    'jatuh_tempo' => $t->jatuh_tempo,
                    'status' => $t->status,
                    'mode_hunian' => $t->penyewaan?->mode_hunian ?? 'tunggal',
                    'porsi_saya' => $porsi,
                    'sudah_bayar_saya' => $sudah ?? 0,
                    'sisa_porsi_saya' => isset($porsi) ? max(0, $porsi - ($sudah ?? 0)) : null,
                ];
            });

        return response()->json($tagihans);
    }

    public function anakKosPembayaran(Request $request): JsonResponse
    {
        $pembayarans = Pembayaran::with(['tagihan:periode', 'verifikator:id,nama'])
            ->where('anak_kos_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'periode' => $p->tagihan?->periode,
                'metode' => $p->metode,
                'jumlah' => (float) $p->jumlah,
                'bukti' => $p->bukti ? '/storage/'.$p->bukti : null,
                'status' => $p->status,
                'diverifikasi_oleh' => $p->verifikator?->nama,
                'verified_at' => $p->verified_at,
                'created_at' => $p->created_at,
                'nomor_kwitansi' => $p->nomor_kwitansi,
                'kwitansi_url' => $p->file_kwitansi ? '/storage/'.$p->file_kwitansi : null,
            ]);

        return response()->json($pembayarans);
    }

    public function kwitansiSaya(Request $request, int $pembayaranId)
    {
        $pembayaran = Pembayaran::where('id', $pembayaranId)
            ->where('anak_kos_id', $request->user()->id)
            ->where('status', 'diverifikasi')
            ->first();

        if (! $pembayaran) {
            return response()->json(['message' => 'Kwitansi tidak ditemukan.'], 404);
        }

        KwitansiService::untuk($pembayaran);

        $path = $pembayaran->refresh()->file_kwitansi;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File kwitansi belum tersedia.'], 404);
        }

        return response()->download(
            Storage::disk('public')->path($path),
            ($pembayaran->nomor_kwitansi ?? 'kwitansi').'.pdf'
        );
    }

    public function anakKosBayar(Request $request): JsonResponse
    {
        $tagihanAwal = Tagihan::with(['penyewaan.anggotas'])->find($request->input('tagihan_id'));

        if (! $tagihanAwal || ! $tagihanAwal->penyewaan) {
            return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404);
        }

        $wajibAwal = TagihanService::wajibBayar($tagihanAwal, $request->user()->id);

        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'metode' => 'required|in:transfer,cash',
            'jumlah' => 'required|numeric|min:1|max:'.$wajibAwal,
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
        ], [
            'metode.in' => 'Metode pembayaran harus transfer atau tunai.',
            'jumlah.max' => 'Nominal melebihi kewajiban '.TagihanService::rupiah($wajibAwal).'. Bayar harus pas.',
            'bukti.mimes' => 'Bukti harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti.max' => 'Ukuran bukti maksimal 2MB.',
        ]);

        if ($request->input('metode') === 'transfer' && ! $request->hasFile('bukti')) {
            return response()->json(['message' => 'Lampirkan bukti transfer terlebih dahulu.'], 422);
        }

        $tagihan = Tagihan::with(['penyewaan.anggotas'])->findOrFail($validated['tagihan_id']);

        $penghuni = $tagihan->penyewaan ? $tagihan->penyewaan->idPenghuniAktif() : [];

        if (! in_array($request->user()->id, $penghuni, true)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($tagihan->status === 'lunas') {
            return response()->json(['message' => 'Tagihan sudah lunas.'], 422);
        }

        if ($tagihan->pembayarans()->where('status', 'menunggu_verifikasi')->exists()) {
            return response()->json(['message' => 'Pembayaran untuk tagihan ini masih menunggu verifikasi admin/pemilik.'], 422);
        }

        $rincian = TagihanService::rincian($tagihan);
        $isPatungan = (bool) $tagihan->penyewaan?->isPatungan();
        $wajib = TagihanService::wajibBayar($tagihan, $request->user()->id);

        if ((float) $validated['jumlah'] < $wajib) {
            $kurang = TagihanService::rupiah($wajib - (float) $validated['jumlah']);

            return response()->json(['message' => 'Nominal kurang '.$kurang.'. Total saat ini '.TagihanService::rupiah($wajib)
                .' (sewa '.TagihanService::rupiah($rincian['sewa'])
                .($rincian['denda'] > 0 ? ' + denda '.$rincian['hari_telat'].' hari '.TagihanService::rupiah($rincian['denda']) : '')
                .($isPatungan ? ' — porsi patunganmu' : '').'). Bayar harus pas, tidak boleh kurang.'], 422);
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti', 'public');
        }

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $validated['tagihan_id'],
            'anak_kos_id' => $request->user()->id,
            'metode' => $validated['metode'],
            'jumlah' => $validated['jumlah'],
            'bukti' => $buktiPath,
            'status' => 'menunggu_verifikasi',
        ]);

        ChatPesan::notifikasiPembayaranDiajukan($pembayaran);

        return response()->json(['message' => 'Pembayaran berhasil diajukan.'], 201);
    }

    public function anakKosAjukanKeluar(Request $request, int $sewaanId): JsonResponse
    {
        $uid = $request->user()->id;

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->where('anak_kos_id', $uid)
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $uid)->where('status', 'aktif')))
            ->with(['kamar', 'anggotas', 'tagihans.pembayarans'])
            ->first();

        if (! $sewaan) {
            return response()->json(['message' => 'Penyewaan tidak ditemukan.'], 404);
        }

        try {
            $hasil = CheckoutService::keluarPenghuni($sewaan, $uid);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($hasil['jenis'] === 'partial') {
            return response()->json(['message' => 'Kamu sudah keluar dari kamar patungan. Porsi tagihan berikutnya menjadi tanggung jawab penghuni yang stay.']);
        }

        return response()->json(['message' => 'Check-out berhasil. Kamar kembali tersedia.']);
    }

    public function pemilik(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        // Filter grafik: periode (3/6/12/24) + properti tertentu + custom range.
        $bulanCount = \App\Services\SubscriptionService::clampPeriode($user, max(1, (int) $request->input('periode', 6)));
        $propertiId = $request->input('properti_id') ? (int) $request->input('properti_id') : null;

        $totalProperti = Properti::where('pemilik_id', $userId)
            ->when($propertiId, fn ($q) => $q->where('id', $propertiId))
            ->count();

        $kamarStatus = Kamar::whereHas('properti', fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId)))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalKamar = (int) $kamarStatus->sum();
        $kamarTerisi = (int) ($kamarStatus['terisi'] ?? 0);
        $kamarPerbaikan = (int) ($kamarStatus['perbaikan'] ?? 0);

        $awalBulanIni = now()->startOfMonth();
        $akhirBulanIni = now()->copy()->endOfMonth();
        $awalBulanLalu = now()->subMonth()->startOfMonth();
        $akhirBulanLalu = now()->subMonth()->endOfMonth();

        $scopeBayarPemilik = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId));

        $pendapatanBulanIni = (int) Pembayaran::where('status', 'diverifikasi')
            ->whereBetween('verified_at', [$awalBulanIni, $akhirBulanIni])
            ->whereHas('tagihan.penyewaan.properti', $scopeBayarPemilik)
            ->sum('jumlah');

        $pendapatanBulanLalu = (int) Pembayaran::where('status', 'diverifikasi')
            ->whereBetween('verified_at', [$awalBulanLalu, $akhirBulanLalu])
            ->whereHas('tagihan.penyewaan.properti', $scopeBayarPemilik)
            ->sum('jumlah');

        $scopeProperti = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where($w->getModel()->getTable().'.id', $propertiId));

        $scopePropertiId = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId));

        $pengeluaranBulanIni = (int) Pengeluaran::whereHas('properti', $scopeProperti)
            ->whereBetween('tanggal', [$awalBulanIni->toDateString(), $akhirBulanIni->toDateString()])
            ->sum('jumlah');

        $pengeluaranBulanLalu = (int) Pengeluaran::whereHas('properti', $scopeProperti)
            ->whereBetween('tanggal', [$awalBulanLalu->toDateString(), $akhirBulanLalu->toDateString()])
            ->sum('jumlah');

        $kategoriPengeluaran = Pengeluaran::whereHas('properti', $scopeProperti)
            ->where('tanggal', '>=', now()->startOfMonth()->subMonths($bulanCount - 1)->toDateString())
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->limit(7)
            ->get()
            ->map(fn ($r) => ['label' => ucwords(str_replace('_', ' ', (string) $r->kategori)), 'value' => (int) round((float) $r->total)])
            ->values()
            ->all();

        $penyewaanAktif = Penyewaan::where('status', 'aktif')
            ->whereHas('properti', $scopePropertiId)
            ->count();

        $okupansi = $totalKamar > 0 ? (int) round($kamarTerisi / $totalKamar * 100) : 0;

        $tagihanBelumQuery = fn () => Tagihan::where('status', '!=', 'lunas')
            ->whereHas('penyewaan.properti', $scopePropertiId);

        $tagihanBelumCount = $tagihanBelumQuery()->count();
        $nilaiTagihanBelum = (int) round((float) ($tagihanBelumQuery()->selectRaw('COALESCE(SUM(jumlah + denda), 0) as total')->value('total') ?? 0));
        $tagihanTelatCount = $tagihanBelumQuery()->where('jatuh_tempo', '<', today()->toDateString())->count();
        $tagihanBelumList = $tagihanBelumQuery()
            ->select(['id', 'penyewaan_id', 'periode', 'jumlah', 'denda', 'jatuh_tempo'])
            ->with(['penyewaan.anakKos:id,nama', 'penyewaan.kamar:id,nama', 'penyewaan.properti:id,nama'])
            ->orderBy('jatuh_tempo')
            ->limit(6)
            ->get();

        $scope = fn ($q) => $q->whereHas('tagihan.penyewaan.properti', $scopePropertiId);

        $chart = $this->monthlyChart(
            $scope,
            fn ($q) => $q->whereHas('penyewaan.properti', $scopePropertiId),
            $bulanCount,
        );

        $scopeTagihan = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId));

        $rekapPerBulan = $this->rekapPemilikPerBulan($userId, $totalKamar, $bulanCount, $propertiId);

        $scopePembayaranTerbaru = fn ($q) => $q->whereHas('tagihan.penyewaan.properti', $scopePropertiId);

        $pembayaranTerbaru = Pembayaran::where('status', 'diverifikasi')
            ->where($scopePembayaranTerbaru)
            ->with(['tagihan.penyewaan.anakKos:id,nama', 'tagihan.penyewaan.properti:id,nama'])
            ->latest('verified_at')
            ->limit(6)
            ->get();

        $pembayaranMenunggu = Pembayaran::where('status', 'menunggu_verifikasi')
            ->where($scopePembayaranTerbaru)
            ->with(['anakKos:id,nama', 'tagihan.penyewaan.kamar:id,nama', 'tagihan.penyewaan.properti:id,nama'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'total_properti' => $totalProperti,
            'total_kamar' => $totalKamar,
            'kamar_terisi' => $kamarTerisi,
            'kamar_kosong' => max(0, $totalKamar - $kamarTerisi),
            'kamar_perbaikan' => (int) $kamarPerbaikan,
            'okupansi' => $okupansi,
            'pendapatan_bulan_ini' => $pendapatanBulanIni,
            'pendapatan_bulan_lalu' => $pendapatanBulanLalu,
            'pengeluaran_bulan_ini' => $pengeluaranBulanIni,
            'pengeluaran_bulan_lalu' => $pengeluaranBulanLalu,
            'laba_bersih_bulan_ini' => $pendapatanBulanIni - $pengeluaranBulanIni,
            'laba_bersih_bulan_lalu' => $pendapatanBulanLalu - $pengeluaranBulanLalu,
            'tagihan_belum_bayar' => $tagihanBelumCount,
            'nilai_tagihan_belum' => $nilaiTagihanBelum,
            'tagihan_telat' => $tagihanTelatCount,
            'kategori_pengeluaran' => $kategoriPengeluaran,
            'penyewaan_aktif' => $penyewaanAktif,
            'chart' => $chart,
            'rekap' => $rekapPerBulan,
            'pembayaran_terbaru' => $pembayaranTerbaru->map(fn (Pembayaran $p) => [
                'id' => $p->id,
                'anak_kos_nama' => $p->tagihan?->penyewaan?->anakKos?->nama ?? 'Penyewa',
                'properti_nama' => $p->tagihan?->penyewaan?->properti?->nama,
                'periode' => $p->tagihan?->periode,
                'jumlah' => (float) $p->jumlah,
                'verified_at' => $p->verified_at?->toDateString(),
                'nomor_kwitansi' => $p->nomor_kwitansi,
                'kwitansi_url' => $p->file_kwitansi ? '/storage/'.$p->file_kwitansi : null,
            ])->values(),
            'pembayaran_menunggu' => $pembayaranMenunggu->map(fn (Pembayaran $p) => [
                'id' => $p->id,
                'anak_kos_nama' => $p->anakKos?->nama ?? 'Penyewa',
                'properti_nama' => $p->tagihan?->penyewaan?->properti?->nama,
                'kamar_nama' => $p->tagihan?->penyewaan?->kamar?->nama,
                'periode' => $p->tagihan?->periode,
                'metode' => $p->metode,
                'jumlah' => (float) $p->jumlah,
            ])->values(),
            'tagihan_list' => $tagihanBelumList->map(fn (Tagihan $t) => [
                'anak_kos_nama' => $t->penyewaan?->anakKos?->nama ?? 'Penyewa',
                'kamar_nama' => $t->penyewaan?->kamar?->nama,
                'periode' => $t->periode,
                'jumlah' => (float) $t->jumlah,
                'denda' => (float) $t->denda,
                'jatuh_tempo' => $t->jatuh_tempo?->toDateString(),
            ])->values(),
            'funnel' => $this->funnelStages(
                Penyewaan::whereHas('properti', $scopeTagihan)->count(),
                $penyewaanAktif,
                $scopeTagihan,
            ),
        ]);
    }

    /**
     * Deret bulanan pendapatan, pengeluaran, laba bersih, dan okupansi
     * untuk properti milik pemilik. Mendukung periode 1-24 bulan + filter properti.
     */
    private function rekapPemilikPerBulan(int $userId, int $totalKamar, int $bulanCount = 6, ?int $propertiId = null): array
    {
        $bulanCount = max(1, min(24, $bulanCount));
        $scope = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where($w->getModel()->getTable().'.id', $propertiId));
        $scopeId = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId));

        $mulaiRange = now()->startOfMonth()->subMonths($bulanCount - 1);

        $pendapatanPerBulan = Pembayaran::where('status', 'diverifikasi')
            ->whereHas('tagihan.penyewaan.properti', $scopeId)
            ->where('verified_at', '>=', $mulaiRange)
            ->get(['verified_at', 'jumlah'])
            ->groupBy(fn ($p) => $p->verified_at->format('m/Y'))
            ->map(fn ($rows) => (int) $rows->sum('jumlah'));

        $pengeluaranPerBulan = Pengeluaran::whereHas('properti', $scope)
            ->where('tanggal', '>=', $mulaiRange->toDateString())
            ->get(['tanggal', 'jumlah'])
            ->groupBy(fn ($p) => $p->tanggal->format('m/Y'))
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')));

        $sewaansRange = Penyewaan::whereHas('properti', $scopeId)
            ->where('tanggal_masuk', '<=', now()->endOfMonth()->toDateString())
            ->where(fn ($w) => $w->whereNull('tanggal_keluar')->orWhere('tanggal_keluar', '>=', $mulaiRange->toDateString()))
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

            $kamarTerisiBulan = $sewaansRange
                ->filter(fn ($s) => $s->tanggal_masuk->lte($end) && ($s->tanggal_keluar === null || $s->tanggal_keluar->gte($start)))
                ->pluck('kamar_id')
                ->unique()
                ->count();

            $okupansi[] = $totalKamar > 0 ? (int) round($kamarTerisiBulan / $totalKamar * 100) : 0;
        }

        return [
            'labels' => $labels,
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran,
            'laba' => $laba,
            'okupansi' => $okupansi,
        ];
    }

    /**
     * Halaman grafik detail pemilik: filter periode + properti,
     * plus aging piutang, top properti, dan tren lunas-vs-belum.
     */
    public function grafik(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;
        $bulanCount = \App\Services\SubscriptionService::clampPeriode($user, max(1, (int) $request->input('periode', 12)));
        $propertiId = $request->input('properti_id') ? (int) $request->input('properti_id') : null;

        $scopeId = fn ($q) => $q->where('pemilik_id', $userId)
            ->when($propertiId, fn ($w) => $w->where('propertis.id', $propertiId));

        $propertis = Properti::where('pemilik_id', $userId)
            ->withCount([
                'kamars as total_kamar',
                'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
            ])
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $totalKamar = (int) $propertis->when($propertiId, fn ($c) => $c->where('id', $propertiId))->sum('total_kamar');

        $scopeBayar = fn ($q) => $q->whereHas('tagihan.penyewaan.properti', $scopeId);
        $scopeTagihan = fn ($q) => $q->whereHas('penyewaan.properti', $scopeId);

        $chart = $this->monthlyChart($scopeBayar, $scopeTagihan, $bulanCount);
        $rekap = $this->rekapPemilikPerBulan($userId, $totalKamar, $bulanCount, $propertiId);

        $mulai = now()->startOfMonth()->subMonths($bulanCount - 1);

        // Tren jumlah transaksi + nilai per bulan: 1 query agregasi, bukan 2x get semua rows.
        $trenRows = Pembayaran::where('status', 'diverifikasi')
            ->where('verified_at', '>=', $mulai)
            ->where($scopeBayar)
            ->selectRaw(GrafikBulan::kolomBulan('verified_at').', COUNT(*) as jml, SUM(jumlah) as nilai')
            ->groupBy('bulan')
            ->get()
            ->keyBy('bulan');

        $counts = [];
        $nilais = [];
        foreach (range($bulanCount - 1, 0) as $i) {
            $key = now()->startOfMonth()->subMonths($i)->format('m/Y');
            $counts[] = (int) ($trenRows[$key]->jml ?? 0);
            $nilais[] = (int) round((float) ($trenRows[$key]->nilai ?? 0));
        }

        // Aging piutang + total: 1 query agregasi CASE, tanpa hydrate semua model + with relation.
        $hariIni = today()->toDateString();
        $kurang7 = GrafikBulan::kurangiHari('?', 7);
        $kurang30 = GrafikBulan::kurangiHari('?', 30);
        $agregat = Tagihan::where('status', '!=', 'lunas')
            ->where($scopeTagihan)
            ->selectRaw('COUNT(*) as jml, COALESCE(SUM(jumlah + denda), 0) as nilai')
            ->selectRaw('COALESCE(SUM(CASE WHEN jatuh_tempo >= ? THEN jumlah + denda ELSE 0 END), 0) as belum_jatuh_tempo', [$hariIni])
            ->selectRaw("COALESCE(SUM(CASE WHEN jatuh_tempo < ? AND jatuh_tempo >= {$kurang7} THEN jumlah + denda ELSE 0 END), 0) as telat_1_7", [$hariIni, $hariIni])
            ->selectRaw("COALESCE(SUM(CASE WHEN jatuh_tempo < {$kurang7} AND jatuh_tempo >= {$kurang30} THEN jumlah + denda ELSE 0 END), 0) as telat_8_30", [$hariIni, $hariIni])
            ->selectRaw("COALESCE(SUM(CASE WHEN jatuh_tempo < {$kurang30} THEN jumlah + denda ELSE 0 END), 0) as telat_lebih_30", [$hariIni])
            ->first();

        $aging = [
            'belum_jatuh_tempo' => (int) round((float) ($agregat->belum_jatuh_tempo ?? 0)),
            'telat_1_7' => (int) round((float) ($agregat->telat_1_7 ?? 0)),
            'telat_8_30' => (int) round((float) ($agregat->telat_8_30 ?? 0)),
            'telat_lebih_30' => (int) round((float) ($agregat->telat_lebih_30 ?? 0)),
        ];
        $belumCount = (int) ($agregat->jml ?? 0);
        $belumNilai = (int) round((float) ($agregat->nilai ?? 0));

        // Top properti berdasarkan pendapatan terverifikasi pada periode: join + group, tanpa with+group PHP.
        $pendapatanProperti = DB::table('pembayarans')
            ->join('tagihans', 'tagihans.id', '=', 'pembayarans.tagihan_id')
            ->join('penyewaans', 'penyewaans.id', '=', 'tagihans.penyewaan_id')
            ->join('propertis', 'propertis.id', '=', 'penyewaans.properti_id')
            ->where('pembayarans.status', 'diverifikasi')
            ->where('pembayarans.verified_at', '>=', $mulai)
            ->where('propertis.pemilik_id', $userId)
            ->when($propertiId, fn ($q) => $q->where('propertis.id', $propertiId))
            ->selectRaw('penyewaans.properti_id as properti_id, SUM(pembayarans.jumlah) as total')
            ->groupBy('penyewaans.properti_id')
            ->pluck('total', 'properti_id');

        $topProperti = $propertis->map(fn ($p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'total_kamar' => (int) $p->total_kamar,
            'kamar_terisi' => (int) $p->kamar_terisi,
            'pendapatan' => (int) ($pendapatanProperti[$p->id] ?? 0),
        ])->sortByDesc('pendapatan')->values()->take(10)->all();

        return response()->json([
            'periode' => $bulanCount,
            'properti_id' => $propertiId,
            'propertis' => $propertis->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama])->values(),
            'chart' => $chart,
            'rekap' => $rekap,
            'tren_transaksi' => ['jumlah' => $counts, 'nilai' => $nilais],
            'aging_piutang' => array_map('intval', $aging),
            'tagihan_belum' => [
                'count' => $belumCount,
                'nilai' => $belumNilai,
            ],
            'top_properti' => $topProperti,
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $response = $this->grafik($request);
        $data = $response->getData(true);

        return response()->json([
            'periode' => $data['periode'] ?? null,
            'chart' => $data['chart'] ?? null,
            'rekap' => $data['rekap'] ?? null,
            'tren_transaksi' => $data['tren_transaksi'] ?? null,
            'aging_piutang' => $data['aging_piutang'] ?? null,
            'top_properti' => $data['top_properti'] ?? null,
        ]);
    }

    public function rekapPremium(Request $request): JsonResponse
    {
        try {
            $data = PemilikRekapService::data($request->user()->id, $request->query('bulan'), \App\Services\SubscriptionService::reportTier($request->user()));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($data);
    }

    public function pemilikSewaans(Request $request): JsonResponse
    {
        $sewaans = Penyewaan::whereHas('properti', fn ($q) => $q->where('pemilik_id', $request->user()->id))
            ->with(['anakKos:id,nama', 'kamar:id,nama', 'kamar.properti:id,nama', 'tagihans', 'anggotas.user:id,nama'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($s) {
                $belumLunas = $s->tagihans->where('status', '!=', 'lunas');
                $sisa = $belumLunas->sum(fn ($t) => $t->jumlah + $t->denda);
                $telat = $belumLunas->filter(fn ($t) => $t->denda > 0)->count();

                return [
                    'id' => $s->id,
                    'anak_kos_nama' => $s->anakKos?->nama,
                    'kamar_nama' => $s->kamar?->nama,
                    'properti_nama' => $s->kamar?->properti?->nama ?? $s->properti?->nama,
                    'tanggal_masuk' => $s->tanggal_masuk,
                    'tanggal_keluar' => $s->tanggal_keluar,
                    'status' => $s->status,
                    'permintaan_keluar_pada' => $s->permintaan_keluar_pada,
                    'ktp_url' => null,
                    'butuh_ktp' => ! $s->ktp_path,
                    'mode_hunian' => $s->mode_hunian ?? 'tunggal',
                    'anggotas' => $s->anggotas->map(fn ($a) => [
                        'user_id' => $a->user_id,
                        'nama' => $a->user?->nama,
                        'porsi_persen' => (int) $a->porsi_persen,
                        'status' => $a->status,
                        'ktp_url' => null,
                    ])->values(),
                    'sisa_tagihan' => $sisa,
                    'tagihan_belum_bayar' => $belumLunas->count(),
                    'telat' => $telat,
                ];
            })
            ->values();

        return response()->json($sewaans);
    }

    public function ktpPenyewaan(Request $request, int $sewaanId)
    {
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->whereHas('properti', fn ($q) => $q->where('pemilik_id', $request->user()->id))
            ->with('anggotas')
            ->first();

        if (! $sewaan) {
            return response()->json(['message' => 'Penyewaan tidak ditemukan.'], 404);
        }

        $userId = $request->input('user_id') ? (int) $request->input('user_id') : $sewaan->anak_kos_id;

        $path = $userId === $sewaan->anak_kos_id
            ? $sewaan->ktp_path
            : $sewaan->anggotas->firstWhere('user_id', $userId)?->ktp_path;

        if (! $path || ! \App\Services\KtpStorage::ada($path)) {
            return response()->json(['message' => 'File KTP belum tersedia.'], 404);
        }

        return response()->download(
            \App\Services\KtpStorage::pathAbsolut($path),
            'ktp-sewaan-'.$sewaan->id.'-'.$userId.'.'.pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    public function tambahAnggota(Request $request, int $sewaanId): JsonResponse
    {
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->where('anak_kos_id', $request->user()->id)
                ->orWhereHas('properti', fn ($w) => $w->where('pemilik_id', $request->user()->id)))
            ->with(['kamar', 'anggotas'])
            ->first();

        if (! $sewaan) {
            return response()->json(['message' => 'Penyewaan tidak ditemukan.'], 404);
        }

        if (! $request->user()->hasRole('anak_kos') && ! $request->user()->hasAnyRole(['pemilik', 'admin', 'super_admin'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'ktp' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
        ], [
            'email.exists' => 'Akun teman tidak ditemukan. Minta temanmu daftar dulu.',
            'ktp.required' => 'Foto KTP teman wajib diunggah.',
        ]);

        $teman = User::where('email', $validated['email'])->first();

        if (! $teman->hasRole('anak_kos')) {
            return response()->json(['message' => 'Hanya akun anak kos yang bisa jadi teman sekamar.'], 422);
        }

        $ktpPath = \App\Services\KtpStorage::simpan($request->file('ktp'));

        try {
            $anggota = PatunganService::tambahAnggota($sewaan, $teman, $ktpPath);
        } catch (\DomainException $e) {
            \App\Services\KtpStorage::hapus($ktpPath);

            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => "Teman sekamar {$teman->nama} berhasil ditambahkan (patungan 50/50).",
            'anggota' => ['user_id' => $anggota->user_id, 'porsi_persen' => (int) $anggota->porsi_persen],
        ], 201);
    }

    public function keluarAnggota(Request $request, int $sewaanId): JsonResponse
    {
        $uid = $request->user()->id;

        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->where('anak_kos_id', $uid)
                ->orWhereHas('anggotas', fn ($w) => $w->where('user_id', $uid)->where('status', 'aktif')))
            ->with(['kamar', 'anggotas', 'tagihans.pembayarans'])
            ->first();

        if (! $sewaan) {
            return response()->json(['message' => 'Penyewaan tidak ditemukan.'], 404);
        }

        // Hanya untuk patungan (ada yang stay). Sewa tunggal pakai endpoint keluar biasa.
        $adaYangStay = $sewaan->anggotas->where('status', 'aktif')->where('user_id', '!=', $uid)->isNotEmpty()
            || ($sewaan->anak_kos_id !== $uid);

        if (! $adaYangStay) {
            return response()->json(['message' => 'Gunakan menu check-out biasa untuk sewa tunggal.'], 422);
        }

        try {
            PatunganService::keluarkan($sewaan, $uid);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Kamu sudah keluar dari kamar patungan. Pastikan porsimu sudah lunas.']);
    }

    public function pemilikCheckOut(Request $request, int $sewaanId): JsonResponse
    {
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('status', 'aktif')
            ->whereHas('properti', fn ($q) => $q->where('pemilik_id', $request->user()->id))
            ->with(['kamar', 'anakKos'])
            ->first();

        if (! $sewaan) {
            return response()->json(['message' => 'Penyewaan tidak ditemukan.'], 404);
        }

        $nama = $sewaan->anakKos?->nama;
        $kamarNama = $sewaan->kamar?->nama;

        try {
            $hasil = CheckoutService::checkoutPemilik($sewaan, $request->user()->id);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tambahan = $hasil['kamar_tersedia'] ? ' Kamar kembali tersedia.' : ' Kamar tetap terisi karena masih ada anggota patungan yang stay.';
        $catatan = $hasil['tagihan_belum_lunas'] > 0 ? " Perhatian: masih ada {$hasil['tagihan_belum_lunas']} tagihan belum lunas." : '';

        return response()->json(['message' => "Check-out $nama dari kamar $kamarNama berhasil.{$tambahan}{$catatan}"]);
    }

    public function pemilikProperti(Request $request): JsonResponse
    {
        $propertis = Properti::withCount([
            'kamars as total_kamar',
            'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
        ])->with(['kamars:id,nama,kapasitas,harga_sewa_bulanan,jenis_harga,status,foto,properti_id'])
            ->where('pemilik_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($propertis->map(fn (Properti $p) => $this->formatProperti($p))->values());
    }

    public function rekap(Request $request): JsonResponse
    {
        try {
            $data = PemilikRekapService::data(
                $request->user()->id,
                $request->input('bulan'),
                \App\Services\SubscriptionService::reportTier($request->user()),
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($data);
    }

    public function admin(Request $request): JsonResponse
    {
        $user = $request->user();
        $kelolaan = $this->kelolaan($user);

        $pembayarans = Pembayaran::whereHas('tagihan.penyewaan.properti', $kelolaan)
            ->with(['anakKos:id,nama', 'tagihan:id,periode'])
            ->latest()
            ->limit(15)
            ->get();

        $checkouts = Penyewaan::whereHas('properti', $kelolaan)
            ->where('status', 'selesai')
            ->with(['anakKos:id,nama', 'kamar:id,nama', 'kamar.properti:id,nama'])
            ->latest('tanggal_keluar')
            ->limit(20)
            ->get();

        $bulanCount = min(24, max(1, (int) $request->input('periode', 6)));

        $growthLabels = collect(range($bulanCount - 1, 0))
            ->map(fn ($i) => now()->startOfMonth()->subMonths($i)->translatedFormat('M Y'))
            ->all();

        $userGrowthRows = User::with('roles:id,name')
            ->where('created_at', '>=', now()->startOfMonth()->subMonths($bulanCount - 1))
            ->get(['id', 'created_at'])
            ->groupBy(fn ($u) => $u->created_at->format('m/Y'));

        $userAnak = [];
        $userPemilik = [];

        foreach (range($bulanCount - 1, 0) as $i) {
            $key = now()->startOfMonth()->subMonths($i)->format('m/Y');
            $rows = $userGrowthRows[$key] ?? collect();
            $userAnak[] = $rows->filter(fn ($u) => $u->roles->pluck('name')->contains('anak_kos'))->count();
            $userPemilik[] = $rows->filter(fn ($u) => $u->roles->pluck('name')->contains('pemilik'))->count();
        }

        $nilaiTransaksi = $this->sumMonthly(
            fn () => Pembayaran::where('status', 'diverifikasi')->whereHas('tagihan.penyewaan.properti', $kelolaan),
            $bulanCount,
            'verified_at',
        );

        $sewaanStatus = Penyewaan::whereHas('properti', $kelolaan)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $pembayaranStatus = Pembayaran::whereHas('tagihan.penyewaan.properti', $kelolaan)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return response()->json([
            'total_tugas' => Properti::whereHas('admins', fn ($q) => $q->where('users.id', $user->id))->count(),
            'pembayaran_menunggu' => Pembayaran::where('status', 'menunggu_verifikasi')
                ->whereHas('tagihan.penyewaan.properti', $kelolaan)
                ->count(),
            'penyewaan_aktif' => Penyewaan::where('status', 'aktif')
                ->whereHas('properti', $kelolaan)
                ->count(),
            'total_kamar' => Kamar::whereHas('properti', $kelolaan)->count(),
            'kamar_terisi' => Kamar::where('status', 'terisi')->whereHas('properti', $kelolaan)->count(),
            'kamar_kosong' => Kamar::where('status', 'tersedia')->whereHas('properti', $kelolaan)->count(),
            'tagihan_belum_bayar' => Tagihan::where('status', '!=', 'lunas')
                ->whereHas('penyewaan.properti', $kelolaan)
                ->count(),
            'growth' => [
                'labels' => $growthLabels,
                'properti' => $this->growthMonthly(fn () => Properti::query()->where($this->kelolaan($user)), $bulanCount),
                'penyewaan' => $this->growthMonthly(fn () => Penyewaan::whereHas('properti', $kelolaan), $bulanCount),
                'pembayaran' => $this->growthMonthly(fn () => Pembayaran::where('status', 'diverifikasi')->whereHas('tagihan.penyewaan.properti', $kelolaan), $bulanCount, 'verified_at'),
            ],
            'funnel' => $this->funnelStages(
                Properti::whereHas('admins', fn ($q) => $q->where('users.id', $user->id))->count(),
                Penyewaan::where('status', 'aktif')->whereHas('properti', $kelolaan)->count(),
                $kelolaan,
            ),
            'chart' => $this->monthlyChart(
                fn ($q) => $q->whereHas('tagihan.penyewaan.properti', $kelolaan),
                fn ($q) => $q->whereHas('penyewaan.properti', $kelolaan),
            ),
            'user_growth' => ['labels' => $growthLabels, 'anak_kos' => $userAnak, 'pemilik' => $userPemilik],
            'transaksi_nilai' => $nilaiTransaksi,
            'sewaan_status' => $sewaanStatus,
            'pembayaran_status' => $pembayaranStatus,
            'pembayarans' => $pembayarans->map(fn (Pembayaran $p) => [
                'id' => $p->id,
                'anak_kos_nama' => $p->anakKos?->nama ?? '-',
                'periode' => $p->tagihan?->periode,
                'jumlah' => (float) $p->jumlah,
                'metode' => $p->metode,
                'bukti' => $p->bukti ? '/storage/'.$p->bukti : null,
                'status' => $p->status,
                'created_at' => $p->created_at,
            ])->values(),
            'checkouts' => $this->formatCheckouts($checkouts),
        ]);
    }

    public function superAdmin(Request $request): JsonResponse
    {
        $propertis = Properti::with('pemilik:id,nama')
            ->withCount(['kamars as total_kamar', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])
            ->orderBy('nama')
            ->limit(100)
            ->get();

        $checkouts = Penyewaan::where('status', 'selesai')
            ->with(['anakKos:id,nama', 'kamar:id,nama', 'kamar.properti:id,nama', 'kamar.properti.pemilik:id,nama'])
            ->latest('tanggal_keluar')
            ->limit(20)
            ->get();

        $bulanCount = min(24, max(1, (int) $request->input('periode', 6)));
        $monthStart = now()->startOfMonth()->subMonths($bulanCount - 1);

        $transaksiJumlah = $this->growthMonthly(fn () => Pembayaran::where('status', 'diverifikasi'), $bulanCount, 'verified_at');
        $transaksiNilai = $this->sumMonthly(fn () => Pembayaran::where('status', 'diverifikasi'), $bulanCount, 'verified_at');

        $pendapatanProperti = Pembayaran::where('status', 'diverifikasi')
            ->with('tagihan.penyewaan')
            ->get()
            ->groupBy(fn ($p) => $p->tagihan?->penyewaan?->properti_id)
            ->map(fn ($rows) => (int) round($rows->sum('jumlah')));

        $penyewaanPerProperti = Penyewaan::query()
            ->selectRaw('properti_id, count(*) as total')
            ->groupBy('properti_id')
            ->pluck('total', 'properti_id');

        $topPropertis = Properti::withCount([
            'kamars as total_kamar',
            'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi'),
        ])->withAvg('ulasans as rating', 'rating')
            ->with('pemilik:id,nama')
            ->whereIn('id', $pendapatanProperti->keys()->filter())
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'kota' => $p->kota,
                'pemilik_nama' => $p->pemilik?->nama ?? '-',
                'total_kamar' => (int) $p->total_kamar,
                'kamar_terisi' => (int) $p->kamar_terisi,
                'okupansi' => $p->total_kamar > 0 ? (int) round($p->kamar_terisi / $p->total_kamar * 100) : 0,
                'rating' => $p->rating !== null ? round((float) $p->rating, 1) : null,
                'pendapatan' => (int) ($pendapatanProperti[$p->id] ?? 0),
                'penyewaan' => (int) ($penyewaanPerProperti[$p->id] ?? 0),
            ])
            ->sortByDesc('pendapatan')
            ->values()
            ->take(5)
            ->all();

        $userGrowth = User::with('roles:id,name')
            ->where('created_at', '>=', $monthStart)
            ->get(['id', 'created_at'])
            ->groupBy(fn ($u) => $u->created_at->format('m/Y'));

        $propertiGrowth = Properti::where('created_at', '>=', $monthStart)
            ->get(['id', 'created_at'])
            ->groupBy(fn ($p) => $p->created_at->format('m/Y'));

        $penyewaanGrowth = Penyewaan::where('created_at', '>=', $monthStart)
            ->get(['id', 'created_at'])
            ->groupBy(fn ($p) => $p->created_at->format('m/Y'));

        $growthLabels = [];
        $growthUserTotal = [];
        $growthUserAnak = [];
        $growthUserPemilik = [];
        $growthProperti = [];
        $growthPenyewaan = [];

        foreach (range($bulanCount - 1, 0) as $i) {
            $key = now()->startOfMonth()->subMonths($i)->format('m/Y');
            $growthLabels[] = now()->startOfMonth()->subMonths($i)->translatedFormat('M Y');

            $users = $userGrowth[$key] ?? collect();
            $growthUserTotal[] = $users->count();
            $growthUserAnak[] = $users->filter(fn ($u) => $u->roles->pluck('name')->contains('anak_kos'))->count();
            $growthUserPemilik[] = $users->filter(fn ($u) => $u->roles->pluck('name')->contains('pemilik'))->count();
            $growthProperti[] = ($propertiGrowth[$key] ?? collect())->count();
            $growthPenyewaan[] = ($penyewaanGrowth[$key] ?? collect())->count();
        }

        return response()->json([
            'total_user' => User::count(),
            'total_pemilik' => User::role('pemilik')->count(),
            'total_anak_kos' => User::role('anak_kos')->count(),
            'total_admin' => User::role('admin')->count(),
            'total_properti' => Properti::count(),
            'total_kamar' => Kamar::count(),
            'kamar_terisi' => Kamar::where('status', 'terisi')->count(),
            'penyewaan_aktif' => Penyewaan::where('status', 'aktif')->count(),
            'pendapatan' => (int) Pembayaran::where('status', 'diverifikasi')->sum('jumlah'),
            'growth' => [
                'labels' => $growthLabels,
                'user_total' => $growthUserTotal,
                'user_anak_kos' => $growthUserAnak,
                'user_pemilik' => $growthUserPemilik,
                'properti' => $growthProperti,
                'penyewaan' => $growthPenyewaan,
            ],
            'funnel' => $this->funnelStages(
                User::count(),
                Penyewaan::where('status', 'aktif')->count(),
            ),
            'chart' => $this->monthlyChart(
                fn ($q) => $q->where('status', 'diverifikasi'),
                fn ($q) => $q,
            ),
            'transaction_growth' => ['labels' => $growthLabels, 'jumlah' => $transaksiJumlah, 'nilai' => $transaksiNilai],
            'top_propertis' => $topPropertis,
            'sewaan_status' => Penyewaan::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'platform_revenue' => null,
            'premium_conversion' => null,
            'propertis' => $propertis->map(fn (Properti $p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'pemilik_nama' => $p->pemilik?->nama ?? '-',
                'alamat' => $p->alamat,
                'total_kamar' => (int) $p->total_kamar,
                'kamar_terisi' => (int) $p->kamar_terisi,
                'status' => $p->status,
            ])->values(),
            'checkouts' => $this->formatCheckouts($checkouts),
        ]);
    }

    public function verifikasiPembayaran(Request $request, int $pembayaranId): JsonResponse
    {
        $status = $request->validate(['status' => 'required|in:diverifikasi,ditolak'])['status'];

        $pembayaran = Pembayaran::where('id', $pembayaranId)
            ->where('status', 'menunggu_verifikasi')
            ->when(! $request->user()->hasRole('super_admin'), fn ($q) => $q->whereHas('tagihan.penyewaan.properti', $this->kelolaanAtauPemilik($request->user())))
            ->with('anakKos', 'tagihan')
            ->first();

        if (! $pembayaran) {
            return response()->json(['message' => 'Pembayaran tidak ditemukan atau sudah diproses.'], 404);
        }

        try {
            $hasil = PembayaranService::verifikasi($pembayaran, $request->user()->id, $status);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($status === 'diverifikasi') {
            $nama = $pembayaran->anakKos?->nama ?? '-';
            $pesan = "Pembayaran $nama sebesar Rp".number_format($pembayaran->jumlah, 0, ',', '.').' diverifikasi.';

            if ($hasil['kwitansi_url']) {
                $pesan .= ' Kwitansi tersedia: '.$hasil['kwitansi_url'];
            }

            return response()->json([
                'message' => $pesan,
                'nomor_kwitansi' => $hasil['pembayaran']->nomor_kwitansi,
                'kwitansi_url' => $hasil['kwitansi_url'],
                'tagihan_lunas' => $hasil['tagihan_lunas'],
            ]);
        }

        return response()->json(['message' => 'Pengajuan pembayaran ditolak.']);
    }

    /**
     * Jumlah baris baru per bulan selama $bulanCount bulan terakhir.
     * $builderScope menerima query builder yang sudah di-scope (properti/penyewaan/pembayaran).
     */
    private function growthMonthly(callable $builderScope, int $bulanCount, string $kolom = 'created_at'): array
    {
        $rows = $builderScope()
            ->where($kolom, '>=', now()->startOfMonth()->subMonths(max(1, min(24, $bulanCount)) - 1))
            ->get([$kolom]);

        $grouped = collect($rows)->groupBy(fn ($r) => $r->{$kolom}->format('m/Y'));

        $out = [];
        foreach (range(max(1, min(24, $bulanCount)) - 1, 0) as $i) {
            $key = now()->startOfMonth()->subMonths($i)->format('m/Y');
            $out[] = ($grouped[$key] ?? collect())->count();
        }

        return $out;
    }

    /**
     * Total nilai (SUM jumlah) baris per bulan selama $bulanCount bulan terakhir.
     * Panggil dengan scope query (mis. Pembayaran diverifikasi).
     */
    private function sumMonthly(callable $builderScope, int $bulanCount, string $kolom = 'verified_at'): array
    {
        $rows = $builderScope()
            ->where($kolom, '>=', now()->startOfMonth()->subMonths(max(1, min(24, $bulanCount)) - 1))
            ->get([$kolom, 'jumlah']);

        $grouped = collect($rows)->groupBy(fn ($r) => $r->{$kolom}->format('m/Y'));

        $out = [];
        foreach (range(max(1, min(24, $bulanCount)) - 1, 0) as $i) {
            $key = now()->startOfMonth()->subMonths($i)->format('m/Y');
            $out[] = (int) round(($grouped[$key] ?? collect())->sum('jumlah'));
        }

        return $out;
    }

    /**
     * Builds the last N months of chart data (labels, revenue, paid/unpaid bills)
     * for a given role scope. $pembayaranScope filters Pembayaran, $tagihanScope filters Tagihan.
     * Hanya 2 query agregasi (bukan 3xN): rows ditarik sekali dalam range lalu dikelompokkan di PHP.
     */
    private function monthlyChart(callable $pembayaranScope, callable $tagihanScope, int $bulanCount = 6): array
    {
        $bulanCount = max(1, min(24, $bulanCount));
        $start = now()->subMonths($bulanCount - 1)->startOfMonth();
        $end = now()->endOfMonth();

        $pendapatanRows = Pembayaran::where('status', 'diverifikasi')
            ->whereBetween('verified_at', [$start, $end])
            ->where($pembayaranScope)
            ->get(['verified_at', 'jumlah'])
            ->groupBy(fn ($p) => $p->verified_at->format('m/Y'));

        $tagihanRows = Tagihan::whereBetween('created_at', [$start, $end])
            ->where($tagihanScope)
            ->get(['created_at', 'status', 'jumlah', 'denda'])
            ->groupBy(fn ($t) => $t->created_at->format('m/Y'));

        $labels = [];
        $pendapatan = [];
        $lunas = [];
        $belum = [];

        for ($i = 0; $i < $bulanCount; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('m/Y');
            $labels[] = $month->translatedFormat('M Y');
            $pendapatan[] = (int) round(($pendapatanRows[$key] ?? collect())->sum('jumlah'));

            $rows = $tagihanRows[$key] ?? collect();
            $totalLunas = (int) round($rows->where('status', 'lunas')->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda));
            $totalAll = (int) round($rows->sum(fn ($t) => (float) $t->jumlah + (float) $t->denda));

            $lunas[] = $totalLunas;
            $belum[] = (int) max(0, $totalAll - $totalLunas);
        }

        return compact('labels', 'pendapatan', 'lunas', 'belum');
    }

    /**
     * Pipeline corong: Kunjungan → Penyewa → Tagihan → Lunas.
     * $tagihanScope memfilter Tagihan bila role tidak mencari seluruh properti.
     */
    private function funnelStages(int $kunjungan, int $penyewa, ?callable $tagihanScope = null): array
    {
        $query = Tagihan::query();

        if ($tagihanScope !== null) {
            $query = $query->whereHas('penyewaan.properti', $tagihanScope);
        }

        return [
            ['label' => 'Kunjungan', 'sub' => 'calon penyewa / properti', 'nilai' => $kunjungan],
            ['label' => 'Penyewa', 'sub' => 'penyewaan aktif', 'nilai' => $penyewa],
            ['label' => 'Tagihan', 'sub' => 'tagihan terbit', 'nilai' => (int) $query->count()],
            ['label' => 'Lunas', 'sub' => 'tagihan lunas', 'nilai' => (int) (clone $query)->where('status', 'lunas')->count()],
        ];
    }

    /**
     * Scope properti yang boleh dikelola oleh admin (siswa admin) atau super admin.
     */
    private function kelolaan($user)
    {
        if ($user->is_super_admin) {
            return fn ($query) => $query;
        }

        $id = $user->id;

        return function ($query) use ($id) {
            $query->where(function ($q) use ($id) {
                $q->whereHas('admins', fn ($a) => $a->where('users.id', $id))
                    ->orWhereDoesntHave('admins');
            });
        };
    }

    /**
     * Scope properti untuk verifikasi pembayaran: super admin semua,
     * pemilik propertinya sendiri, admin sesuai tugas kelolaan.
     */
    private function kelolaanAtauPemilik($user)
    {
        if ($user->hasRole('pemilik')) {
            return fn ($query) => $query->where('pemilik_id', $user->id);
        }

        return $this->kelolaan($user);
    }

    private function formatCheckouts($checkouts): array
    {
        return $checkouts->map(function ($s) {
            return [
                'id' => $s->id,
                'anak_kos_nama' => $s->anakKos?->nama ?? '-',
                'kamar_nama' => $s->kamar?->nama,
                'properti_nama' => $s->kamar?->properti?->nama ?? $s->properti?->nama,
                'pemilik_nama' => $s->kamar?->properti?->pemilik?->nama ?? $s->properti?->pemilik?->nama,
                'tanggal_masuk' => $s->tanggal_masuk,
                'tanggal_keluar' => $s->tanggal_keluar,
                'status' => $s->status,
            ];
        })->values()->toArray();
    }

    private function formatProperti(Properti $p): array
    {
        $p->loadMissing(['fotos', 'kamars.fotos']);

        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'kota' => $p->kota,
            'alamat' => $p->alamat,
            'deskripsi' => $p->deskripsi,
            'fasilitas' => $p->fasilitas ? array_values(array_filter(array_map('trim', explode(',', $p->fasilitas)))) : [],
            'aturan' => $p->aturan,
            'denda_per_hari' => $p->denda_per_hari !== null ? (float) $p->denda_per_hari : null,
            'harga' => $p->harga !== null ? (float) $p->harga : null,
            'jenis_harga' => $p->jenis_harga,
            'status' => $p->status,
            'foto' => $p->fotoCover(),
            'fotos' => $p->galeriUrls(),
            'total_kamar' => (int) $p->total_kamar,
            'kamar_terisi' => (int) $p->kamar_terisi,
            'kamars' => $p->kamars->map(fn (Kamar $k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'kapasitas' => (int) $k->kapasitas,
                'harga_sewa_bulanan' => (float) $k->harga_sewa_bulanan,
                'jenis_harga' => $k->jenis_harga,
                'status' => $k->status,
                'foto' => $k->fotoCover(),
                'fotos' => $k->galeriUrls(),
            ])->values(),
        ];
    }
}
