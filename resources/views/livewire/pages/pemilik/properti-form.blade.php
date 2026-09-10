<?php

use App\Models\Properti;
use App\Models\User;
use App\Support\FacilityHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?Properti $properti = null;

    #[Validate('required|string|max:255')]
    public string $nama = '';

    #[Validate('nullable|string|max:100')]
    public ?string $kota = null;

    #[Validate('nullable|numeric|between:-90,90')]
    public ?string $latitude = null;

    #[Validate('nullable|numeric|between:-180,180')]
    public ?string $longitude = null;

    #[Validate('nullable|string|max:500')]
    public ?string $alamat = null;

    #[Validate('nullable|string')]
    public ?string $deskripsi = null;

    public array $fasilitasTerpilih = [];

    #[Validate('nullable|string')]
    public ?string $aturan = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $denda_per_hari = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $harga = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $harga_mingguan = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $harga_harian = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $harga_asli = null;

    #[Validate('required|in:aktif,nonaktif')]
    public string $status = 'aktif';

    #[Validate('nullable|image|max:2048')]
    public $fotoBaru = null;

    public $galeriBaru = [];

    public array $hapusGaleriIds = [];

    #[Validate('nullable|integer')]
    public ?int $pemilikId = null;

    public ?string $pesan = null;

    private function bolehKelolaSemua(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin']);
    }

    public function mount(): void
    {
        if ($this->properti) {
            abort_unless(
                $this->properti->pemilik_id === auth()->id() || $this->bolehKelolaSemua(),
                403
            );

            $this->nama = $this->properti->nama;
            $this->kota = $this->properti->kota;
            $this->latitude = $this->properti->latitude;
            $this->longitude = $this->properti->longitude;
            $this->alamat = $this->properti->alamat;
            $this->deskripsi = $this->properti->deskripsi;
            $this->fasilitasTerpilih = $this->properti->fasilitas
                ? array_filter(array_map('trim', explode(',', $this->properti->fasilitas)))
                : [];
            $this->aturan = $this->properti->aturan;
            $this->denda_per_hari = $this->properti->denda_per_hari;
            $this->harga = $this->properti->harga;
            $this->harga_mingguan = $this->properti->harga_mingguan;
            $this->harga_harian = $this->properti->harga_harian;
            $this->harga_asli = $this->properti->harga_asli;
            $this->status = $this->properti->status;
        }
    }

    public function with(): array
    {
        return [
            'pilihPemilik' => $this->bolehKelolaSemua() && ! $this->properti,
            'daftarPemilik' => $this->bolehKelolaSemua()
                ? User::role('pemilik')->orderBy('nama')->get(['id', 'nama', 'email'])
                : collect(),
            'pemilikProperti' => $this->properti?->pemilik,
            'galeri' => $this->properti
                ? $this->properti->fotos()->orderBy('urutan')->orderBy('id')->get()
                : collect(),
        ];
    }

    public function simpan(): void
    {
        $this->normalizeKosong();

        if ($this->bolehKelolaSemua() && $this->properti === null) {
            $this->validate([
                'pemilikId' => ['required', 'integer', Rule::exists('users', 'id')],
            ], [
                'pemilikId.required' => 'Pilih pemilik kos.',
                'pemilikId.exists' => 'Pemilik kos tidak valid.',
            ]);

            if (! User::find($this->pemilikId)?->hasRole('pemilik')) {
                $this->addError('pemilikId', 'User yang dipilih bukan pemilik kos.');
                return;
            }
        }

        $this->validate([
            'nama' => 'required|string|max:255',
            'kota' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'alamat' => 'nullable|string|max:500',
            'deskripsi' => 'nullable|string',
            'aturan' => 'nullable|string',
            'denda_per_hari' => 'nullable|numeric|min:0',
            'harga' => 'nullable|numeric|min:0',
            'harga_mingguan' => 'nullable|numeric|min:0',
            'harga_harian' => 'nullable|numeric|min:0',
            'harga_asli' => 'nullable|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
            'galeriBaru' => ['nullable', 'array', 'max:10'],
            'galeriBaru.*' => ['image', 'max:4096'],
        ], [
            'galeriBaru.max' => 'Maksimal 10 foto tambahan sekaligus.',
            'galeriBaru.*.image' => 'Setiap file galeri harus berupa gambar.',
            'galeriBaru.*.max' => 'Ukuran tiap foto galeri maksimal 4MB.',
        ]);

        $fasilitasString = FacilityHelper::normalizeString(
            implode(', ', $this->fasilitasTerpilih)
        );

        $data = [
            'nama' => $this->nama,
            'kota' => $this->kota,
            'latitude' => $this->latitude !== null && $this->latitude !== '' ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null && $this->longitude !== '' ? (float) $this->longitude : null,
            'alamat' => $this->alamat,
            'deskripsi' => $this->deskripsi,
            'fasilitas' => $fasilitasString,
            'aturan' => $this->aturan,
            'denda_per_hari' => $this->denda_per_hari,
            'harga' => $this->harga,
            'harga_mingguan' => $this->harga_mingguan,
            'harga_harian' => $this->harga_harian,
            'harga_asli' => $this->harga_asli,
            'status' => $this->status,
        ];

        if ($this->fotoBaru) {
            $data['foto'] = $this->fotoBaru->store('properti', 'public');
            if ($this->properti?->foto) {
                Storage::disk('public')->delete($this->properti->foto);
            }
        }

        if ($this->properti) {
            $this->properti->update($data);
            $properti = $this->properti->refresh();
            session()->flash('sukses', "Perubahan kos \"{$data['nama']}\" berhasil disimpan.");
        } else {
            $data['pemilik_id'] = $this->bolehKelolaSemua() ? $this->pemilikId : auth()->id();
            $properti = Properti::create($data);
            session()->flash('sukses', "Kos baru \"$data[nama]\" berhasil ditambahkan.");
        }

        $this->sinkronGaleri($properti);

        $this->redirect(route('pemilik.properti', absolute: false), navigate: true);
    }

    private function normalizeKosong(): void
    {
        foreach (['kota', 'latitude', 'longitude', 'alamat', 'deskripsi', 'aturan', 'denda_per_hari', 'harga', 'harga_mingguan', 'harga_harian', 'harga_asli'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }
    }

    public function hapusFoto(): void
    {
        if (! $this->properti?->foto) {
            return;
        }

        Storage::disk('public')->delete($this->properti->foto);
        $this->properti->update(['foto' => null]);
        $this->pesan = 'Foto kos berhasil dihapus.';
    }

    public function tandaiHapusGaleri(int $fotoId): void
    {
        if (! in_array($fotoId, $this->hapusGaleriIds, true)) {
            $this->hapusGaleriIds[] = $fotoId;
        }
    }

    public function batalHapusGaleri(int $fotoId): void
    {
        $this->hapusGaleriIds = array_values(array_filter($this->hapusGaleriIds, fn ($id) => $id !== $fotoId));
    }

    public function jadikanCover(int $fotoId): void
    {
        if (! $this->properti) {
            return;
        }

        abort_unless(
            $this->properti->pemilik_id === auth()->id() || $this->bolehKelolaSemua(),
            403
        );

        $foto = $this->properti->fotos()->find($fotoId);

        if (! $foto) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($foto) {
            $this->properti->fotos()->update(['is_cover' => false]);
            $foto->update(['is_cover' => true, 'urutan' => 0]);
            $this->properti->update(['foto' => $foto->path]);
        });

        $this->pesan = 'Foto cover berhasil diganti.';
    }

    private function sinkronGaleri(Properti $properti): void
    {
        // Hapus yang ditandai.
        if ($this->hapusGaleriIds !== []) {
            $hapus = $properti->fotos()->whereIn('id', $this->hapusGaleriIds)->get();

            foreach ($hapus as $foto) {
                Storage::disk('public')->delete($foto->path);
                $foto->delete();
            }

            $this->hapusGaleriIds = [];
        }

        // Tambah galeri baru (total maks 10).
        $sisa = 10 - $properti->fotos()->count();

        if ($sisa > 0 && $this->galeriBaru) {
            $urutan = (int) ($properti->fotos()->max('urutan') ?? -1) + 1;

            foreach (array_slice($this->galeriBaru, 0, $sisa) as $file) {
                $path = $file->store('properti_galeri', 'public');
                $isCover = ! $properti->fotos()->exists();

                $properti->fotos()->create([
                    'path' => $path,
                    'urutan' => $urutan++,
                    'is_cover' => $isCover,
                ]);

                if ($isCover) {
                    $properti->update(['foto' => $path]);
                }
            }
        }

        // Pastikan selalu ada cover bila galeri tersisa.
        // Jika galeri kosong, biarkan kolom foto apa adanya (kompatibel data lama).
        if (! $properti->fotos()->where('is_cover', true)->exists()) {
            $pertama = $properti->fotos()->orderBy('urutan')->first();

            if ($pertama) {
                $pertama->update(['is_cover' => true]);
                $properti->update(['foto' => $pertama->path]);
            }
        }

        $this->galeriBaru = [];
    }

    public function ambilLokasiSaya(): void
    {
        $this->dispatch('minta-lokasi');
    }
}; ?>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        <div>
            <a href="{{ route('pemilik.properti') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke daftar kos
            </a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ $properti ? 'Ubah Kos: ' . $properti->nama : 'Tambah Kos Baru' }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lengkapi informasi kos agar menarik bagi pencari kos.</p>
        </div>

        <form wire:submit="simpan" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-5 sm:p-8 space-y-5">
            <!-- Foto Cover -->
            <div x-data="{
                cropper: null,
                showCrop: false,
                tempUrl: null,
                onSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.tempUrl = URL.createObjectURL(file);
                    this.showCrop = true;
                    this.$nextTick(() => {
                        if (this.cropper) this.cropper.destroy();
                        const img = document.getElementById('cropFoto');
                        img.onload = () => {
                            if (this.cropper) this.cropper.destroy();
                            this.cropper = new Cropper(img, { viewMode: 1, autoCropArea: 0.9 });
                        };
                        img.src = this.tempUrl;
                    });
                },
                batalCrop() {
                    if (this.tempUrl) URL.revokeObjectURL(this.tempUrl);
                    this.tempUrl = null;
                    this.showCrop = false;
                    if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
                    const input = document.getElementById('fotoBaru');
                    if (input) input.value = '';
                },
                terapkanCrop() {
                    if (!this.cropper) return;
                    const canvas = this.cropper.getCroppedCanvas({ maxWidth: 1920, maxHeight: 1080, imageSmoothingQuality: 'high' });
                    const wire = this.$wire || null;
                    canvas.toBlob((blob) => {
                        if (!blob) return;
                        const file = new File([blob], 'foto-properti.jpg', { type: 'image/jpeg' });
                        if (wire) wire.upload('fotoBaru', file, () => this.batalCrop());
                    }, 'image/jpeg', 0.92);
                }
            }">
                <x-input-label for="fotoBaru" value="Foto Cover Kos" />
                <div class="mt-2">
                    @if ($fotoBaru)
                        <div class="relative">
                            <img src="{{ $fotoBaru->temporaryUrl() }}" class="h-48 w-full rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-600" alt="Pratinjau">
                            <button type="button" @click="$wire.set('fotoBaru', null)" class="absolute top-2 right-2 h-8 w-8 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    @elseif ($properti?->foto)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $properti->foto) }}" class="h-48 w-full rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-600" alt="Foto kos">
                            <button type="button" wire:click="hapusFoto" class="absolute top-2 right-2 h-8 w-8 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    @endif
                    <input x-ref="fileInput" type="file" id="fotoBaru" accept="image/*" @change="onSelect($event)"
                        class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-600 dark:file:text-teal-300 hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                </div>
                <x-input-error :messages="$errors->get('fotoBaru')" class="mt-2" />
                <div wire:loading wire:target="fotoBaru" class="mt-2 flex items-center gap-2 text-sm font-medium text-teal-600">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Mengunggah foto...
                </div>

                {{-- Modal Crop --}}
                <div x-show="showCrop" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70">
                    <div class="w-full max-w-3xl max-h-[90vh] flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Potong Foto Cover</h3>
                            <button type="button" @click="batalCrop" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-hidden bg-gray-100 dark:bg-gray-900">
                            <img id="cropFoto" src="" alt="Pratinjau crop" class="block max-h-[60vh] w-full">
                        </div>
                        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                            <button type="button" @click="batalCrop" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">Batal</button>
                            <button type="button" @click="terapkanCrop" class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">Terapkan</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galeri Foto (maks 10, bisa digeser di halaman detail) -->
            <div x-data="photoCropManager()" x-init="init()">
                <x-input-label for="galeriBaru" value="Galeri Foto (maks 10 foto)" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tambah beberapa foto sekaligus. Setiap foto bisa di-crop sebelum disimpan. Foto pertama menjadi cover.</p>
                <input x-ref="fileInput" type="file" accept="image/*" multiple @change="onFilesSelected($event)"
                    class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-600 dark:file:text-teal-300 hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                <x-input-error :messages="$errors->get('galeriBaru')" class="mt-2" />
                <x-input-error :messages="$errors->get('galeriBaru.*')" class="mt-2" />

                @if ($galeriBaru)
                    <div class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2">
                        @foreach ($galeriBaru as $i => $file)
                            <div class="relative h-20 rounded-lg overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600">
                                <img src="{{ $file->temporaryUrl() }}" class="h-full w-full object-cover" alt="Pratinjau {{ $i + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($galeri->isNotEmpty())
                    <div class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2">
                        @foreach ($galeri as $foto)
                            @if (! in_array($foto->id, $hapusGaleriIds, true))
                                <div class="relative h-20 rounded-lg overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600 group">
                                    <img src="{{ asset('storage/' . $foto->path) }}" class="h-full w-full object-cover" alt="Galeri">
                                    @if ($foto->is_cover)
                                        <span class="absolute top-1 left-1 rounded-full bg-teal-600 px-1.5 py-0.5 text-[9px] font-bold text-white">Cover</span>
                                    @endif
                                    <div class="absolute inset-x-0 bottom-0 flex items-center justify-center gap-1 bg-black/50 p-1 opacity-0 group-hover:opacity-100 transition">
                                        @if (! $foto->is_cover)
                                            <button type="button" wire:click="jadikanCover({{ $foto->id }})" class="text-[10px] font-semibold text-white hover:underline">Cover</button>
                                        @endif
                                        <button type="button" wire:click="tandaiHapusGaleri({{ $foto->id }})" class="text-[10px] font-semibold text-rose-200 hover:underline">Hapus</button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @if ($hapusGaleriIds !== [])
                        <div class="mt-2 flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400">
                            <span>{{ count($hapusGaleriIds) }} foto akan dihapus saat disimpan.</span>
                            @foreach ($hapusGaleriIds as $batalId)
                                <button type="button" wire:click="batalHapusGaleri({{ $batalId }})" class="font-semibold underline">Batalkan #{{ $batalId }}</button>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- Modal Crop Multiple Photos --}}
                <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80">
                    <div class="w-full max-w-4xl max-h-[95vh] flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Crop Foto Galeri</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Foto <span x-text="currentIndex + 1"></span> dari <span x-text="files.length"></span>
                                </p>
                            </div>
                            <button type="button" @click="cancelAll" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-hidden bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                            <img x-ref="cropImage" src="" alt="Crop" class="max-h-[60vh] max-w-full">
                        </div>
                        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between gap-3">
                                <button type="button" @click="prevImage" :disabled="!canGoPrev" :class="canGoPrev ? 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100' : 'text-gray-300 dark:text-gray-600 cursor-not-allowed'" class="inline-flex items-center gap-1 text-sm font-medium">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                                    Sebelumnya
                                </button>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="skipCrop" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                                        Lewati
                                    </button>
                                    <button type="button" @click="applyCrop" class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                        <span x-text="isLastImage ? 'Selesai' : 'Terapkan & Lanjut'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nama Kos -->
            <div>
                <x-input-label for="nama" value="Nama Kos" />
                <x-text-input wire:model="nama" id="nama" class="mt-1 block w-full" placeholder="Contoh: Kos Melati" />
                <x-input-error :messages="$errors->get('nama')" class="mt-2" />
            </div>

            @if ($pilihPemilik)
                <div>
                    <x-input-label for="pemilikId" value="Pemilik Kos" />
                    <select wire:model="pemilikId" id="pemilikId"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">-- Pilih pemilik kos --</option>
                        @foreach ($daftarPemilik as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('pemilikId')" class="mt-2" />
                </div>
            @elseif ($properti && $pemilikProperti)
                <div>
                    <x-input-label for="pemilikInfo" value="Pemilik Kos" />
                    <div class="mt-1 flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 px-3 py-2.5">
                        <x-user-avatar :user="$pemilikProperti" size="sm" />
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pemilikProperti->nama }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $pemilikProperti->email }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Lokasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="kota" value="Kota" />
                    <x-text-input wire:model="kota" id="kota" class="mt-1 block w-full" placeholder="Contoh: Bandung" />
                    <x-input-error :messages="$errors->get('kota')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" value="Status Tampil" />
                    <select wire:model="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="aktif">Aktif -- tampil di Cari Kos</option>
                        <option value="nonaktif">Nonaktif -- disembunyikan</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="alamat" value="Alamat Lengkap" />
                <x-text-input wire:model="alamat" id="alamat" class="mt-1 block w-full" placeholder="Jalan, RT/RW, kecamatan" />
                <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
            </div>

            <!-- Lokasi Peta -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="latitude" value="Latitude" />
                    <x-text-input wire:model="latitude" id="latitude" class="mt-1 block w-full" type="number" step="any" min="-90" max="90" placeholder="-6.200000" />
                    <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="longitude" value="Longitude" />
                    <x-text-input wire:model="longitude" id="longitude" class="mt-1 block w-full" type="number" step="any" min="-180" max="180" placeholder="106.816666" />
                    <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
                </div>
            </div>

            @php
                $titikAwal = \App\Support\Koordinat::titik($kota, $latitude, $longitude) ?? [-6.9175, 107.6191];
            @endphp

            <div id="peta-properti-form"
                wire:ignore
                data-lat="{{ $latitude }}"
                data-lng="{{ $longitude }}"
                data-default-lat="{{ $titikAwal[0] }}"
                data-default-lng="{{ $titikAwal[1] }}"
                class="mt-3 h-72 w-full rounded-xl ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden z-0"></div>

            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                <button type="button" id="peta-cari-alamat" class="inline-flex items-center gap-1 font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg>
                    Cari dari Alamat
                </button>
                <button type="button" wire:click="ambilLokasiSaya" class="inline-flex items-center gap-1 font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    Gunakan lokasi saya saat ini
                </button>
                <span id="peta-status">Klik pada peta untuk menandai lokasi kos.</span>
            </div>

            <div>
                <x-input-label for="deskripsi" value="Deskripsi" />
                <textarea wire:model="deskripsi" id="deskripsi" rows="3" placeholder="Ceritakan keunggulan kos Anda..."
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
            </div>

            <!-- Harga: sekali input bulanan + mingguan + harian -->
            <div>
                <x-input-label value="Harga Sewa (sekali isi, pencari kos yang memilih)" />
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Isi harga per bulan (wajib). Harga per minggu & per hari opsional — kosongkan bila tidak menerima periode itu.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="harga" value="Harga per Bulan (Rp)" />
                    <x-text-input wire:model="harga" id="harga" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 750000" />
                    <x-input-error :messages="$errors->get('harga')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="harga_mingguan" value="Harga per Minggu (Rp)" />
                    <x-text-input wire:model="harga_mingguan" id="harga_mingguan" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 200000" />
                    <x-input-error :messages="$errors->get('harga_mingguan')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="harga_harian" value="Harga per Hari (Rp)" />
                    <x-text-input wire:model="harga_harian" id="harga_harian" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 40000" />
                    <x-input-error :messages="$errors->get('harga_harian')" class="mt-2" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="harga_asli" value="Harga Asli / Sebelum Diskon (Rp)" />
                    <x-text-input wire:model="harga_asli" id="harga_asli" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 900000 (dicoret)" />
                    <x-input-error :messages="$errors->get('harga_asli')" class="mt-2" />
                </div>
            </div>

            <!-- Fasilitas - Checkboxes dengan Ikon -->
            <div>
                <x-input-label value="Fasilitas yang Tersedia" />
                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Pilih fasilitas yang tersedia di kos Anda.</p>
                <div class="mt-3">
                    <x-facility-icons :selected="$fasilitasTerpilih" :editable="true" />
                </div>
                @error('fasilitasTerpilih') <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            <!-- Denda -->
            <div>
                <x-input-label for="denda_per_hari" value="Denda Terlambat per Hari (Rp)" />
                <x-text-input wire:model="denda_per_hari" id="denda_per_hari" class="mt-1 block w-full sm:w-64" type="number" min="0" placeholder="5000" />
                <x-input-error :messages="$errors->get('denda_per_hari')" class="mt-2" />
            </div>

            <!-- Aturan -->
            <div>
                <x-input-label for="aturan" value="Aturan Kos" />
                <textarea wire:model="aturan" id="aturan" rows="3" placeholder="Contoh: Jam malam 23.00, dilarang membawa tamu menginap"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                <x-input-error :messages="$errors->get('aturan')" class="mt-2" />
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('pemilik.properti') }}" wire:navigate class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">
                    Batal
                </a>
                <x-primary-button wire:loading.attr="disabled">
                    {{ $properti ? 'Simpan Perubahan' : 'Simpan Kos' }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>

@script
<script>
    let petaForm = null;
    let markerForm = null;
    let statusPeta = null;
    let geocoderForm = null;
    let modePeta = '';

    function geocoderFormDapat() {
        if (!geocoderForm) geocoderForm = new google.maps.Geocoder();
        return geocoderForm;
    }

    function tulisStatusForm(t) {
        if (statusPeta) statusPeta.textContent = t;
    }

    function setKoordinatForm(lat, lng) {
        const latStr = Number(lat.toFixed(7));
        const lngStr = Number(lng.toFixed(7));

        const el = document.getElementById('peta-properti-form');
        if (el) { el.dataset.lat = lat; el.dataset.lng = lng; }

        if (modePeta === 'js') {
            if (markerForm) markerForm.setPosition({ lat: latStr, lng: lngStr });
            if (petaForm) petaForm.panTo({ lat: latStr, lng: lngStr });
        } else if (modePeta === 'embed' && typeof window.pasangGoogleEmbed === 'function') {
            window.pasangGoogleEmbed(el, latStr, lngStr, 15);
        }

        $wire.set('latitude', latStr);
        $wire.set('longitude', lngStr);
        tulisStatusForm('Koordinat: ' + lat.toFixed(5) + ', ' + lng.toFixed(5));
    }

    function alamatSaatIni() {
        const input = document.getElementById('alamat');
        return input ? input.value.trim() : ($wire.alamat || '').trim();
    }

    function isiAlamatDariPeta(lat, lng) {
        if (alamatSaatIni() !== '') return;

        if (typeof google !== 'undefined' && google.maps) {
            geocoderFormDapat().geocode({ location: { lat, lng } }, (hasil, status) => {
                if (status === 'OK' && hasil && hasil.length && alamatSaatIni() === '') {
                    $wire.set('alamat', hasil[0].formatted_address);
                }
            });
            return;
        }

        fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=id&lat=' + lat + '&lon=' + lng)
            .then(r => r.json())
            .then(j => {
                if (j && j.display_name && alamatSaatIni() === '') {
                    $wire.set('alamat', j.display_name);
                }
            })
            .catch(() => {});
    }

    function pasangCariAlamatForm() {
        const tombol = document.getElementById('peta-cari-alamat');
        if (!tombol) return;

        tombol.addEventListener('click', () => {
            const alamat = alamatSaatIni();
            const kota = (document.getElementById('kota')?.value || '').trim();
            const q = [alamat, kota].filter(Boolean).join(', ');

            if (!q) {
                tulisStatusForm('Isi alamat atau kota terlebih dahulu.');
                return;
            }

            tulisStatusForm('Mencari alamat…');

            if (typeof google !== 'undefined' && google.maps) {
                geocoderFormDapat().geocode({ address: q }, (hasil, status) => {
                    if (status === 'OK' && hasil && hasil.length) {
                        const pos = hasil[0].geometry.location;
                        setKoordinatForm(pos.lat(), pos.lng());
                        if (alamatSaatIni() === '') {
                            $wire.set('alamat', hasil[0].formatted_address);
                        }
                        tulisStatusForm('Lokasi ditemukan.');
                    } else {
                        tulisStatusForm('Lokasi tidak ditemukan. Coba perbaiki alamat.');
                    }
                });
                return;
            }

            fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&accept-language=id&limit=1&q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(hasil => {
                    if (!hasil || !hasil.length) {
                        tulisStatusForm('Lokasi tidak ditemukan. Coba perbaiki alamat.');
                        return;
                    }
                    const hit = hasil[0];
                    setKoordinatForm(parseFloat(hit.lat), parseFloat(hit.lon));
                    if (alamatSaatIni() === '') {
                        $wire.set('alamat', hit.display_name);
                    }
                    tulisStatusForm('Lokasi ditemukan.');
                })
                .catch(() => tulisStatusForm('Gagal mencari alamat. Coba lagi.'));
        });
    }

    function cariDariKoordinatKiri() {
        if (!navigator.geolocation) {
            tulisStatusForm('Geolocation tidak didukung oleh browser Anda.');
            return;
        }

        navigator.geolocation.getCurrentPosition((pos) => {
            setKoordinatForm(pos.coords.latitude, pos.coords.longitude);
            isiAlamatDariPeta(pos.coords.latitude, pos.coords.longitude);
        }, () => tulisStatusForm('Gagal mendapatkan lokasi. Pastikan izin lokasi diberikan.'));
    }

    function pasangToggleLokasiForm() {
        if (typeof Livewire === 'undefined') return;
        Livewire.on('minta-lokasi', cariDariKoordinatKiri);
    }

    function koordinatAwalForm() {
        const el = document.getElementById('peta-properti-form');
        const lat = parseFloat(el?.dataset.lat);
        const lng = parseFloat(el?.dataset.lng);
        if (!Number.isNaN(lat) && !Number.isNaN(lng)) return { lat, lng };
        return {
            lat: parseFloat(el?.dataset['defaultLat']),
            lng: parseFloat(el?.dataset['defaultLng']),
        };
    }

    function initPetaForm() {
        const el = document.getElementById('peta-properti-form');
        if (!el || el.dataset.terpasang) return;
        el.dataset.terpasang = '1';

        statusPeta = document.getElementById('peta-status');

        const awal = koordinatAwalForm();
        if (Number.isNaN(awal.lat) || Number.isNaN(awal.lng)) {
            tulisStatusForm('Koordinat belum diketahui.');
            return;
        }

        if (typeof google === 'undefined' || !google.maps) {
            modePeta = 'embed';
            if (typeof window.pasangGoogleEmbed === 'function') {
                window.pasangGoogleEmbed(el, awal.lat, awal.lng, 15);
                tulisStatusForm("Peta Google (pratinjau tanpa API key). Untuk menandai titik, isi alamat lalu tekan 'Cari dari Alamat', atau isi Latitude/Longitude.");
            } else {
                el.innerHTML = '<div class="h-full w-full flex items-center justify-center p-4 text-center text-xs text-gray-400">Peta belum dikonfigurasi.</div>';
            }
            pasangCariAlamatForm();
            pasangToggleLokasiForm();
            return;
        }

        modePeta = 'js';
        petaForm = new google.maps.Map(el, {
            center: awal,
            zoom: Number.isNaN(parseFloat(el.dataset.lat)) ? 12 : 16,
            mapTypeId: 'roadmap',
        });

        markerForm = new google.maps.Marker({
            position: awal,
            map: petaForm,
            draggable: true,
            title: 'Geser untuk memindahkan lokasi',
        });

        markerForm.addListener('dragend', () => {
            const p = markerForm.getPosition();
            setKoordinatForm(p.lat(), p.lng());
            isiAlamatDariPeta(p.lat(), p.lng());
        });

        petaForm.addListener('click', (e) => {
            setKoordinatForm(e.latLng.lat(), e.latLng.lng());
            isiAlamatDariPeta(e.latLng.lat(), e.latLng.lng());
        });

        pasangCariAlamatForm();
        pasangToggleLokasiForm();
    }

    // Initialize on load
    if (typeof window.loadNgekosMaps === 'function') {
        window.loadNgekosMaps(initPetaForm);
    } else {
        initPetaForm();
    }

    // Re-initialize after Livewire navigation
    document.addEventListener('livewire:navigated', () => {
        // Reset flag so map can be re-initialized
        const el = document.getElementById('peta-properti-form');
        if (el && el.dataset.terpasang) {
            delete el.dataset.terpasang;
        }
        
        // Re-init map
        setTimeout(() => {
            if (typeof window.loadNgekosMaps === 'function') {
                window.loadNgekosMaps(initPetaForm);
            } else {
                initPetaForm();
            }
        }, 100);
    });
</script>
@endscript

