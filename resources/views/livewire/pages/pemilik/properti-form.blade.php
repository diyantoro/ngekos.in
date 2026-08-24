<?php

use App\Models\Properti;
use App\Models\User;
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

    #[Validate('nullable|string|max:500')]
    public ?string $alamat = null;

    #[Validate('nullable|string')]
    public ?string $deskripsi = null;

    #[Validate('nullable|string')]
    public ?string $fasilitas = null;

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
            $this->alamat = $this->properti->alamat;
            $this->deskripsi = $this->properti->deskripsi;
            $this->fasilitas = $this->properti->fasilitas;
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

        // Admin/super admin wajib memilih pemilik HANYA saat membuat kos baru.
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

        $data = $this->validate();

        unset($data['fotoBaru'], $data['pemilikId']);

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
        foreach (['kota', 'alamat', 'deskripsi', 'fasilitas', 'aturan', 'denda_per_hari', 'harga'] as $field) {
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
}; ?>

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        <div>
            <a href="{{ route('pemilik.properti') }}" wire:navigate class="text-sm font-medium text-teal-600 hover:text-teal-500 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke daftar kos
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">
                {{ $properti ? 'Ubah Kos: ' . $properti->nama : 'Tambah Kos Baru' }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">Lengkapi informasi kos agar menarik bagi pencari kos.</p>
        </div>

        <form wire:submit="simpan" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6 sm:p-8 space-y-5">
            <div>
                <x-input-label for="nama" value="Nama Kos" />
                <x-text-input wire:model="nama" id="nama" class="mt-1 block w-full" placeholder="Contoh: Kos Melati" />
                <x-input-error :messages="$errors->get('nama')" class="mt-2" />
            </div>

            @if ($pilihPemilik)
                <div>
                    <x-input-label for="pemilikId" value="Pemilik Kos" />
                    <select wire:model="pemilikId" id="pemilikId"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">— Pilih pemilik kos —</option>
                        @foreach ($daftarPemilik as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('pemilikId')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-400">Sebagai admin, tentukan pemilik yang memiliki kos ini.</p>
                </div>
            @elseif ($properti && $pemilikProperti)
                <div>
                    <x-input-label for="pemilikInfo" value="Pemilik Kos" />
                    <x-text-input id="pemilikInfo" class="mt-1 block w-full" :value="$pemilikProperti?->nama . ' (' . $pemilikProperti?->email . ')'" disabled />
                    <p class="mt-1 text-xs text-gray-400">Pemilik tidak dapat diubah.</p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="kota" value="Kota" />
                    <x-text-input wire:model="kota" id="kota" class="mt-1 block w-full" placeholder="Contoh: Bandung" />
                    <x-input-error :messages="$errors->get('kota')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" value="Status Tampil" />
                    <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="aktif">Aktif — tampil di Cari Kos</option>
                        <option value="nonaktif">Nonaktif — disembunyikan</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="alamat" value="Alamat Lengkap" />
                <x-text-input wire:model="alamat" id="alamat" class="mt-1 block w-full" placeholder="Jalan, RT/RW, kecamatan" />
                <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="deskripsi" value="Deskripsi" />
                <textarea wire:model="deskripsi" id="deskripsi" rows="4" placeholder="Ceritakan keunggulan kos Anda..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="jenis_harga" value="Jenis Harga" />
                    <select wire:model="jenis_harga" id="jenis_harga" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="fasilitas" value="Fasilitas" />
                    <x-text-input wire:model="fasilitas" id="fasilitas" class="mt-1 block w-full" placeholder="Pisahkan dengan koma: WiFi, AC, Kasur" />
                    <x-input-error :messages="$errors->get('fasilitas')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="denda_per_hari" value="Denda Terlambat per Hari (Rp)" />
                    <x-text-input wire:model="denda_per_hari" id="denda_per_hari" class="mt-1 block w-full" type="number" min="0" placeholder="5000" />
                    <x-input-error :messages="$errors->get('denda_per_hari')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="aturan" value="Aturan Kos" />
                <textarea wire:model="aturan" id="aturan" rows="3" placeholder="Contoh: Jam malam 23.00, dilarang membawa tamu menginap"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                <x-input-error :messages="$errors->get('aturan')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="fotoBaru" value="Foto Kos" />
                <input wire:model="fotoBaru" id="fotoBaru" type="file" accept="image/*"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-600 hover:file:bg-teal-100">
                <x-input-error :messages="$errors->get('fotoBaru')" class="mt-2" />

                <div wire:loading wire:target="fotoBaru" class="mt-3 flex items-center gap-2 text-sm font-medium text-teal-600">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Mengunggah foto...
                </div>

                @if ($fotoBaru)
                    <div class="mt-3">
                        <p class="text-xs font-semibold text-gray-500 mb-1">Pratinjau foto baru:</p>
                        <img src="{{ $fotoBaru->temporaryUrl() }}" class="h-40 w-full sm:w-72 rounded-xl object-cover ring-1 ring-gray-200" alt="Pratinjau">
                    </div>
                @elseif ($properti?->foto)
                    <div class="mt-3 flex items-end gap-3">
                        <img src="{{ asset('storage/' . $properti->foto) }}" class="h-40 w-full sm:w-72 rounded-xl object-cover ring-1 ring-gray-200" alt="Foto kos">
                        <button type="button" wire:click="hapusFoto" class="text-sm font-medium text-rose-600 hover:text-rose-500">Hapus foto</button>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('pemilik.properti') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Batal
                </a>
                <x-primary-button wire:loading.attr="disabled">
                    {{ $properti ? 'Simpan Perubahan' : 'Simpan Kos' }}
                </x-primary-button>
            </div>
        </form>
    </div>
</div>