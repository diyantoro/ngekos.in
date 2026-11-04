<?php

use App\Enums\BarangKategori;
use App\Enums\BarangKondisi;
use App\Enums\BarangStatus;
use App\Models\BarangKos;
use App\Models\Penyewaan;
use App\Models\Properti;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?BarangKos $barang = null;

    public bool $isEdit = false;

    #[Validate('required|string|min:4|max:150')]
    public string $nama = '';

    #[Validate('required|string')]
    public string $kategori = 'elektronik';

    #[Validate('required|string')]
    public string $kondisi = 'bekas_mulus';

    #[Validate('required|numeric|min:0')]
    public $harga = '';

    public bool $bisa_nego = false;

    #[Validate('required|string|min:10')]
    public string $deskripsi = '';

    public ?string $minus = null;

    #[Validate('required|string|max:60')]
    public string $kota = '';

    public ?string $lokasi_detail = null;

    #[Validate('required|string|min:8|max:25')]
    public string $no_wa = '';

    public ?int $properti_id = null;

    public string $status = 'tersedia';

    public $tayangHari = 7;

    public ?string $tayangSampai = null;

    public int $jumlahBelumTerjual = 0;

    public $fotoUtama = null;

    public ?string $fotoUtamaLama = null;

    public $fotoTambahanBaru = [];

    public array $fotoTambahanLama = [];

    public function mount(?BarangKos $barang = null): void
    {
        $user = auth()->user();

        if ($barang && $barang->exists) {
            if (! $barang->isMilik($user)) {
                abort(403, 'Anda tidak memiliki hak untuk mengedit barang ini.');
            }

            $this->barang = $barang;
            $this->isEdit = true;
            $this->nama = $barang->nama;
            $this->kategori = $barang->kategori?->value ?? 'lainnya';
            $this->kondisi = $barang->kondisi?->value ?? 'bekas_mulus';
            $this->harga = $barang->harga;
            $this->bisa_nego = (bool) $barang->bisa_nego;
            $this->deskripsi = $barang->deskripsi;
            $this->minus = $barang->minus;
            $this->kota = $barang->kota;
            $this->lokasi_detail = $barang->lokasi_detail;
            $this->no_wa = $barang->no_wa;
            $this->properti_id = $barang->properti_id;
            $this->status = $barang->status?->value ?? 'tersedia';
            $this->tayangHari = $barang->tayang_hari ?? 7;
            $this->tayangSampai = $barang->tayang_sampai;
            $this->fotoUtamaLama = $barang->foto;
            $this->fotoTambahanLama = $barang->foto_tambahan ?? [];
        } else {
            $this->isEdit = false;
            $this->no_wa = $user->no_hp ?? '';
            $this->tayangHari = 7; // Default 7 hari

            // Coba tebak kota dari properti terkait user
            if ($user->hasRole('pemilik')) {
                $propertiPertama = Properti::where('pemilik_id', $user->id)->first();
                $this->kota = $propertiPertama?->kota ?? '';
                $this->properti_id = $propertiPertama?->id;
            } else {
                $sewaAktif = Penyewaan::where('anak_kos_id', $user->id)
                    ->where('status', 'aktif')
                    ->with('properti')
                    ->first();
                $this->kota = $sewaAktif?->properti?->kota ?? '';
                $this->properti_id = $sewaAktif?->properti_id;
                if ($sewaAktif?->properti) {
                    $this->lokasi_detail = 'Area ' . $sewaAktif->properti->nama;
                }
            }
        }

        $this->jumlahBelumTerjual = BarangKos::jumlahBelumTerjual(
            (int) $user->id,
            $this->isEdit ? $this->barang?->id : null
        );
    }

    public function hapusFotoTambahanLama(int $index): void
    {
        if (isset($this->fotoTambahanLama[$index])) {
            $path = $this->fotoTambahanLama[$index];
            if ($path && ! str_starts_with($path, 'http')) {
                Storage::disk('public')->delete($path);
            }
            unset($this->fotoTambahanLama[$index]);
            $this->fotoTambahanLama = array_values($this->fotoTambahanLama);
        }
    }

    public function simpan(): void
    {
        $rules = [
            'nama' => 'required|string|min:4|max:150',
            'kategori' => ['required', Rule::in(BarangKategori::values())],
            'kondisi' => ['required', Rule::in(BarangKondisi::values())],
            'harga' => 'required|numeric|min:0|max:100000000',
            'deskripsi' => 'required|string|min:10',
            'minus' => 'nullable|string',
            'kota' => 'required|string|max:60',
            'lokasi_detail' => 'nullable|string|max:255',
            'no_wa' => 'required|string|min:8|max:25',
            'properti_id' => 'nullable|integer',
            'status' => ['required', Rule::in(BarangStatus::values())],
            'tayangHari' => 'required|integer|min:1|max:7',
            'fotoUtama' => $this->isEdit ? 'nullable|image|max:4096' : 'nullable|image|max:4096',
            'fotoTambahanBaru.*' => 'nullable|image|max:4096',
        ];

        $this->validate($rules);

        $user = auth()->user();
        $jumlahLainBelumTerjual = BarangKos::jumlahBelumTerjual(
            (int) $user->id,
            $this->isEdit ? $this->barang?->id : null
        );

        if ((! $this->isEdit || $this->barang?->status === BarangStatus::Terjual)
            && $this->status !== BarangStatus::Terjual->value
            && $jumlahLainBelumTerjual >= BarangKos::BATAS_BARANG_PER_AKUN) {
            $this->addError('limitBarang', 'Maksimal '.BarangKos::BATAS_BARANG_PER_AKUN.' barang yang belum terjual. Hapus barang atau tandai terjual sebelum menambahkan barang baru.');
            return;
        }

        // Masa tayang hanya dihitung saat pemasangan baru. Edit tidak
        // memperpanjang masa tayang yang sudah berjalan.
        if (! $this->isEdit) {
            $this->tayangSampai = now()->addDays($this->tayangHari)->endOfDay();
        }

        $fotoUtamaPath = $this->fotoUtamaLama;
        if ($this->fotoUtama) {
            if ($this->fotoUtamaLama && ! str_starts_with($this->fotoUtamaLama, 'http')) {
                Storage::disk('public')->delete($this->fotoUtamaLama);
            }
            $fotoUtamaPath = $this->fotoUtama->store('barang_kos', 'public');
        }

        $fotoTambahan = $this->fotoTambahanLama;
        if (! empty($this->fotoTambahanBaru)) {
            foreach ($this->fotoTambahanBaru as $file) {
                if ($file && count($fotoTambahan) < 4) {
                    $fotoTambahan[] = $file->store('barang_kos', 'public');
                }
            }
        }

        $payload = [
            'user_id' => auth()->id(),
            'properti_id' => $this->properti_id ?: null,
            'nama' => trim($this->nama),
            'kategori' => $this->kategori,
            'kondisi' => $this->kondisi,
            'harga' => (int) $this->harga,
            'bisa_nego' => (bool) $this->bisa_nego,
            'deskripsi' => trim($this->deskripsi),
            'minus' => $this->minus ? trim($this->minus) : null,
            'foto' => $fotoUtamaPath,
            'foto_tambahan' => $fotoTambahan,
            'kota' => trim($this->kota),
            'lokasi_detail' => $this->lokasi_detail ? trim($this->lokasi_detail) : null,
            'no_wa' => trim($this->no_wa),
            'status' => $this->status,
            'tayang_hari' => $this->tayangHari,
            'tayang_sampai' => $this->tayangSampai,
        ];

        if ($this->isEdit && $this->barang) {
            $this->barang->update($payload);
            session()->flash('pesan', 'Iklan barang kos berhasil diperbarui.');
            $this->redirect(route('barang.detail', $this->barang), navigate: true);
        } else {
            $baru = BarangKos::create($payload);
            session()->flash('pesan', 'Iklan barang kos berhasil dipasang.');
            $this->redirect(route('barang.detail', $baru), navigate: true);
        }
    }

    public function with(): array
    {
        $user = auth()->user();
        $daftarProperti = collect();

        if ($user->hasRole('pemilik')) {
            $daftarProperti = Properti::where('pemilik_id', $user->id)->get(['id', 'nama', 'kota']);
        } elseif ($user->hasRole('anak_kos')) {
            $daftarProperti = Penyewaan::where('anak_kos_id', $user->id)
                ->where('status', 'aktif')
                ->with('properti:id,nama,kota')
                ->get()
                ->pluck('properti')
                ->filter();
        } elseif ($user->hasAnyRole(['admin', 'super_admin'])) {
            $daftarProperti = Properti::limit(50)->get(['id', 'nama', 'kota']);
        }

        return [
            'daftarKategori' => BarangKategori::cases(),
            'daftarKondisi' => BarangKondisi::cases(),
            'daftarProperti' => $daftarProperti,
        ];
    }
}; ?>

<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Header Form --}}
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('barang.kelola') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 dark:text-brand-300 hover:underline mb-2">
                    ← Kembali ke Kelola Barang
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $isEdit ? 'Ubah Iklan Barang Kos' : 'Pasang Iklan Barang Kos' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
                    Isi detail barang dengan jelas agar cepat diminati oleh anak kos atau pemilik kos lainnya.
                </p>
                @if (! $isEdit)
                    <p class="mt-2 text-xs font-medium text-slate-500 dark:text-gray-400">
                        Barang belum terjual: {{ $jumlahBelumTerjual }}/{{ \App\Models\BarangKos::BATAS_BARANG_PER_AKUN }}
                    </p>
                @endif
                @error('limitBarang')
                    <p class="mt-2 text-sm font-semibold text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <form wire:submit="simpan" class="bg-white dark:bg-gray-900 border border-stone-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-8">

            {{-- 1. Info Dasar --}}
            <div class="space-y-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs">1</span>
                    Informasi Barang
                </h2>

                <div>
                    <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Judul / Nama Barang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" wire:model="nama"
                           placeholder="Contoh: Kulkas Mini Sharp 50L / Meja Belajar Lipat Kayu"
                           class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                    @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="kategori" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <select id="kategori" wire:model="kategori"
                                class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-brand-500">
                            @foreach ($daftarKategori as $kat)
                                <option value="{{ $kat->value }}">{{ $kat->label() }}</option>
                            @endforeach
                        </select>
                        @error('kategori') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="kondisi" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                            Kondisi Barang <span class="text-red-500">*</span>
                        </label>
                        <select id="kondisi" wire:model="kondisi"
                                class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-brand-500">
                            @foreach ($daftarKondisi as $kon)
                                <option value="{{ $kon->value }}">{{ $kon->label() }}</option>
                            @endforeach
                        </select>
                        @error('kondisi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Harga & Nego --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                    <div>
                        <label for="harga" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                            Harga (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" id="harga" wire:model="harga" min="0" step="1000" placeholder="150000"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-brand-500">
                        </div>
                        @error('harga') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:pt-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="bisa_nego" class="rounded border-stone-300 text-brand-600 focus:ring-brand-500 h-4 w-4">
                            <span class="text-sm font-semibold text-slate-800 dark:text-gray-200">Bisa Nego Tipis</span>
                        </label>
                        <p class="text-xs text-slate-400 mt-0.5">Centang jika pembeli boleh menawar harga secara wajar.</p>
                    </div>
                </div>

                {{-- Status Iklan --}}
                @if ($isEdit)
                    <div>
                        <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                            Status Iklan
                        </label>
                        <select id="status" wire:model="status"
                                class="w-full sm:w-1/2 rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-brand-500">
                            <option value="tersedia">Tersedia (Bisa Dibeli)</option>
                            <option value="terjual">Sudah Terjual</option>
                        </select>
                    </div>
                @endif
            </div>

            {{-- 2. Foto Barang --}}
            <div class="space-y-4 pt-4 border-t border-stone-100 dark:border-gray-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs">2</span>
                    Foto Barang
                </h2>

                {{-- Foto Utama --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Foto Utama <span class="text-slate-400 font-normal">(Disarankan landscape atau 1:1, max 4MB)</span>
                    </label>

                    <div class="flex items-center gap-4">
                        @if ($fotoUtama)
                            <div class="h-24 w-24 rounded-2xl overflow-hidden border border-brand-500 shrink-0">
                                <img src="{{ $fotoUtama->temporaryUrl() }}" alt="Preview" class="h-full w-full object-cover">
                            </div>
                        @elseif ($fotoUtamaLama)
                            <div class="h-24 w-24 rounded-2xl overflow-hidden border border-stone-300 dark:border-gray-700 shrink-0">
                                <img src="{{ str_starts_with($fotoUtamaLama, 'http') ? $fotoUtamaLama : asset('storage/'.$fotoUtamaLama) }}" alt="Foto Lama" class="h-full w-full object-cover">
                            </div>
                        @endif

                        <input type="file" wire:model="fotoUtama" accept="image/*"
                               class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900 dark:file:text-brand-300">
                    </div>
                    @error('fotoUtama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Foto Tambahan (Max 4) --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Foto Tambahan <span class="text-slate-400 font-normal">(Maksimal 4 foto pendukung dari sisi lain atau kelengkapan)</span>
                    </label>

                    {{-- Foto Tambahan Lama --}}
                    @if (! empty($fotoTambahanLama))
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            @foreach ($fotoTambahanLama as $idx => $f)
                                <div class="relative h-20 w-20 rounded-xl overflow-hidden border border-stone-200 dark:border-gray-700 shrink-0 group">
                                    <img src="{{ str_starts_with($f, 'http') ? $f : asset('storage/'.$f) }}" alt="Foto" class="h-full w-full object-cover">
                                    <button type="button" wire:click="hapusFotoTambahanLama({{ $idx }})"
                                            class="absolute top-1 right-1 h-5 w-5 bg-red-600 text-white rounded-full flex items-center justify-center text-[10px] font-bold shadow hover:bg-red-700">
                                        ✕
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <input type="file" wire:model="fotoTambahanBaru" multiple accept="image/*"
                           class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-slate-700 hover:file:bg-stone-200 dark:file:bg-gray-800 dark:file:text-gray-300">
                    @error('fotoTambahanBaru.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 3. Deskripsi & Minus --}}
            <div class="space-y-4 pt-4 border-t border-stone-100 dark:border-gray-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs">3</span>
                    Deskripsi & Kondisi
                </h2>

                <div>
                    <label for="deskripsi" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Deskripsi Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea id="deskripsi" wire:model="deskripsi" rows="4"
                              placeholder="Jelaskan alasan dijual, kelengkapan barang, spesifikasi fungsi, dan lama pemakaian..."
                              class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500"></textarea>
                    @error('deskripsi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="minus" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Catatan Minus / Kekurangan <span class="text-slate-400 font-normal">(Opsional, transparansi membuat pembeli makin percaya)</span>
                    </label>
                    <textarea id="minus" wire:model="minus" rows="2"
                              placeholder="Contoh: Dus asli hilang, ada goresan kecil di bagian samping, kabel ada lakban..."
                              class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500"></textarea>
                    @error('minus') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 3.5. Masa Tayang Barang --}}
            <div class="space-y-4 pt-4 border-t border-stone-100 dark:border-gray-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 text-xs">3.5</span>
                    Masa Tayang Barang
                </h2>

                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 rounded-xl p-4">
                    @if ($isEdit)
                        <p class="text-xs text-emerald-800 dark:text-emerald-300">
                            Masa tayang tidak diperpanjang saat mengubah barang. Barang berhenti tampil di katalog setelah tanggal berikut.
                        </p>
                        <div class="mt-3 flex items-center gap-2 text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>
                                Berakhir:
                                <strong>{{ $tayangSampai ? \Carbon\Carbon::parse($tayangSampai)->format('d M Y') : 'Tidak terbatas' }}</strong>
                            </span>
                        </div>
                    @else
                        <p class="text-xs text-emerald-800 dark:text-emerald-300 mb-3">
                            Pilih berapa lama barang tampil di katalog. Maksimal 7 hari per pemasangan dan tidak dapat diperpanjang.
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label for="tayangHari" class="block text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 mb-2">
                                    Durasi Tayang <span class="text-slate-500 font-normal">(Maksimal 7 hari)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <select id="tayangHari" wire:model="tayangHari"
                                            class="flex-1 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-white dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-emerald-500">
                                        <option value="1">1 hari</option>
                                        <option value="2">2 hari</option>
                                        <option value="3">3 hari</option>
                                        <option value="4">4 hari</option>
                                        <option value="5">5 hari</option>
                                        <option value="6">6 hari</option>
                                        <option value="7" selected>7 hari (maksimal)</option>
                                    </select>
                                    <span class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">
                                        {{ $tayangHari }} hari = {{ \Carbon\Carbon::now()->addDays($tayangHari)->format('d M Y') }}
                                    </span>
                                </div>
                                @error('tayangHari') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 4. Lokasi & Kontak --}}
            <div class="space-y-4 pt-4 border-t border-stone-100 dark:border-gray-800">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 text-xs">4</span>
                    Lokasi & Nomor WhatsApp
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="kota" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                            Kota <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="kota" wire:model="kota" placeholder="Contoh: Yogyakarta / Jakarta Selatan"
                               class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                        @error('kota') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="no_wa" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                            Nomor WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="no_wa" wire:model="no_wa" placeholder="081234567890"
                               class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                        @error('no_wa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="lokasi_detail" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                        Detail Lokasi / Titik COD <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input type="text" id="lokasi_detail" wire:model="lokasi_detail"
                           placeholder="Contoh: Pogung Baru Blok F / Bisa COD di gerbang utama kampus UGM"
                           class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500">
                    @error('lokasi_detail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                @if ($daftarProperti->count() > 0)
                    <div>
                        <label for="properti_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-1.5">
                            Tautkan ke Properti Kos <span class="text-slate-400 font-normal">(Opsional, tampilkan nama kos di halaman iklan)</span>
                        </label>
                        <select id="properti_id" wire:model="properti_id"
                                class="w-full rounded-xl border border-stone-300 dark:border-gray-700 bg-stone-50 dark:bg-gray-800 text-slate-800 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Tidak Ditautkan ke Kos Tertentu --</option>
                            @foreach ($daftarProperti as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->kota }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            {{-- Tombol Submit --}}
            <div class="pt-6 border-t border-stone-100 dark:border-gray-800 flex items-center justify-end gap-3">
                <a href="{{ route('barang.kelola') }}" wire:navigate
                   class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-gray-700 text-slate-700 dark:text-gray-200 text-xs font-bold hover:bg-stone-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition shadow-md shadow-brand-700/20">
                    {{ $isEdit ? 'Perbarui Iklan' : 'Tayangkan Iklan Sekarang' }}
                </button>
            </div>

        </form>

    </div>
</div>
