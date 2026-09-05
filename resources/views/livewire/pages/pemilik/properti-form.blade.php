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

    #[Validate('required|in:bulanan,harian')]
    public string $jenis_harga = 'bulanan';

    #[Validate('required|in:aktif,nonaktif')]
    public string $status = 'aktif';

    #[Validate('nullable|image|max:2048')]
    public $fotoBaru = null;

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
            $this->jenis_harga = $this->properti->jenis_harga ?? 'bulanan';
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
            'jenis_harga' => 'required|in:bulanan,harian',
            'status' => 'required|in:aktif,nonaktif',
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
            'jenis_harga' => $this->jenis_harga,
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
            session()->flash('sukses', "Perubahan kos \"{$data['nama']}\" berhasil disimpan.");
        } else {
            $data['pemilik_id'] = $this->bolehKelolaSemua() ? $this->pemilikId : auth()->id();
            Properti::create($data);
            session()->flash('sukses', "Kos baru \"$data[nama]\" berhasil ditambahkan.");
        }

        $this->redirect(route('pemilik.properti', absolute: false), navigate: true);
    }

    private function normalizeKosong(): void
    {
        foreach (['kota', 'latitude', 'longitude', 'alamat', 'deskripsi', 'aturan', 'denda_per_hari', 'harga'] as $field) {
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
            <div>
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
                    <input wire:model="fotoBaru" id="fotoBaru" type="file" accept="image/*"
                        class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-600 dark:file:text-teal-300 hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                </div>
                <x-input-error :messages="$errors->get('fotoBaru')" class="mt-2" />
                <div wire:loading wire:target="fotoBaru" class="mt-2 flex items-center gap-2 text-sm font-medium text-teal-600">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Mengunggah foto...
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
            <div>
                <button type="button" wire:click="ambilLokasiSaya" class="inline-flex items-center gap-1.5 text-xs font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    Gunakan lokasi saya saat ini
                </button>
                @if ($latitude && $longitude)
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Koordinat: {{ $latitude }}, {{ $longitude }}</p>
                @endif
            </div>

            <div>
                <x-input-label for="deskripsi" value="Deskripsi" />
                <textarea wire:model="deskripsi" id="deskripsi" rows="3" placeholder="Ceritakan keunggulan kos Anda..."
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
            </div>

            <!-- Harga -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="jenis_harga" value="Jenis Harga" />
                    <select wire:model="jenis_harga" id="jenis_harga" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="bulanan">Per Bulan</option>
                        <option value="harian">Per Hari</option>
                    </select>
                    <x-input-error :messages="$errors->get('jenis_harga')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="harga" value="Harga (Rp)" />
                    <x-text-input wire:model="harga" id="harga" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 750000" />
                    <x-input-error :messages="$errors->get('harga')" class="mt-2" />
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
    Livewire.on('minta-lokasi', () => {
        function gunakan(wireId) {
            if (!navigator.geolocation) {
                alert('Geolocation tidak didukung oleh browser Anda.');
                return;
            }
            navigator.geolocation.getCurrentPosition((pos) => {
                const comp = Livewire.find(wireId);
                comp.set('latitude', Number(pos.coords.latitude.toFixed(7)));
                comp.set('longitude', Number(pos.coords.longitude.toFixed(7)));
            }, () => alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diberikan.'));
        }
        const el = document.querySelector('[wire\\:id]');
        el && gunakan(el.getAttribute('wire:id'));
    });
</script>
@endscript

