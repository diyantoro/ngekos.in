<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IklanPasarKos;
use App\Models\Kamar;
use App\Models\Properti;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'kota' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:100',
            'harga_min' => 'nullable|numeric|min:0',
            'harga_max' => 'nullable|numeric|min:0',
            'kapasitas' => 'nullable|integer|min:1|max:20',
            'sort' => 'nullable|in:trending,tersedia,termurah,termahal',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Properti::withCount([
            'kamars as total_kamar',
            'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
        ])->withMin(['kamars as harga_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_bulanan', '>', 0)], 'harga_sewa_bulanan')
            ->withMin(['kamars as mingguan_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_mingguan', '>', 0)], 'harga_sewa_mingguan')
            ->withMin(['kamars as harian_termurah' => fn ($q) => $q->where('status', 'tersedia')->where('harga_sewa_harian', '>', 0)], 'harga_sewa_harian')
            ->addSelect(['asli_termurah' => Kamar::select('harga_asli')->whereColumn('properti_id', 'propertis.id')->where('status', 'tersedia')->where('harga_sewa_bulanan', '>', 0)->orderBy('harga_sewa_bulanan')->limit(1)])
            ->with(['fotos:id,properti_id,path,urutan'])
            ->where('status', 'aktif');

        if ($request->filled('kota')) {
            // Cocokkan baik bentuk kanonik maupun teks mentah (mobile bisa
            // mengirim varian bebas: "Kab. Bandung", "kota bandung", ...).
            $kota = trim((string) $request->kota);
            $kanonik = \App\Support\DaftarKota::kanonik($kota) ?? $kota;
            $query->where(function ($q) use ($kota, $kanonik) {
                $q->where('kota_kanonik', $kanonik)
                    ->orWhere('kota', 'like', "%{$kota}%");
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('kota_kanonik', 'like', "%{$search}%");
            });
        }

        if ($request->filled('harga_min')) {
            $query->whereHas('kamars', function ($q) use ($request) {
                $q->where('status', 'tersedia')
                    ->where('harga_sewa_bulanan', '>=', $request->harga_min);
            });
        }

        if ($request->filled('harga_max')) {
            $query->whereHas('kamars', function ($q) use ($request) {
                $q->where('status', 'tersedia')
                    ->where('harga_sewa_bulanan', '<=', $request->harga_max);
            });
        }

        if ($request->filled('kapasitas')) {
            $query->whereHas('kamars', function ($q) use ($request) {
                $q->where('status', 'tersedia')
                    ->where('kapasitas', '>=', $request->kapasitas);
            });
        }

        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'trending') {
                $query->orderByRaw('(ifnull(total_kamar, 0) - ifnull(kamar_tersedia, 0)) desc');
            } elseif ($sort === 'tersedia') {
                $query->orderByRaw('kamar_tersedia desc');
            } elseif ($sort === 'termurah') {
                // Harga resmi dari kamar termurah.
                $query->orderByRaw('harga_termurah asc');
            } elseif ($sort === 'termahal') {
                $query->orderByRaw('harga_termurah desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);
        $propertis = $query->paginate($perPage);

        $propertis->getCollection()->transform(fn (Properti $p) => $this->formatRingkas($p));

        return response()->json($propertis);
    }

    public function show(Request $request, int $properti): JsonResponse
    {
        $properti = Properti::where('id', $properti)
            ->where('status', 'aktif')
            ->with(['pemilik:id,nama'])
            ->firstOrFail();

        $properti->loadCount([
            'kamars as total_kamar',
            'kamars as kamar_tersedia' => fn ($q) => $q->where('status', 'tersedia'),
        ]);

        $properti->load([
            'kamars' => fn ($q) => $q->orderBy('nama'),
            'pemilik:id,nama,no_hp',
        ]);

        return response()->json($this->formatDetail($properti));
    }

    /**
     * Daftar kota baku untuk autocomplete (filter ?q=).
     */
    public function daftarKota(Request $request): JsonResponse
    {
        $request->validate(['q' => 'nullable|string|max:50']);

        $q = trim((string) $request->input('q', ''));

        $kota = $q !== ''
            ? \App\Support\DaftarKota::saran($q, 30)
            : \App\Support\DaftarKota::semua();

        return response()->json(['kota' => array_values($kota)]);
    }

    /**
     * Daftar fasilitas per scope (?scope=kos|kamar|semua).
     */
    public function daftarFasilitas(Request $request): JsonResponse
    {
        $request->validate(['scope' => 'nullable|in:kos,kamar,semua']);

        $scope = $request->input('scope', 'semua');

        return response()->json([
            'scope' => $scope,
            'fasilitas' => \App\Support\FacilityHelper::untukScope($scope),
            'kos' => \App\Support\FacilityHelper::FASILITAS_KOS,
            'kamar' => \App\Support\FacilityHelper::FASILITAS_KAMAR,
        ]);
    }

    /**
     * Bentuk ringkas untuk daftar katalog.
     */
    private function formatRingkas(Properti $p): array
    {
        $p->loadMissing('fotos');

        // Harga tampil = kamar termurah (bulanan wajib, mingguan/harian min kamar).
        $hargaTampil = $p->harga_termurah ?? null;
        $asliTampil = ($p->asli_termurah ?? null) && $hargaTampil && $p->asli_termurah > $hargaTampil
            ? (float) $p->asli_termurah : null;

        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'kota' => $p->kota,
            'latitude' => $p->latitude !== null ? (float) $p->latitude : null,
            'longitude' => $p->longitude !== null ? (float) $p->longitude : null,
            'alamat' => $p->alamat,
            'fasilitas' => $this->fasilitasArray($p->fasilitas),
            'foto' => $p->fotoCover(),
            'fotos' => $p->galeriUrls(),
            'harga' => $hargaTampil !== null ? (float) $hargaTampil : null,
            'harga_termurah' => $hargaTampil !== null ? (float) $hargaTampil : null,
            'harga_mingguan' => $p->mingguan_termurah !== null ? (float) $p->mingguan_termurah : null,
            'harga_harian' => $p->harian_termurah !== null ? (float) $p->harian_termurah : null,
            'harga_asli' => $asliTampil,
            'jenis_harga' => 'bulanan',
            'status' => $p->status?->value,
            'total_kamar' => (int) $p->total_kamar,
            'kamar_tersedia' => (int) $p->kamar_tersedia,
        ];
    }

    /**
     * Bentuk lengkap untuk halaman detail.
     */
    private function formatDetail(Properti $p): array
    {
        $p->loadMissing(['fotos', 'kamars.fotos']);

        // Rekening utuh hanya untuk user login (butuh transfer saat booking);
        // pengunjung anonim hanya dapat versi tersamar (anti-scraping,
        // sama perlakuannya seperti nomor HP pemilik di bawah).
        $bolehLihatPenuh = auth('sanctum')->check();

        $tersedia = $p->kamars->where('status', \App\Enums\KamarStatus::Tersedia);
        $termurah = $tersedia->filter(fn ($k) => (float) $k->harga_sewa_bulanan > 0)->min('harga_sewa_bulanan');
        $kamarTermurah = $tersedia->filter(fn ($k) => (float) $k->harga_sewa_bulanan > 0)->sortBy('harga_sewa_bulanan')->first();
        $asliTermurah = $kamarTermurah?->harga_asli;
        $asliTampil = $asliTermurah && $termurah && $asliTermurah > $termurah ? (float) $asliTermurah : null;

        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'kota' => $p->kota,
            'latitude' => $p->latitude !== null ? (float) $p->latitude : null,
            'longitude' => $p->longitude !== null ? (float) $p->longitude : null,
            'alamat' => $p->alamat,
            'deskripsi' => $p->deskripsi,
            'fasilitas' => $this->fasilitasArray($p->fasilitas),
            'aturan' => $p->aturan,
            'denda_per_hari' => $p->denda_per_hari !== null ? (float) $p->denda_per_hari : null,
            // Rekening tujuan transfer DP (wajib diisi pemilik agar booking bulanan bisa jalan).
            // Anonim: tersamar; login: utuh (dibutuhkan saat transfer DP).
            'bank_nama' => $p->bank_nama,
            'no_rekening' => $bolehLihatPenuh ? $p->no_rekening : $p->noRekeningTersamar(),
            'rekening_nama' => $bolehLihatPenuh ? $p->rekening_nama : $p->namaRekeningTersamar(),
            'rekening_tersamar' => $p->labelRekeningTersamar(),
            'punya_rekening' => $p->punyaRekening(),
            'dp_nominal' => \App\Services\PenyewaanService::DP_NOMINAL,
            'harga' => $termurah !== null ? (float) $termurah : null,
            'harga_termurah' => $termurah !== null ? (float) $termurah : null,
            'harga_mingguan' => ($m = $tersedia->filter(fn ($k) => (float) $k->harga_sewa_mingguan > 0)->min('harga_sewa_mingguan')) !== null ? (float) $m : null,
            'harga_harian' => ($h = $tersedia->filter(fn ($k) => (float) $k->harga_sewa_harian > 0)->min('harga_sewa_harian')) !== null ? (float) $h : null,
            'harga_asli' => $asliTampil,
            'jenis_harga' => 'bulanan',
            'status' => $p->status?->value,
            'foto' => $p->fotoCover(),
            'fotos' => $p->galeriUrls(),
            'total_kamar' => (int) $p->total_kamar,
            'kamar_tersedia' => (int) $p->kamar_tersedia,
            'pemilik' => $p->pemilik ? [
                'id' => $p->pemilik->id,
                'nama' => $p->pemilik->nama,
                // Nomor HP disamarkan di katalog publik (anti-scraping); lengkap via chat.
                'no_hp' => $this->samarkanNoHp($p->pemilik->no_hp),
            ] : null,
            'kamars' => $p->kamars->map(fn (Kamar $k) => [
                'id' => $k->id,
                'nama' => $k->nama,
                'kapasitas' => (int) $k->kapasitas,
                'harga_sewa_bulanan' => (float) $k->harga_sewa_bulanan,
                'harga_sewa_mingguan' => $k->harga_sewa_mingguan !== null ? (float) $k->harga_sewa_mingguan : null,
                'harga_sewa_harian' => $k->harga_sewa_harian !== null ? (float) $k->harga_sewa_harian : null,
                'harga_asli' => $k->harga_asli !== null ? (float) $k->harga_asli : null,
                'jenis_harga' => 'bulanan',
                'fasilitas' => $this->fasilitasArray($k->fasilitas),
                'status' => $k->status?->value,
                'foto' => $k->fotoCover(),
                'fotos' => $k->galeriUrls(),
            ])->values(),
        ];
    }

    /**
     * Samarkan nomor HP untuk katalog publik: 0812****7890.
     */
    private function samarkanNoHp(?string $noHp): ?string
    {
        if (blank($noHp) || strlen($noHp) < 7) {
            return $noHp;
        }

        return substr($noHp, 0, 4).str_repeat('*', max(strlen($noHp) - 8, 2)).substr($noHp, -4);
    }

    private function fasilitasArray(?string $fasilitas): array
    {
        if (blank($fasilitas)) {
            return [];
        }

        return \App\Support\FacilityHelper::normalizeList(
            \App\Support\FacilityHelper::fromString($fasilitas)
        );
    }

    /**
     * Daftar iklan aktif yang bisa ditayangkan di katalog (untuk super admin).
     * Admin mengatur akun_jumlah berapa yang ditampilkan.
     */
    public function iklanTayangan(Request $request): JsonResponse
    {
        $request->validate([
            'akun_jumlah' => 'nullable|integer|min:1|max:10',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $akunFilter = $request->input('akun_jumlah');
        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);

        $query = IklanPasarKos::with(['barangKos.user', 'barangKos.properti'])
            ->aktif()
            ->where('status', true);

        if ($akunFilter) {
            $query->where('akun_jumlah', '>=', $akunFilter);
        }

        $iklans = $query->orderBy('tayang_sampai', 'desc')->paginate($perPage);

        return response()->json([
            'iklans' => $iklans->getCollection()->map(fn ($iklan) => $this->formatIklanTayangan($iklan)),
            'total' => $iklans->total(),
            'per_page' => $iklans->perPage(),
            'current_page' => $iklans->currentPage(),
            'akun_filter' => $akunFilter,
        ]);
    }

    /**
     * Daftar semua iklan yang sedang/dulu pernah tayang (untuk super admin).
     * Admin bisa melihat statistik dan mengelola tayangan.
     */
    public function riwayatIklanTayangan(Request $request): JsonResponse
    {
        $request->validate([
            'akun_jumlah' => 'nullable|integer|min:1|max:10',
            'status_iklan' => 'nullable|in:aktif,berakhir',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $akunFilter = $request->input('akun_jumlah');
        $statusFilter = $request->input('status_iklan');
        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);

        $query = IklanPasarKos::with(['barangKos.user', 'barangKos.properti']);

        if ($akunFilter) {
            $query->where('akun_jumlah', '>=', $akunFilter);
        }

        if ($statusFilter) {
            if ($statusFilter === 'aktif') {
                $query->aktif();
            } else {
                // Berakhir: status true tapi sudah lewat tayang_sampai
                $query->where('status', true)
                    ->where('tayang_sampai', '<', Carbon::now());
            }
        }

        $iklans = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'iklans' => $iklans->getCollection()->map(fn ($iklan) => $this->formatIklanTayangan($iklan)),
            'total' => $iklans->total(),
            'per_page' => $iklans->perPage(),
            'current_page' => $iklans->currentPage(),
            'akun_filter' => $akunFilter,
            'status_filter' => $statusFilter,
        ]);
    }

    /**
     * Set akun_jumlah minimum untuk tayangan katalog (super admin).
     * Ini mengatur berapa akun minimum yang akan muncul di katalog.
     */
    public function setAkunTayangan(Request $request): JsonResponse
    {
        $request->validate([
            'akun_jumlah' => 'required|integer|min:1|max:10',
        ]);

        $akunJumlah = $request->input('akun_jumlah');
        
        // Simpan ke pengaturan
        \App\Models\Pengaturan::where('key', 'akun_di_katalog_pasar_kos')->update([
            'value' => (string) $akunJumlah,
        ]);

        return response()->json([
            'message' => 'Akun tayangan katalog berhasil diset',
            'akun_jumlah' => $akunJumlah,
        ]);
    }

    /**
     * Format iklan untuk tayangan katalog.
     */
    private function formatIklanTayangan(IklanPasarKos $iklan): array
    {
        return [
            'id' => $iklan->id,
            'barang_kos' => [
                'id' => $iklan->barangKos->id,
                'nama' => $iklan->barangKos->nama,
                'harga' => $iklan->barangKos->harga,
                'foto' => $iklan->barangKos->fotoUrl(),
                'kota' => $iklan->barangKos->kota,
                'status' => $iklan->barangKos->status,
                'properti' => $iklan->barangKos->properti ? [
                    'id' => $iklan->barangKos->properti->id,
                    'nama' => $iklan->barangKos->properti->nama,
                ] : null,
            ],
            'user' => [
                'id' => $iklan->user->id,
                'nama' => $iklan->user->nama,
                'email' => $iklan->user->email,
            ],
            'akun_jumlah' => $iklan->akun_jumlah,
            'tayang_dimulai' => $iklan->tayang_dimulai ? $iklan->tayang_dimulai->format('Y-m-d H:i:s') : null,
            'tayang_sampai' => $iklan->tayang_sampai ? $iklan->tayang_sampai->format('Y-m-d H:i:s') : null,
            'durasi_hari' => $iklan->durasi_hari,
            'sisa_hari' => $iklan->sisaHari(),
            'biaya' => $iklan->biaya,
            'biaya_formatted' => 'Rp '.number_format($iklan->biaya, 0, ',', '.'),
            'aktif' => $iklan->isAktif(),
            'created_at' => $iklan->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
