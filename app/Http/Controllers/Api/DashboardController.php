<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penyewaan;
use App\Models\Properti;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function anakKos(Request $request): JsonResponse
    {
        $id = $request->user()->id;

        $penyewaanAktif = Penyewaan::where('anak_kos_id', $id)->where('status', 'aktif')->count();

        $tagihanBelumBayar = Tagihan::whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $id))
            ->where('status', 'belum_bayar')
            ->count();

        $totalDibayar = (int) Pembayaran::where('anak_kos_id', $id)
            ->where('status', 'diverifikasi')
            ->sum('jumlah');

        return response()->json([
            'penyewaan_aktif' => $penyewaanAktif,
            'tagihan_belum_bayar' => $tagihanBelumBayar,
            'total_dibayar' => $totalDibayar,
        ]);
    }

    public function anakKosPenyewaan(Request $request): JsonResponse
    {
        $sewaans = Penyewaan::with(['kamar:id,nama,harga_sewa_bulanan', 'properti:id,nama,foto'])
            ->where('anak_kos_id', $request->user()->id)
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
            ]);

        return response()->json($sewaans);
    }

    public function anakKosTagihan(Request $request): JsonResponse
    {
        $tagihans = Tagihan::with(['penyewaan.kamar:id,nama', 'penyewaan.properti:id,nama'])
            ->whereHas('penyewaan', fn ($q) => $q->where('anak_kos_id', $request->user()->id))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'periode' => $t->periode,
                'kamar' => $t->penyewaan?->kamar?->nama,
                'properti' => $t->penyewaan?->properti?->nama,
                'jumlah' => (float) $t->jumlah,
                'denda' => (float) $t->denda,
                'jatuh_tempo' => $t->jatuh_tempo,
                'status' => $t->status,
            ]);

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
            ]);

        return response()->json($pembayarans);
    }

    public function anakKosBayar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'metode' => 'required|string|max:50',
            'jumlah' => 'required|numeric|min:1',
            'bukti' => 'nullable|image|max:2048',
        ]);

        $tagihan = Tagihan::with('penyewaan')->findOrFail($validated['tagihan_id']);

        if ($tagihan->penyewaan->anak_kos_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($tagihan->status === 'lunas') {
            return response()->json(['message' => 'Tagihan sudah lunas.'], 422);
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti', 'public');
        }

        Pembayaran::create([
            'tagihan_id' => $validated['tagihan_id'],
            'anak_kos_id' => $request->user()->id,
            'metode' => $validated['metode'],
            'jumlah' => $validated['jumlah'],
            'bukti' => $buktiPath,
            'status' => 'menunggu_verifikasi',
        ]);

        return response()->json(['message' => 'Pembayaran berhasil diajukan.'], 201);
    }

    public function anakKosAjukanKeluar(Request $request, int $sewaanId): JsonResponse
    {
        $sewaan = Penyewaan::where('id', $sewaanId)
            ->where('anak_kos_id', $request->user()->id)
            ->where('status', 'aktif')
            ->with(['kamar'])
            ->first();

        if (! $sewaan) {
            return response()->json(['message' => 'Penyewaan tidak ditemukan.'], 404);
        }

        if ($sewaan->permintaan_keluar_pada) {
            return response()->json(['message' => 'Pengajuan check-out sudah pernah dikirim.'], 422);
        }

        DB::transaction(function () use ($sewaan) {
            $sewaan->update([
                'permintaan_keluar_pada' => now(),
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'selesai',
            ]);
            optional($sewaan->kamar)->update(['status' => 'tersedia']);
        });

        return response()->json(['message' => 'Check-out berhasil. Kamar kembali tersedia.']);
    }

    public function pemilik(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $totalProperti = Properti::where('pemilik_id', $userId)->count();

        $totalKamar = Properti::where('pemilik_id', $userId)
            ->sum(DB::raw('(SELECT COUNT(*) FROM kamars WHERE kamars.properti_id = propertis.id)')) ?? 0;

        $kamarTerisi = Properti::where('pemilik_id', $userId)
            ->sum(DB::raw('(SELECT COUNT(*) FROM kamars WHERE kamars.properti_id = propertis.id AND kamars.status = \'terisi\')')) ?? 0;

        $pendapatanBulanIni = (int) Pembayaran::where('status', 'diverifikasi')
            ->whereMonth('verified_at', now()->month)
            ->whereYear('verified_at', now()->year)
            ->whereHas('tagihan.penyewaan.properti', fn ($q) => $q->where('pemilik_id', $userId))
            ->sum('jumlah');

        $penyewaanAktif = Penyewaan::where('status', 'aktif')
            ->whereHas('properti', fn ($q) => $q->where('pemilik_id', $userId))
            ->count();

        $scope = fn ($q) => $q->whereHas('tagihan.penyewaan.properti', fn ($x) => $x->where('pemilik_id', $userId));

        $chart = $this->monthlyChart(
            $scope,
            fn ($q) => $q->whereHas('penyewaan.properti', fn ($x) => $x->where('pemilik_id', $userId)),
        );

        return response()->json([
            'total_properti' => $totalProperti,
            'total_kamar' => $totalKamar,
            'kamar_terisi' => $kamarTerisi,
            'pendapatan_bulan_ini' => $pendapatanBulanIni,
            'penyewaan_aktif' => $penyewaanAktif,
            'chart' => $chart,
        ]);
    }

    public function pemilikSewaans(Request $request): JsonResponse
    {
        $sewaans = Penyewaan::whereHas('properti', fn ($q) => $q->where('pemilik_id', $request->user()->id))
            ->with(['anakKos:id,nama', 'kamar:id,nama', 'kamar.properti:id,nama', 'tagihans'])
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
                    'sisa_tagihan' => $sisa,
                    'tagihan_belum_bayar' => $belumLunas->count(),
                    'telat' => $telat,
                ];
            })
            ->values();

        return response()->json($sewaans);
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

        DB::transaction(function () use ($sewaan) {
            $sewaan->update([
                'tanggal_keluar' => now()->toDateString(),
                'status' => 'selesai',
            ]);
            optional($sewaan->kamar)->update(['status' => 'tersedia']);
        });

        return response()->json(['message' => "Check-out $nama dari kamar $kamarNama berhasil. Kamar kembali tersedia."]);
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
        $userId = $request->user()->id;
        $bulan = $request->input('bulan');

        try {
            $periodeMulai = $bulan
                ? \Carbon\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()
                : now()->startOfMonth();
            $periodeAkhir = (clone $periodeMulai)->endOfMonth();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Format bulan tidak valid (contoh: 2026-03).'], 422);
        }

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

        $pendapatan = (int) $transaksi->sum(fn ($t) => (float) $t->jumlah);

        return response()->json([
            'periode' => $periodeMulai->translatedFormat('F Y'),
            'bulan' => $periodeMulai->format('Y-m'),
            'ringkasan' => [
                'total_properti' => $propertis->count(),
                'total_kamar' => (int) $propertis->sum('total_kamar'),
                'kamar_terisi' => (int) $propertis->sum('kamar_terisi'),
                'penyewaan_aktif' => $sewaans->where('status', 'aktif')->count(),
                'pendapatan' => $pendapatan,
                'jumlah_transaksi' => $transaksi->count(),
            ],
            'propertis' => $propertis->map(fn (Properti $p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'alamat' => $p->alamat,
                'status' => $p->status,
                'total_kamar' => (int) $p->total_kamar,
                'kamar_terisi' => (int) $p->kamar_terisi,
            ])->values(),
            'sewaans' => $sewaans->map(fn ($s) => [
                'anak_kos_nama' => $s->anakKos?->nama ?? '-',
                'kamar_nama' => $s->kamar?->nama,
                'properti_nama' => $s->kamar?->properti?->nama ?? $s->properti?->nama,
                'tanggal_masuk' => $s->tanggal_masuk,
                'tanggal_keluar' => $s->tanggal_keluar,
                'status' => $s->status,
            ])->values(),
            'transaksi' => $transaksi->map(fn (Pembayaran $p) => [
                'anak_kos_nama' => $p->anakKos?->nama ?? '-',
                'periode' => $p->tagihan?->periode,
                'metode' => $p->metode,
                'jumlah' => (float) $p->jumlah,
                'status' => $p->status,
                'verified_at' => $p->verified_at?->toDateTimeString(),
            ])->values(),
        ]);
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

        return response()->json([
            'total_tugas' => Properti::whereHas('admins', fn ($q) => $q->where('users.id', $user->id))->count(),
            'pembayaran_menunggu' => Pembayaran::where('status', 'menunggu_verifikasi')
                ->whereHas('tagihan.penyewaan.properti', $kelolaan)
                ->count(),
            'penyewaan_aktif' => Penyewaan::where('status', 'aktif')
                ->whereHas('properti', $kelolaan)
                ->count(),
            'chart' => $this->monthlyChart(
                fn ($q) => $q->whereHas('tagihan.penyewaan.properti', $kelolaan),
                fn ($q) => $q->whereHas('penyewaan.properti', $kelolaan),
            ),
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

        return response()->json([
            'total_user' => User::count(),
            'total_properti' => Properti::count(),
            'total_kamar' => Kamar::count(),
            'kamar_terisi' => Kamar::where('status', 'terisi')->count(),
            'penyewaan_aktif' => Penyewaan::where('status', 'aktif')->count(),
            'pendapatan' => (int) Pembayaran::where('status', 'diverifikasi')->sum('jumlah'),
            'chart' => $this->monthlyChart(
                fn ($q) => $q->where('status', 'diverifikasi'),
                fn ($q) => $q,
            ),
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
            ->when(! $request->user()->hasRole('super_admin'), fn ($q) => $q->whereHas('tagihan.penyewaan.properti', $this->kelolaan($request->user())))
            ->with('anakKos', 'tagihan')
            ->first();

        if (! $pembayaran) {
            return response()->json(['message' => 'Pembayaran tidak ditemukan atau sudah diproses.'], 404);
        }

        $pembayaran->update([
            'status' => $status,
            'diverifikasi_oleh' => $request->user()->id,
            'verified_at' => now(),
        ]);

        if ($status === 'diverifikasi') {
            $tagihan = $pembayaran->tagihan;
            $total = $tagihan->pembayarans()->where('status', 'diverifikasi')->sum('jumlah');

            if ($total >= $tagihan->jumlah + $tagihan->denda) {
                $tagihan->update(['status' => 'lunas']);
            }

            $nama = $pembayaran->anakKos?->nama ?? '-';
            $pesan = "Pembayaran $nama sebesar Rp".number_format($pembayaran->jumlah, 0, ',', '.').' diverifikasi.';
        } else {
            $pesan = 'Pengajuan pembayaran ditolak.';
        }

        return response()->json(['message' => $pesan]);
    }

    /**
     * Builds the last 6 months of chart data (labels, revenue, paid/unpaid bills)
     * for a given role scope. $pembayaranScope filters Pembayaran, $tagihanScope filters Tagihan.
     */
    private function monthlyChart(callable $pembayaranScope, callable $tagihanScope): array
    {
        $labels = [];
        $pendapatan = [];
        $lunas = [];
        $belum = [];

        $start = now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('m/Y');
            $labels[] = $month->translatedFormat('M Y');
            $pendapatan[] = (int) Pembayaran::where('status', 'diverifikasi')
                ->whereMonth('verified_at', $month->month)
                ->whereYear('verified_at', $month->year)
                ->where($pembayaranScope)
                ->sum('jumlah');

            $agtihan = Tagihan::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->where($tagihanScope);
            $totalLunas = (clone $agtihan)->where('status', 'lunas')->sum(DB::raw('jumlah + denda'));
            $totalAll = (clone $agtihan)->sum(DB::raw('jumlah + denda'));

            $lunas[] = (int) $totalLunas;
            $belum[] = (int) max(0, $totalAll - $totalLunas);
        }

        return compact('labels', 'pendapatan', 'lunas', 'belum');
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
    {        return [
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
            'foto' => $p->foto ? '/storage/'.$p->foto : null,
            'total_kamar' => (int) $p->total_kamar,
            'kamar_terisi' => (int) $p->kamar_terisi,
            'kamars' => $p->kamars->map(fn (Kamar $k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'kapasitas' => (int) $k->kapasitas,
                'harga_sewa_bulanan' => (float) $k->harga_sewa_bulanan,
                'jenis_harga' => $k->jenis_harga,
                'status' => $k->status,
                'foto' => $k->foto ? '/storage/'.$k->foto : null,
            ])->values(),
        ];
    }
}
