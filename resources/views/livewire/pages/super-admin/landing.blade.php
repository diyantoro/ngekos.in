<?php

use App\Models\Pengaturan;
use App\Models\Properti;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public string $cari = '';

    public ?string $pesan = null;

    public ?string $galat = null;

    public ?int $heroPropertiId = null;

    public array $promoIds = [];

    public array $banners = [];

    public ?int $bannerIndex = null;

    public string $bannerBrand = '';

    public string $bannerTagline = '';

    public string $bannerDesc = '';

    public string $bannerGradient = 'from-blue-700 via-sky-600 to-sky-500';

    public string $bannerAccent = 'text-sky-200';

    public string $bannerIcon = 'wifi';

    public bool $bannerAktif = true;

    public float $promoIntervalDetik = 3;

    public ?string $promoBerakhirPada = null;

    public $bannerGambar;

    public ?string $bannerImageLama = null;

    public function mount(): void
    {
        $this->heroPropertiId = Pengaturan::landingHeroId();
        $this->promoIds = Pengaturan::landingPromoIds();
        $this->banners = Pengaturan::landingBanners();
        $this->promoIntervalDetik = Pengaturan::promoIntervalMs() / 1000;
        $this->promoBerakhirPada = Pengaturan::promoBerakhirPada()?->format('Y-m-d\TH:i');
        $this->resetBannerForm();
    }

    public function with(): array
    {
        return [
            'landingPropertis' => Properti::select(['id', 'nama', 'kota', 'alamat', 'status', 'harga', 'harga_asli'])
                ->with('pemilik:id,nama')
                ->when($this->cari, fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$this->cari}%")->orWhere('kota', 'like', "%{$this->cari}%")))
                ->orderBy('nama')
                ->limit(30)
                ->get(),
            'heroProperti' => $this->heroPropertiId ? Properti::select(['id', 'nama', 'kota', 'alamat', 'status', 'harga', 'harga_asli'])
                ->with('pemilik:id,nama')
                ->find($this->heroPropertiId) : null,
            'promoTerpilih' => $this->promoIds !== [] ? Properti::select(['id', 'nama', 'kota', 'status', 'harga', 'harga_asli'])
                ->whereIn('id', $this->promoIds)
                ->orderByRaw('FIELD(id, '.implode(',', array_map('intval', $this->promoIds)).')')
                ->get() : collect(),
        ];
    }

    public function resetBannerForm(): void
    {
        $this->reset(['bannerIndex', 'bannerBrand', 'bannerTagline', 'bannerDesc', 'bannerGambar', 'bannerImageLama']);
        $this->bannerGradient = 'from-blue-700 via-sky-600 to-sky-500';
        $this->bannerAccent = 'text-sky-200';
        $this->bannerIcon = 'wifi';
        $this->bannerAktif = true;
    }

    public function simpanHero(): void
    {
        $this->reset(['pesan', 'galat']);

        $this->validate([
            'heroPropertiId' => 'nullable|integer|exists:propertis,id',
        ], [], ['heroPropertiId' => 'kos hero']);

        if ($this->heroPropertiId) {
            $kos = Properti::select(['id', 'status'])->find($this->heroPropertiId);

            if (! $kos || $kos->status !== 'aktif') {
                $this->galat = 'Kos terpilih tidak aktif sehingga tidak bisa dipin di hero.';

                return;
            }
        }

        Pengaturan::simpanLandingHero($this->heroPropertiId ?: null);
        $this->pesan = $this->heroPropertiId ? 'Kos hero berhasil dipin. Landing kini selalu menampilkan kos tersebut.' : 'Pin hero dilepas. Landing kembali otomatis memakai kos terbaru.';
    }

    public function lepasHero(): void
    {
        $this->heroPropertiId = null;
        $this->simpanHero();
    }

    public function togglePromo(int $id): void
    {
        $this->reset(['pesan', 'galat']);
        $id = (int) $id;
        $daftar = array_map('intval', (array) $this->promoIds);

        if (in_array($id, $daftar, true)) {
            $daftar = array_values(array_diff($daftar, [$id]));
        } else {
            if (count($daftar) >= 8) {
                $this->galat = 'Maksimal 8 kos untuk Promo Ngebut. Lepas satu dulu untuk menambah.';

                return;
            }

            $daftar[] = $id;
        }

        $this->promoIds = $daftar;
        Pengaturan::simpanLandingPromo($daftar);
        $this->pesan = 'Pilihan Promo Ngebut disimpan ('.count($daftar).'/8). Kosongkan semua untuk kembali otomatis.';
    }

    public function naikPromo(int $id): void
    {
        $daftar = array_values(array_map('intval', (array) $this->promoIds));
        $pos = array_search((int) $id, $daftar, true);

        if ($pos === false || $pos === 0) {
            return;
        }

        [$daftar[$pos - 1], $daftar[$pos]] = [$daftar[$pos], $daftar[$pos - 1]];
        $this->promoIds = $daftar;
        Pengaturan::simpanLandingPromo($daftar);
        $this->pesan = 'Urutan Promo Ngebut diperbarui.';
    }

    public function turunPromo(int $id): void
    {
        $daftar = array_values(array_map('intval', (array) $this->promoIds));
        $pos = array_search((int) $id, $daftar, true);

        if ($pos === false || $pos >= count($daftar) - 1) {
            return;
        }

        [$daftar[$pos + 1], $daftar[$pos]] = [$daftar[$pos], $daftar[$pos + 1]];
        $this->promoIds = $daftar;
        Pengaturan::simpanLandingPromo($daftar);
        $this->pesan = 'Urutan Promo Ngebut diperbarui.';
    }

    public function resetPromo(): void
    {
        $this->reset(['pesan', 'galat']);
        $this->promoIds = [];
        Pengaturan::simpanLandingPromo([]);
        $this->pesan = 'Pin Promo Ngebut dikosongkan. Landing kembali otomatis menampilkan kos diskon terbaru.';
    }

    public function baruBanner(): void
    {
        $this->reset(['pesan', 'galat']);
        $this->resetBannerForm();
    }

    public function ubahBanner(int $index): void
    {
        $this->reset(['pesan', 'galat']);
        $banner = $this->banners[$index] ?? null;

        if (! $banner) {
            return;
        }

        $this->bannerIndex = $index;
        $this->bannerBrand = (string) ($banner['brand'] ?? '');
        $this->bannerTagline = (string) ($banner['tagline'] ?? '');
        $this->bannerDesc = (string) ($banner['desc'] ?? '');
        $this->bannerGradient = (string) ($banner['gradient'] ?? 'from-blue-700 via-sky-600 to-sky-500');
        $this->bannerAccent = (string) ($banner['accent'] ?? 'text-sky-200');
        $this->bannerIcon = (string) ($banner['icon'] ?? 'wifi');
        $this->bannerAktif = (bool) ($banner['aktif'] ?? true);
        $this->bannerImageLama = $banner['image'] ?? null;
        $this->reset('bannerGambar');
    }

    public function simpanBanner(): void
    {
        $this->reset(['pesan', 'galat']);

        $this->validate([
            'bannerBrand' => 'required|string|max:40',
            'bannerTagline' => 'required|string|max:60',
            'bannerDesc' => 'nullable|string|max:160',
            'bannerGradient' => 'required|string|max:80',
            'bannerAccent' => 'required|string|max:30',
            'bannerIcon' => 'required|in:wifi,bag,scooter,wallet,tv,chair,tag,gift,star',
            'bannerGambar' => 'nullable|image|max:2048',
        ], [], [
            'bannerBrand' => 'nama brand',
            'bannerTagline' => 'tagline',
            'bannerDesc' => 'deskripsi',
            'bannerGradient' => 'gradasi warna',
            'bannerAccent' => 'warna aksen',
            'bannerIcon' => 'ikon',
            'bannerGambar' => 'gambar banner',
        ]);

        $gambar = $this->bannerImageLama;

        if ($this->bannerGambar) {
            $baru = $this->bannerGambar->store('landing', 'public');

            if ($gambar && Storage::disk('public')->exists($gambar)) {
                Storage::disk('public')->delete($gambar);
            }

            $gambar = $baru;
        }

        $data = [
            'brand' => trim($this->bannerBrand),
            'tagline' => trim($this->bannerTagline),
            'desc' => trim($this->bannerDesc),
            'gradient' => $this->bannerGradient,
            'accent' => $this->bannerAccent,
            'icon' => $this->bannerIcon,
            'image' => $gambar,
            'aktif' => (bool) $this->bannerAktif,
        ];

        $daftar = array_values($this->banners);

        if ($this->bannerIndex !== null && isset($daftar[$this->bannerIndex])) {
            $daftar[$this->bannerIndex] = $data;
            $this->pesan = 'Banner "'.$data['brand'].'" diperbarui.';
        } else {
            if (count($daftar) >= 8) {
                if ($gambar && Storage::disk('public')->exists($gambar)) {
                    Storage::disk('public')->delete($gambar);
                }

                $this->galat = 'Maksimal 8 banner. Hapus satu dulu untuk menambah.';

                return;
            }

            $daftar[] = $data;
            $this->pesan = 'Banner "'.$data['brand'].'" ditambahkan.';
        }

        Pengaturan::simpanLandingBanners($daftar);
        $this->banners = $daftar;
        $this->resetBannerForm();
    }

    public function hapusBanner(int $index): void
    {
        $this->reset(['pesan', 'galat']);
        $daftar = array_values($this->banners);

        if (! isset($daftar[$index])) {
            return;
        }

        $hapus = $daftar[$index];
        unset($daftar[$index]);
        $daftar = array_values($daftar);

        if (! empty($hapus['image']) && Storage::disk('public')->exists($hapus['image'])) {
            $masihDipakai = collect($daftar)->contains(fn ($b) => ($b['image'] ?? null) === $hapus['image']);

            if (! $masihDipakai) {
                Storage::disk('public')->delete($hapus['image']);
            }
        }

        Pengaturan::simpanLandingBanners($daftar);
        $this->banners = $daftar;

        if ($this->bannerIndex === $index) {
            $this->resetBannerForm();
        }

        $this->pesan = 'Banner dihapus.';
    }

    public function toggleBanner(int $index): void
    {
        $daftar = array_values($this->banners);

        if (! isset($daftar[$index])) {
            return;
        }

        $daftar[$index]['aktif'] = ! ($daftar[$index]['aktif'] ?? true);
        Pengaturan::simpanLandingBanners($daftar);
        $this->banners = $daftar;
        $this->pesan = $daftar[$index]['aktif'] ? 'Banner diaktifkan.' : 'Banner dinonaktifkan (disembunyikan dari landing).';
    }

    public function naikBanner(int $index): void
    {
        $daftar = array_values($this->banners);

        if (! isset($daftar[$index]) || $index === 0) {
            return;
        }

        [$daftar[$index - 1], $daftar[$index]] = [$daftar[$index], $daftar[$index - 1]];
        Pengaturan::simpanLandingBanners($daftar);
        $this->banners = $daftar;
    }

    public function turunBanner(int $index): void
    {
        $daftar = array_values($this->banners);

        if (! isset($daftar[$index]) || $index >= count($daftar) - 1) {
            return;
        }

        [$daftar[$index + 1], $daftar[$index]] = [$daftar[$index], $daftar[$index + 1]];
        Pengaturan::simpanLandingBanners($daftar);
        $this->banners = $daftar;
    }

    public function simpanIntervalPromo(): void
    {
        $this->reset(['pesan', 'galat']);

        $this->validate([
            'promoIntervalDetik' => 'required|numeric|min:1|max:10',
        ], [], ['promoIntervalDetik' => 'jeda slide promo']);

        Pengaturan::simpanPromoIntervalMs((int) round($this->promoIntervalDetik * 1000));
        $this->promoIntervalDetik = Pengaturan::promoIntervalMs() / 1000;
        $this->pesan = 'Jeda auto-slide promo disimpan ('.$this->promoIntervalDetik.' detik).';
    }

    public function simpanPromoBerakhir(): void
    {
        $this->reset(['pesan', 'galat']);

        $this->validate([
            'promoBerakhirPada' => 'required|date|after:now',
        ], [], ['promoBerakhirPada' => 'waktu berakhir promo']);

        Pengaturan::simpanPromoBerakhirPada(\Illuminate\Support\Carbon::parse($this->promoBerakhirPada));
        $this->promoBerakhirPada = Pengaturan::promoBerakhirPada()?->format('Y-m-d\TH:i');
        $this->pesan = 'Hitung mundur Promo Ngebut diatur sampai '.Pengaturan::promoBerakhirPada()->translatedFormat('d M Y, H:i').'.';
    }

    public function resetPromoBerakhir(): void
    {
        $this->reset(['pesan', 'galat']);

        Pengaturan::simpanPromoBerakhirPada(null);
        $this->promoBerakhirPada = null;
        $this->pesan = 'Waktu Promo Ngebut dikembalikan otomatis (akhir bulan berjalan).';
    }

    public function kembalikanBannerDefault(): void
    {
        $this->reset(['pesan', 'galat']);

        foreach ((array) $this->banners as $banner) {
            if (! empty($banner['image']) && Storage::disk('public')->exists($banner['image'])) {
                Storage::disk('public')->delete($banner['image']);
            }
        }

        Pengaturan::simpanBanyak(['landing.banners' => null]);
        $this->banners = [];
        $this->resetBannerForm();
        $this->pesan = 'Banner dikembalikan ke bawaan (Biznet, Shopee, GoFood, DANA, IndiHome, IKEA).';
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke dashboard
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Kelola Landing</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Atur hero, Promo Ngebut, dan banner iklan yang tampil di halaman utama.</p>
        </div>

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 font-bold">&times;</button>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari kos berdasarkan nama atau kota..."
                class="w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Gambar kos mengambang (Hero)</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pin 1 kos agar foto hero di landing tidak ikut berubah saat ada kos baru. Kosongkan untuk kembali otomatis (kos terbaru).</p>
                <div class="mt-3 flex flex-col sm:flex-row gap-2">
                    <select wire:model="heroPropertiId"
                        class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        <option value="">Otomatis (kos terbaru)</option>
                        @if ($heroProperti && ! $landingPropertis->contains('id', $heroProperti->id))
                            <option value="{{ $heroProperti->id }}">{{ $heroProperti->nama }} — terpin saat ini</option>
                        @endif
                        @foreach ($landingPropertis as $lp)
                            <option value="{{ $lp->id }}">{{ $lp->nama }} — {{ $lp->kota ?? '-' }} ({{ $lp->status }})</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button wire:click="simpanHero" wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                            Simpan
                        </button>
                        <button wire:click="lepasHero" wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition disabled:opacity-50">
                            Otomatis
                        </button>
                    </div>
                </div>
                @error('heroPropertiId') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                @if ($heroProperti)
                    <div class="mt-3 flex items-center gap-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3">
                        @php $heroCover = $heroProperti->fotoCover(); @endphp
                        @if ($heroCover)
                            <img src="{{ $heroCover }}" alt="{{ $heroProperti->nama }}" class="h-12 w-12 rounded-lg object-cover">
                        @endif
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate">{{ $heroProperti->nama }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $heroProperti->kota ?? $heroProperti->alamat ?? '-' }} · {{ $heroProperti->pemilik?->nama ?? '-' }}</p>
                        </div>
                        <span class="ml-auto shrink-0"><x-status-badge :status="$heroProperti->status" /></span>
                    </div>
                    @if ($heroProperti->status !== 'aktif')
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">Kos ini tidak aktif — landing otomatis memakai kos terbaru sampai Anda ganti pin.</p>
                    @endif
                @else
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Mode otomatis aktif. Cari kos lewat kolom “Cari data...” di atas untuk mempersempit daftar.</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Promo Ngebut ({{ count($promoIds) }}/8)</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Centang kos untuk dikunci di Promo Ngebut sesuai urutan. Kosongkan semua untuk kembali otomatis (kos diskon terbaru).</p>
                    </div>
                    <button wire:click="resetPromo" wire:loading.attr="disabled"
                        class="shrink-0 inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition disabled:opacity-50">
                        Kembali otomatis
                    </button>
                </div>
                <form wire:submit="simpanPromoBerakhir" class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Hitung mundur berakhir</label>
                        <input type="datetime-local" wire:model="promoBerakhirPada"
                            class="mt-1 block rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition">Simpan waktu</button>
                    <button type="button" wire:click="resetPromoBerakhir" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">Otomatis (akhir bulan)</button>
                    <p class="w-full text-[11px] text-gray-400 dark:text-gray-500">Waktu hari/jam/menit/detik di landing. Kosongkan kembali = akhir bulan berjalan.</p>
                    @error('promoBerakhirPada') <p class="w-full text-xs text-rose-600">{{ $message }}</p> @enderror
                </form>
                @if ($promoTerpilih->isNotEmpty())
                    <div class="mt-3 space-y-2">
                        @foreach ($promoTerpilih as $urutan => $pp)
                            <div class="flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 px-3 py-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-teal-600 text-[11px] font-extrabold text-white">{{ $urutan + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">{{ $pp->nama }}</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $pp->kota ?? '-' }} · Rp{{ number_format($pp->harga ?? 0, 0, ',', '.') }}@if ($pp->harga_asli && $pp->harga_asli > $pp->harga) <span class="line-through">Rp{{ number_format($pp->harga_asli, 0, ',', '.') }}</span>@endif</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <button wire:click="naikPromo({{ $pp->id }})" title="Naik" class="rounded-md px-1.5 py-1 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">↑</button>
                                    <button wire:click="turunPromo({{ $pp->id }})" title="Turun" class="rounded-md px-1.5 py-1 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">↓</button>
                                    <button wire:click="togglePromo({{ $pp->id }})" title="Lepas" class="rounded-md px-1.5 py-1 font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="mt-3 divide-y divide-gray-100 dark:divide-gray-700 rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                    @forelse ($landingPropertis as $lp)
                        @php $terpilih = in_array((int) $lp->id, array_map('intval', (array) $promoIds), true); @endphp
                        <div class="flex items-center gap-3 px-3 py-2.5 {{ $terpilih ? 'bg-teal-50/60 dark:bg-teal-500/5' : '' }}">
                            <button wire:click="togglePromo({{ $lp->id }})" wire:loading.attr="disabled"
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border {{ $terpilih ? 'border-teal-600 bg-teal-600 text-white' : 'border-gray-300 dark:border-gray-600 text-transparent' }}">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $lp->nama }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $lp->kota ?? $lp->alamat ?? '-' }} · <x-status-badge :status="$lp->status" /></p>
                            </div>
                            @if ($terpilih)
                                <span class="shrink-0 rounded-full bg-teal-600 px-2 py-0.5 text-[10px] font-bold text-white">#{{ array_search((int) $lp->id, array_map('intval', (array) $promoIds), true) + 1 }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="px-3 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Tidak ada kos yang cocok. Ubah kata kunci pencarian.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Banner iklan partner ({{ count($banners) }}/8)</h4>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Banner paling atas di landing. Nonaktifkan untuk menyembunyikan tanpa menghapus.</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button wire:click="baruBanner" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-500 transition">+ Banner</button>
                        <button wire:click="kembalikanBannerDefault" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 transition">Kembalikan bawaan</button>
                    </div>
                </div>
                <form wire:submit="simpanIntervalPromo" class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Jeda auto-slide promo (detik)</label>
                        <input type="number" wire:model="promoIntervalDetik" min="1" max="10" step="0.5"
                            class="mt-1 block w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition">Simpan jeda</button>
                    <p class="w-full text-[11px] text-gray-400 dark:text-gray-500">Berlaku untuk semua carousel promo (landing & halaman cari kos). Antara 1–10 detik.</p>
                    @error('promoIntervalDetik') <p class="w-full text-xs text-rose-600">{{ $message }}</p> @enderror
                </form>
                <div class="mt-3 space-y-2">
                    @forelse ($banners as $i => $bn)
                        <div class="flex items-center gap-3 rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden {{ ($bn['aktif'] ?? true) ? '' : 'opacity-60' }}">
                            <div class="flex h-14 w-20 shrink-0 items-center justify-center bg-gradient-to-tr {{ $bn['gradient'] }}">
                                @if (! empty($bn['image']))
                                    <img src="{{ asset('storage/'.$bn['image']) }}" alt="{{ $bn['brand'] }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-sm font-extrabold text-white drop-shadow">{{ strtoupper(mb_substr($bn['brand'], 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1 py-2">
                                <p class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">{{ $bn['brand'] }} @if (! ($bn['aktif'] ?? true))<span class="ml-1 rounded-full bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500">nonaktif</span>@endif</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $bn['tagline'] }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1 pr-2">
                                <button wire:click="ubahBanner({{ $i }})" title="Ubah" class="rounded-md px-1.5 py-1 text-xs font-semibold text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-500/10">Ubah</button>
                                <button wire:click="toggleBanner({{ $i }})" title="Aktif/nonaktif" class="rounded-md px-1.5 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">{{ ($bn['aktif'] ?? true) ? 'Matikan' : 'Nyalakan' }}</button>
                                <button wire:click="naikBanner({{ $i }})" title="Naik" class="rounded-md px-1 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">↑</button>
                                <button wire:click="turunBanner({{ $i }})" title="Turun" class="rounded-md px-1 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">↓</button>
                                <button wire:click="hapusBanner({{ $i }})" title="Hapus" class="rounded-md px-1.5 py-1 font-bold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10">&times;</button>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl ring-1 ring-dashed ring-gray-300 dark:ring-gray-600 px-3 py-6 text-center text-xs text-gray-400 dark:text-gray-500">Memakai banner bawaan (Biznet, Shopee, GoFood, DANA, IndiHome, IKEA). Tambah banner untuk mengganti.</p>
                    @endforelse
                </div>
                <form wire:submit="simpanBanner" class="mt-4 space-y-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 ring-1 ring-gray-100 dark:ring-gray-700 p-3 sm:p-4">
                    <p class="text-xs font-bold text-gray-900 dark:text-gray-100">{{ $bannerIndex !== null ? 'Ubah banner' : 'Tambah banner baru' }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Nama brand</label>
                            <input type="text" wire:model="bannerBrand" placeholder="cth: Biznet"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('bannerBrand') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Tagline</label>
                            <input type="text" wire:model="bannerTagline" placeholder="cth: Internet Cepat & Stabil"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('bannerTagline') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Deskripsi</label>
                        <input type="text" wire:model="bannerDesc" placeholder="cth: Pasang internet, kuliah online makin lancar."
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                        @error('bannerDesc') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Gradasi warna</label>
                            <select wire:model="bannerGradient"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="from-blue-700 via-sky-600 to-sky-500">Biru</option>
                                <option value="from-orange-600 via-orange-500 to-amber-400">Oranye</option>
                                <option value="from-green-600 via-emerald-500 to-teal-500">Hijau</option>
                                <option value="from-sky-600 via-blue-500 to-indigo-500">Biru DANA</option>
                                <option value="from-red-700 via-rose-600 to-pink-500">Merah</option>
                                <option value="from-blue-800 via-blue-600 to-cyan-500">Biru tua</option>
                                <option value="from-teal-700 via-emerald-600 to-lime-500">Teal</option>
                                <option value="from-violet-700 via-purple-600 to-fuchsia-500">Ungu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Ikon</label>
                            <select wire:model="bannerIcon"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="wifi">Wifi</option>
                                <option value="bag">Tas belanja</option>
                                <option value="scooter">Skuter</option>
                                <option value="wallet">Dompet</option>
                                <option value="tv">TV</option>
                                <option value="chair">Kursi</option>
                                <option value="tag">Tag promo</option>
                                <option value="gift">Kado</option>
                                <option value="star">Bintang</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Warna aksen</label>
                            <select wire:model="bannerAccent"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="text-sky-200">Biru muda</option>
                                <option value="text-amber-200">Kuning</option>
                                <option value="text-emerald-200">Hijau muda</option>
                                <option value="text-blue-200">Biru</option>
                                <option value="text-rose-200">Merah muda</option>
                                <option value="text-cyan-200">Cyan</option>
                                <option value="text-white">Putih</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200">Gambar banner (opsional, maks 2MB)</label>
                        <input type="file" wire:model="bannerGambar" accept="image/*"
                            class="mt-1 block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-teal-500">
                        @error('bannerGambar') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @if ($bannerGambar)
                            <img src="{{ $bannerGambar->temporaryUrl() }}" alt="Pratinjau banner" class="mt-2 h-20 w-auto rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        @elseif ($bannerImageLama)
                            <img src="{{ asset('storage/'.$bannerImageLama) }}" alt="Banner aktif" class="mt-2 h-20 w-auto rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                        @endif
                    </div>
                    <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200">
                        <input type="checkbox" wire:model="bannerAktif" class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        Tampilkan di landing
                    </label>
                    <div class="flex items-center gap-2 pt-1">
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                            {{ $bannerIndex !== null ? 'Simpan perubahan' : 'Tambah banner' }}
                        </button>
                        @if ($bannerIndex !== null)
                            <button type="button" wire:click="baruBanner" class="inline-flex items-center rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300">Batal</button>
                        @endif
                        <span wire:loading.delay wire:target="simpanBanner,bannerGambar" class="text-xs text-gray-400">Menyimpan...</span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
