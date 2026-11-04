<?php

use App\Enums\UserRole;
use App\Models\BarangKos;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $cariBarang = '';

    public string $cariAkun = '';

    public array $barangIds = [];

    public array $akunIds = [];

    public ?string $pesan = null;

    public function updatedBarangIds(): void
    {
        $this->pesan = null;
    }

    public function updatedAkunIds(): void
    {
        $this->pesan = null;
    }

    private function barangQuery(): Builder
    {
        $kata = trim($this->cariBarang);

        return BarangKos::query()
            ->with(['user:id,nama,role', 'akunTampil:id,nama,role'])
            ->withCount('akunTampil')
            ->where('status', '!=', 'terjual')
            ->when($kata !== '', function (Builder $query) use ($kata) {
                $query->where(function (Builder $sub) use ($kata) {
                    $sub->where('nama', 'like', "%{$kata}%")
                        ->orWhere('kota', 'like', "%{$kata}%")
                        ->orWhereHas('user', fn (Builder $u) => $u->where('nama', 'like', "%{$kata}%"));
                });
            })
            ->latest('id');
    }

    /**
     * Kota dari barang yang sedang dicentang. Dipakai untuk mendahulukan
     * akun yang berada di kota yang sama.
     *
     * @return array<int, string>
     */
    private function kotaBarangTerpilih(): array
    {
        if ($this->barangIds === []) {
            return [];
        }

        return BarangKos::query()
            ->whereIn('id', array_map('intval', $this->barangIds))
            ->pluck('kota')
            ->map(fn ($kota) => trim((string) $kota))
            ->filter()
            ->unique(fn ($kota) => mb_strtolower($kota))
            ->values()
            ->all();
    }

    private function akunQuery(): Builder
    {
        $kata = trim($this->cariAkun);
        $kotaPrioritas = $this->kotaBarangTerpilih();

        $query = User::query()
            ->whereIn('role', [UserRole::AnakKos->value, UserRole::Pemilik->value])
            ->withCount(['barangKos as barang_aktif' => fn (Builder $q) => $q->where('status', '!=', 'terjual')])
            ->withCount('barangKosDitampilkan as jumlah_tampil')
            ->when($kata !== '', function (Builder $query) use ($kata) {
                $query->where(function (Builder $sub) use ($kata) {
                    $sub->where('nama', 'like', "%{$kata}%")
                        ->orWhere('email', 'like', "%{$kata}%")
                        ->orWhere('no_hp', 'like', "%{$kata}%");
                });
            });

        // Kota akun: properti milik pemilik, properti yang disewa anak kos,
        // atau kota barang yang pernah dipasang akun tersebut.
        $query->addSelect('users.*')
            ->selectRaw(
                "COALESCE(
                    (SELECT p.kota FROM propertis p
                        WHERE p.pemilik_id = users.id AND p.deleted_at IS NULL
                            AND p.kota IS NOT NULL AND p.kota <> ''
                        ORDER BY p.id LIMIT 1),
                    (SELECT p2.kota FROM penyewaans py
                        INNER JOIN propertis p2 ON p2.id = py.properti_id AND p2.deleted_at IS NULL
                        WHERE py.anak_kos_id = users.id
                            AND p2.kota IS NOT NULL AND p2.kota <> ''
                        ORDER BY py.id DESC LIMIT 1),
                    (SELECT b.kota FROM barang_kos b
                        WHERE b.user_id = users.id
                            AND b.kota IS NOT NULL AND b.kota <> ''
                        ORDER BY b.id DESC LIMIT 1)
                ) AS kota_akun"
            );

        if ($kotaPrioritas !== []) {
            $kota = array_map(fn ($kota) => mb_strtolower($kota), $kotaPrioritas);
            $placeholder = implode(', ', array_fill(0, count($kota), '?'));

            $cocokKota = "
                EXISTS (SELECT 1 FROM propertis p
                    WHERE p.pemilik_id = users.id AND p.deleted_at IS NULL
                        AND LOWER(TRIM(p.kota)) IN ({$placeholder}))
                OR EXISTS (SELECT 1 FROM penyewaans py
                    INNER JOIN propertis p2 ON p2.id = py.properti_id AND p2.deleted_at IS NULL
                    WHERE py.anak_kos_id = users.id
                        AND LOWER(TRIM(p2.kota)) IN ({$placeholder}))
                OR EXISTS (SELECT 1 FROM barang_kos b
                    WHERE b.user_id = users.id
                        AND LOWER(TRIM(b.kota)) IN ({$placeholder}))
            ";

            $query->selectRaw(
                "CASE WHEN {$cocokKota} THEN 1 ELSE 0 END AS kota_sama",
                array_merge($kota, $kota, $kota)
            )->orderByDesc('kota_sama');
        }

        return $query->orderBy('nama');
    }

    private function validasiBarang(): void
    {
        $this->validate([
            'barangIds' => 'required|array|min:1',
            'barangIds.*' => 'integer|exists:barang_kos,id',
        ], [
            'barangIds.required' => 'Pilih minimal satu barang.',
            'barangIds.min' => 'Pilih minimal satu barang.',
        ]);
    }

    /**
     * @return array<int>
     */
    private function akunTerpilih(): array
    {
        $this->validate([
            'akunIds' => 'required|array|min:1',
            'akunIds.*' => 'integer|exists:users,id',
        ], [
            'akunIds.required' => 'Pilih minimal satu akun.',
            'akunIds.min' => 'Pilih minimal satu akun.',
        ]);

        return User::query()
            ->whereIn('id', array_map('intval', $this->akunIds))
            ->whereIn('role', [UserRole::AnakKos->value, UserRole::Pemilik->value])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, BarangKos>
     */
    private function barangTerpilih()
    {
        return BarangKos::query()->whereIn('id', array_map('intval', $this->barangIds))->get();
    }

    public function tampilkanDiAkun(): void
    {
        $this->validasiBarang();
        $akun = $this->akunTerpilih();
        $barangs = $this->barangTerpilih();

        foreach ($barangs as $barang) {
            $barang->akunTampil()->syncWithoutDetaching($akun);
        }

        $this->pesan = $barangs->count().' barang sekarang tampil di '.count($akun).' akun terpilih (akun lain tetap dipertahankan).';
    }

    public function sembunyikanBarang(): void
    {
        $this->validasiBarang();
        $barangs = $this->barangTerpilih();

        foreach ($barangs as $barang) {
            $barang->akunTampil()->detach();
        }

        $this->pesan = $barangs->count().' barang disembunyikan dari semua toko dan katalog Pasar Kos.';
    }

    public function hapusAkun(int $barangId, int $userId): void
    {
        $barang = BarangKos::query()->findOrFail($barangId);
        $barang->akunTampil()->detach($userId);

        $this->pesan = 'Akun dihapus dari barang "'.$barang->nama.'".';
    }

    public function pilihSemuaBarang(): void
    {
        $this->barangIds = $this->barangQuery()
            ->limit(100)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function kosongkanBarang(): void
    {
        $this->barangIds = [];
    }

    public function pilihSemuaAkun(): void
    {
        $this->akunIds = $this->akunQuery()
            ->limit(100)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function kosongkanAkun(): void
    {
        $this->akunIds = [];
    }

    public function with(): array
    {
        $semuaBarang = $this->barangQuery()->limit(100)->get();

        return [
            'barangs' => $semuaBarang,
            'akuns' => $this->akunQuery()->limit(100)->get(),
            'ringkasan' => $semuaBarang->filter(fn (BarangKos $barang) => $barang->akunTampil->isNotEmpty())->values(),
            'kotaPrioritas' => $this->kotaBarangTerpilih(),
            'jumlahBarangTerpilih' => count($this->barangIds),
            'jumlahAkunTerpilih' => count($this->akunIds),
            'totalBarang' => BarangKos::query()->where('status', '!=', 'terjual')->count(),
            'totalAkun' => User::query()->whereIn('role', [UserRole::AnakKos->value, UserRole::Pemilik->value])->count(),
            'totalTampil' => BarangKos::query()->tersedia()->tampilDiKatalog()->count(),
        ];
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke dashboard
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Kelola Pasar Kos</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Centang barang yang dijual anak kos/pemilik, lalu centang akun tujuan tempat barang itu ditampilkan.
                Barang tetap muncul sekali di katalog utama dengan penjual aslinya.
            </p>
        </div>

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl bg-white dark:bg-gray-800 p-4 ring-1 ring-gray-100 dark:ring-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Barang belum terjual</p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $totalBarang }}</p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-gray-800 p-4 ring-1 ring-gray-100 dark:ring-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Akun anak kos & pemilik</p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ $totalAkun }}</p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-gray-800 p-4 ring-1 ring-gray-100 dark:ring-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Barang tampil di katalog</p>
                <p class="mt-1 text-2xl font-extrabold text-teal-600 dark:text-teal-400">{{ $totalTampil }}</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Panel 1: Barang --}}
            <div class="rounded-2xl bg-white dark:bg-gray-800 p-4 sm:p-5 ring-1 ring-gray-100 dark:ring-gray-700 space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">1. Pilih barang</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Barang yang dicentang akan diatur akun tampilnya.</p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="cariBarang"
                               placeholder="Cari nama barang, kota, atau penjual..."
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 pl-9 pr-3 py-2 text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $jumlahBarangTerpilih }} dipilih</span>
                        <button type="button" wire:click="pilihSemuaBarang"
                                class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-[11px] font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">
                            Pilih tampil
                        </button>
                        <button type="button" wire:click="kosongkanBarang"
                                class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-[11px] font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">
                            Kosongkan
                        </button>
                    </div>
                </div>

                @error('barangIds') <p class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror

                <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                    @forelse ($barangs as $barang)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60 px-3 py-2.5 transition hover:border-teal-300 dark:hover:border-teal-600">
                            <input type="checkbox" wire:model.live="barangIds" value="{{ $barang->id }}"
                                   class="h-4 w-4 rounded text-teal-600">
                            <img src="{{ $barang->fotoUrl() }}" alt="{{ $barang->nama }}"
                                 class="h-11 w-11 shrink-0 rounded-lg object-cover ring-1 ring-black/5">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $barang->nama }}</span>
                                <span class="block truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $barang->formattedHarga() }} • {{ $barang->user?->nama ?? 'Penjual' }}
                                </span>
                            </span>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $barang->akun_tampil_count > 0 ? 'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' }}">
                                {{ $barang->akun_tampil_count }} toko
                            </span>
                        </label>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Tidak ada barang yang cocok dengan pencarian.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Panel 2: Akun tujuan --}}
            <div class="rounded-2xl bg-white dark:bg-gray-800 p-4 sm:p-5 ring-1 ring-gray-100 dark:ring-gray-700 space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">2. Pilih akun tujuan</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Akun-akun ini akan menampilkan barang yang dipilih di toko mereka.</p>
                    @if (! empty($kotaPrioritas))
                        <p class="mt-2 inline-flex items-start gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <span>Didahulukan: akun di kota <strong>{{ implode(', ', $kotaPrioritas) }}</strong> sesuai barang terpilih.</span>
                        </p>
                    @endif
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="cariAkun"
                               placeholder="Cari nama, email, atau nomor HP akun..."
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 pl-9 pr-3 py-2 text-sm text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $jumlahAkunTerpilih }} dipilih</span>
                        <button type="button" wire:click="pilihSemuaAkun"
                                class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-[11px] font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">
                            Pilih semua
                        </button>
                        <button type="button" wire:click="kosongkanAkun"
                                class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-[11px] font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">
                            Kosongkan
                        </button>
                    </div>
                </div>

                @error('akunIds') <p class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror

                <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                    @forelse ($akuns as $akun)
                        @php($kotaSama = (int) ($akun->kota_sama ?? 0) === 1)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 transition hover:border-teal-300 dark:hover:border-teal-600 {{ $kotaSama ? 'border-amber-300 bg-amber-50/70 ring-1 ring-amber-200 dark:border-amber-500/40 dark:bg-amber-500/5 dark:ring-amber-500/30' : 'border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60' }}">
                            <input type="checkbox" wire:model.live="akunIds" value="{{ $akun->id }}"
                                   class="h-4 w-4 rounded text-teal-600">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 font-bold text-xs">
                                {{ strtoupper(substr($akun->nama ?? 'U', 0, 1)) }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $akun->nama }}</span>
                                <span class="block truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $akun->roleValue() === 'pemilik' ? 'Pemilik Kos' : 'Anak Kos' }}
                                    • {{ $akun->barang_aktif }} barang aktif
                                    @if ($akun->kota_akun)
                                        • {{ $akun->kota_akun }}
                                    @endif
                                </span>
                            </span>
                            <span class="flex shrink-0 flex-wrap items-center justify-end gap-1">
                                @if ($kotaSama)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                                        Kota sama
                                    </span>
                                @endif
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $akun->jumlah_tampil }} tampil
                                </span>
                            </span>
                        </label>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Tidak ada akun yang cocok dengan pencarian.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-800 p-4 sm:p-5 ring-1 ring-gray-100 dark:ring-gray-700 space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <strong class="text-gray-700 dark:text-gray-200">{{ $jumlahBarangTerpilih }}</strong> barang dipilih,
                    <strong class="text-gray-700 dark:text-gray-200">{{ $jumlahAkunTerpilih }}</strong> akun dicentang.
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="tampilkanDiAkun" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-teal-500 disabled:opacity-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        Tampilkan di akun terpilih
                    </button>
                    <button type="button" wire:click="sembunyikanBarang"
                            wire:confirm="Sembunyikan barang terpilih dari semua toko dan katalog?"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-100 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:hover:bg-rose-500/25">
                        Sembunyikan
                    </button>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 dark:text-gray-500">
                Tombol "Tampilkan di akun terpilih" menambahkan akun tanpa melepas akun lain. Untuk melepas akun, klik tanda silang pada chip akun di bagian ringkasan.
            </p>
        </div>

        {{-- Ringkasan barang & toko --}}
        <div class="rounded-2xl bg-white dark:bg-gray-800 p-4 sm:p-5 ring-1 ring-gray-100 dark:ring-gray-700 space-y-4">
            <div>
                <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">Barang &amp; toko tempat tampil</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Barang tanpa akun tidak muncul di katalog publik. Klik tanda silang untuk melepas akun.
                </p>
            </div>

            <div class="space-y-2">
                @forelse ($ringkasan as $barang)
                    <div class="flex flex-col gap-2 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60 px-3 py-2.5 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <img src="{{ $barang->fotoUrl() }}" alt="{{ $barang->nama }}"
                                 class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-black/5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $barang->nama }}</p>
                                <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    Penjual asli: {{ $barang->user?->nama ?? '-' }} • {{ $barang->formattedHarga() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 sm:justify-end">
                            @foreach ($barang->akunTampil as $akun)
                                <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 py-0.5 pl-2 pr-1 text-[11px] font-semibold text-teal-700 ring-1 ring-teal-200 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-500/30">
                                    {{ $akun->nama }}
                                    <button type="button"
                                            wire:click="hapusAkun({{ $barang->id }}, {{ $akun->id }})"
                                            aria-label="Hapus akun {{ $akun->nama }} dari {{ $barang->nama }}"
                                            class="flex h-4 w-4 items-center justify-center rounded-full bg-teal-600 text-white transition hover:bg-teal-500">
                                        <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada barang yang diatur ke akun mana pun.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
