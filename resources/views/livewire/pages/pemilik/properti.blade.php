<?php

use App\Models\Properti;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?string $pesan = null;

    /**
     * Pemilik hanya mengelola kos miliknya sendiri,
     * sedangkan admin/super admin dapat mengelola seluruh kos.
     */
    private function bolehKelolaSemua(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin']);
    }

    public function mount(): void
    {
        // Pesan sukses dari halaman form (flash setelah redirect simpan).
        $this->pesan = session('sukses');
    }

    public function with(): array
    {
        return [
            'kelolaSemua' => $this->bolehKelolaSemua(),
            'propertis' => Properti::query()
                ->when(! $this->bolehKelolaSemua(), fn ($q) => $q->where('pemilik_id', auth()->id()))
                ->with('fotos')
                ->withCount(['kamars as total_kamar', 'kamars as kamar_terisi' => fn ($q) => $q->where('status', 'terisi')])
                ->orderBy('nama')
                ->get(),
        ];
    }

    public function ubahStatus(int $id, string $status): void
    {
        $properti = Properti::where('id', $id)
            ->when(! $this->bolehKelolaSemua(), fn ($q) => $q->where('pemilik_id', auth()->id()))
            ->first();

        if (! $properti) {
            return;
        }

        $properti->update(['status' => $status === 'aktif' ? 'aktif' : 'nonaktif']);

        $this->pesan = $status === 'aktif'
            ? "Kos \"{$properti->nama}\" kini tampil di halaman Cari Kos."
            : "Kos \"{$properti->nama}\" disembunyikan dari halaman Cari Kos.";
    }

    public function hapusProperti(int $id): void
    {
        $properti = Properti::where('id', $id)
            ->when(! $this->bolehKelolaSemua(), fn ($q) => $q->where('pemilik_id', auth()->id()))
            ->first();

        if (! $properti) {
            return;
        }

        $nama = $properti->nama;

        if ($properti->foto) {
            Storage::disk('public')->delete($properti->foto);
        }

        foreach ($properti->fotos as $foto) {
            Storage::disk('public')->delete($foto->path);
        }

        foreach ($properti->kamars as $kamar) {
            if ($kamar->foto) {
                Storage::disk('public')->delete($kamar->foto);
            }
            foreach ($kamar->fotos as $foto) {
                Storage::disk('public')->delete($foto->path);
            }
        }

        $properti->delete();

        $this->pesan = "Kos \"{$nama}\" beserta seluruh kamar & datanya telah dihapus.";
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $kelolaSemua ? 'Kelola Semua Kos' : 'Kelola Kos Saya' }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($kelolaSemua)
                        Sebagai admin, Anda dapat mengelola seluruh kos yang terdaftar di Ngekos.in.
                    @else
                        Kos dengan status <span class="font-medium text-emerald-600 dark:text-emerald-400">Aktif</span> akan tampil di halaman Cari Kos untuk dipromosikan.
                    @endif
                </p>
            </div>
            <a href="{{ route('pemilik.properti.buat') }}" wire:navigate
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Kos Baru
            </a>
        </div>

        @forelse ($propertis as $properti)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row gap-5">
                        <div class="h-36 sm:h-32 sm:w-48 shrink-0 rounded-xl bg-gradient-to-br from-teal-100 via-emerald-100 to-cyan-100 dark:from-teal-500/20 dark:via-emerald-500/20 dark:to-cyan-500/20 overflow-hidden relative">
                            @php $coverKelola = $properti->fotoCover(); @endphp
                            @if ($coverKelola)
                                <img src="{{ $coverKelola }}" alt="{{ $properti->nama }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-10 w-10 text-teal-300 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18-8.25V21m-1.5-8.25v-3.75a2.25 2.25 0 00-2.25-2.25h-1.5m-1.5 0V3.545c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v7.5" /></svg>
                                </div>
                            @endif
                            @if (count($properti->galeriUrls()) > 1)
                                <span class="absolute bottom-2 left-2 rounded-full bg-black/50 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">{{ count($properti->galeriUrls()) }} foto</span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $properti->nama }}</h2>
                                <x-status-badge :status="$properti->status" />
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 truncate">{{ $properti->alamat ?? $properti->kota ?? 'Lokasi belum diisi' }}</p>
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $properti->total_kamar }} kamar &middot; {{ $properti->kamar_terisi }} terisi
                            </p>
                        </div>

                        <div class="flex sm:flex-col items-center sm:items-stretch gap-2 sm:gap-2 shrink-0">
                            <a href="{{ route('pemilik.kamar', $properti) }}" wire:navigate
                               class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">
                                Kelola Kamar
                            </a>
                            <a href="{{ route('pemilik.properti.ubah', $properti) }}" wire:navigate
                               class="inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Ubah
                            </a>
                            @if ($properti->status === 'aktif')
                                <button wire:click="ubahStatus({{ $properti->id }}, 'nonaktif')"
                                    class="inline-flex items-center justify-center rounded-lg border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-4 py-2 text-sm font-medium text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition">
                                    Sembunyikan
                                </button>
                            @else
                                <button wire:click="ubahStatus({{ $properti->id }}, 'aktif')"
                                    class="inline-flex items-center justify-center rounded-lg border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition">
                                    Tampilkan
                                </button>
                            @endif
                            <button wire:click="hapusProperti({{ $properti->id }})"
                                    wire:confirm="Hapus kos ini beserta datanya?"
                                class="inline-flex items-center justify-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-4 py-2 text-sm font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 py-16 text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-teal-400 dark:text-teal-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18-8.25V21m-1.5-8.25v-3.75a2.25 2.25 0 00-2.25-2.25h-1.5m-1.5 0V3.545c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v7.5" /></svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Belum ada kos terdaftar.</p>
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Tambahkan kos pertamamu agar mulai dipromosikan di Ngekos.in.</p>
                <a href="{{ route('pemilik.properti.buat') }}" wire:navigate
                   class="mt-5 inline-flex items-center rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-500 transition">
                    Tambah Kos Baru
                </a>
            </div>
        @endforelse
    </div>
</div>