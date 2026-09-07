<?php

use App\Models\Pengeluaran;
use App\Models\Properti;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?int $filterPropertiId = null;

    public string $mode = 'buat';

    public ?int $pengeluaranId = null;

    #[Validate('required|integer')]
    public ?int $properti_id = null;

    #[Validate('required|in:listrik,air,internet,maintenance,kebersihan,gaji,renovasi,lainnya')]
    public string $kategori = 'lainnya';

    #[Validate('nullable|string|max:255')]
    public ?string $keterangan = null;

    #[Validate('required|numeric|min:0')]
    public string $jumlah = '';

    #[Validate('required|date')]
    public string $tanggal = '';

    public ?string $pesan = null;

    public function mount(?int $properti = null): void
    {
        $this->tanggal = now()->toDateString();

        if ($properti && $this->propertisTerkelola()->contains('id', $properti)) {
            $this->properti_id = $properti;
            $this->filterPropertiId = $properti;
        }
    }

    public function with(): array
    {
        $propertis = $this->propertisTerkelola();

        $propertiIds = $propertis->pluck('id');

        $query = Pengeluaran::with('properti')->whereIn('properti_id', $propertiIds);

        if ($this->filterPropertiId) {
            $query->where('properti_id', $this->filterPropertiId);
        }

        $total = (int) $query->pluck('jumlah')->sum();

        $pengeluarans = (clone $query)->orderByDesc('tanggal')->orderByDesc('id')->limit(200)->get();

        return compact('propertis', 'pengeluarans', 'total');
    }

    #[Computed]
    public function propertisTerkelola()
    {
        $user = auth()->user();

        $query = Properti::query();

        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return $query->orderBy('nama')->get();
        }

        return $query->where('pemilik_id', $user->id)->orderBy('nama')->get();
    }

    public function simpanPengeluaran(): void
    {
        if (! $this->propertisTerkelola()->contains('id', $this->properti_id)) {
            $this->addError('properti_id', 'Kos tidak valid atau bukan milik Anda.');

            return;
        }

        $this->validate();

        $data = [
            'properti_id' => $this->properti_id,
            'kategori' => $this->kategori,
            'keterangan' => $this->keterangan,
            'jumlah' => $this->jumlah,
            'tanggal' => $this->tanggal,
            'dibuat_oleh' => auth()->id(),
        ];

        if ($this->mode === 'ubah' && $this->pengeluaranId) {
            $pengeluaran = Pengeluaran::where('id', $this->pengeluaranId)
                ->whereIn('properti_id', $this->propertisTerkelola()->pluck('id'))
                ->first();

            if (! $pengeluaran) {
                return;
            }

            $pengeluaran->update($data);
            $this->pesan = 'Pengeluaran berhasil diperbarui.';
        } else {
            Pengeluaran::create($data);
            $this->pesan = 'Pengeluaran berhasil dicatat.';
        }

        $this->resetForm();
    }

    public function editPengeluaran(int $id): void
    {
        $pengeluaran = Pengeluaran::where('id', $id)
            ->whereIn('properti_id', $this->propertisTerkelola()->pluck('id'))
            ->first();

        if (! $pengeluaran) {
            return;
        }

        $this->mode = 'ubah';
        $this->pengeluaranId = $pengeluaran->id;
        $this->properti_id = $pengeluaran->properti_id;
        $this->kategori = $pengeluaran->kategori;
        $this->keterangan = $pengeluaran->keterangan;
        $this->jumlah = $pengeluaran->jumlah;
        $this->tanggal = $pengeluaran->tanggal->toDateString();
    }

    public function resetForm(): void
    {
        $this->mode = 'buat';
        $this->pengeluaranId = null;
        $this->properti_id = $this->filterPropertiId ?: null;
        $this->kategori = 'lainnya';
        $this->keterangan = null;
        $this->jumlah = '';
        $this->tanggal = now()->toDateString();
        $this->resetErrorBag();
    }

    public function hapusPengeluaran(int $id): void
    {
        $pengeluaran = Pengeluaran::where('id', $id)
            ->whereIn('properti_id', $this->propertisTerkelola()->pluck('id'))
            ->first();

        if (! $pengeluaran) {
            return;
        }

        $pengeluaran->delete();
        $this->pesan = 'Pengeluaran dihapus.';
    }
}; ?>

<div class="py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pengeluaran</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catat biaya operasional kos (listrik, air, internet, maintenance, dan lainnya) untuk menghitung laba bersih.</p>
        </div>

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        <!-- Form Pengeluaran -->
        <form wire:submit="simpanPengeluaran" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-6 space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $mode === 'ubah' ? 'Ubah Pengeluaran' : 'Catat Pengeluaran' }}</h2>
                @if ($mode === 'ubah')
                    <button type="button" wire:click="resetForm" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Batal ubah</button>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-input-label for="properti_id" value="Kos" />
                    <select wire:model="properti_id" id="properti_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">— Pilih kos —</option>
                        @foreach ($propertis as $properti)
                            <option value="{{ $properti->id }}">{{ $properti->nama }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('properti_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="kategori" value="Kategori" />
                    <select wire:model="kategori" id="kategori" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="listrik">Listrik</option>
                        <option value="air">Air</option>
                        <option value="internet">Internet</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="kebersihan">Kebersihan</option>
                        <option value="gaji">Gaji</option>
                        <option value="renovasi">Renovasi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                    <x-input-error :messages="$errors->get('kategori')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="jumlah" value="Jumlah (Rp)" />
                    <x-text-input wire:model="jumlah" id="jumlah" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 250000" />
                    <x-input-error :messages="$errors->get('jumlah')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="tanggal" value="Tanggal" />
                    <x-text-input wire:model="tanggal" id="tanggal" class="mt-1 block w-full" type="date" />
                    <x-input-error :messages="$errors->get('tanggal')" class="mt-2" />
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <x-input-label for="keterangan" value="Keterangan (opsional)" />
                    <x-text-input wire:model="keterangan" id="keterangan" class="mt-1 block w-full" placeholder="Contoh: Tagihan listrik bulan September" />
                    <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-button wire:loading.attr="disabled">
                    {{ $mode === 'ubah' ? 'Simpan Perubahan' : 'Simpan Pengeluaran' }}
                </x-primary-button>
            </div>
        </form>

        <!-- Daftar Pengeluaran -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Riwayat ({{ $pengeluarans->count() }}) · Total <span class="text-rose-600 dark:text-rose-400">Rp{{ number_format($total, 0, ',', '.') }}</span></h2>
                <select wire:model.live="filterPropertiId" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                    <option value="">Semua kos</option>
                    @foreach ($propertis as $properti)
                        <option value="{{ $properti->id }}">{{ $properti->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($pengeluarans as $pengeluaran)
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="h-10 w-10 shrink-0 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-300 flex items-center justify-center">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ str($pengeluaran->kategori)->title() }}</h3>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $pengeluaran->properti->nama }}</span>
                            </div>
                            @if ($pengeluaran->keterangan)
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400 truncate">{{ $pengeluaran->keterangan }}</p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $pengeluaran->tanggal->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <p class="font-bold text-rose-600 dark:text-rose-400">-Rp{{ number_format($pengeluaran->jumlah, 0, ',', '.') }}</p>
                            <button wire:click="editPengeluaran({{ $pengeluaran->id }})"
                                class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Ubah
                            </button>
                            <button wire:click="hapusPengeluaran({{ $pengeluaran->id }})"
                                    wire:confirm="Hapus pengeluaran ini?"
                                class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada pengeluaran. Catat pengeluaran pertama melalui form di atas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>