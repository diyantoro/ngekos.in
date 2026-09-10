<?php

use App\Models\Kamar;
use App\Models\Properti;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public Properti $properti;

    public string $mode = 'buat';

    public ?int $kamarId = null;

    #[Validate('required|string|max:100')]
    public string $nama = '';

    #[Validate('required|integer|min:1|max:10')]
    public int $kapasitas = 1;

    #[Validate('required|numeric|min:0')]
    public string $harga = '';

    #[Validate('nullable|numeric|min:0')]
    public ?string $harga_harian = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $harga_mingguan = null;

    #[Validate('nullable|numeric|min:0')]
    public ?string $harga_asli = null;

    #[Validate('required|in:tersedia,terisi,perbaikan')]
    public string $status = 'tersedia';

    #[Validate('nullable|image|max:2048')]
    public $fotoBaru = null;

    public $galeriBaru = [];

    public array $hapusGaleriIds = [];

    public array $galeriEdit = [];

    public ?string $pesan = null;

    public ?string $galat = null;

    public function mount(): void
    {
        abort_unless(
            $this->properti->pemilik_id === auth()->id()
                || auth()->user()->hasAnyRole(['admin', 'super_admin']),
            403
        );
    }

    public function with(): array
    {
        return [
            'kamars' => Kamar::with('fotos')->where('properti_id', $this->properti->id)->orderBy('nama')->get(),
            'kamarEdit' => $this->mode === 'ubah' && $this->kamarId ? Kamar::with('fotos')->find($this->kamarId) : null,
        ];
    }

    public function simpanKamar(): void
    {
        $this->validate(array_merge(
            $this->aturanValidasi(),
            ['galeriBaru' => ['nullable', 'array', 'max:10'], 'galeriBaru.*' => ['image', 'max:4096']],
        ), [
            'galeriBaru.max' => 'Maksimal 10 foto tambahan sekaligus.',
            'galeriBaru.*.image' => 'Setiap file galeri harus berupa gambar.',
            'galeriBaru.*.max' => 'Ukuran tiap foto galeri maksimal 4MB.',
        ]);

        $data = $this->dataForm();

        if ($this->mode === 'ubah' && $this->kamarId) {
            $kamar = Kamar::where('id', $this->kamarId)->where('properti_id', $this->properti->id)->first();

            if (! $kamar) {
                return;
            }

            if ($this->fotoBaru && $kamar->foto) {
                Storage::disk('public')->delete($kamar->foto);
            }

            $kamar->update($data);
            $this->sinkronGaleri($kamar->refresh());
            $this->pesan = "Kamar {$kamar->nama} berhasil diperbarui.";
        } else {
            $data['properti_id'] = $this->properti->id;
            $kamar = Kamar::create($data);
            $this->sinkronGaleri($kamar);
            $this->pesan = "Kamar {$data['nama']} berhasil ditambahkan.";
        }

        $this->resetForm();
    }

    private function aturanValidasi(): array
    {
        return [
            'nama' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1|max:10',
            'harga' => 'required|numeric|min:0',
            'harga_mingguan' => 'nullable|numeric|min:0',
            'harga_harian' => 'nullable|numeric|min:0',
            'harga_asli' => 'nullable|numeric|min:0',
            'status' => 'required|in:tersedia,terisi,perbaikan',
            'fotoBaru' => 'nullable|image|max:2048',
        ];
    }

    private function dataForm(): array
    {
        $data = [
            'nama' => $this->nama,
            'kapasitas' => $this->kapasitas,
            'harga_sewa_bulanan' => $this->harga,
            'harga_sewa_mingguan' => $this->harga_mingguan === '' || $this->harga_mingguan === null ? null : $this->harga_mingguan,
            'harga_sewa_harian' => $this->harga_harian === '' || $this->harga_harian === null ? null : $this->harga_harian,
            'harga_asli' => $this->harga_asli === '' || $this->harga_asli === null ? null : $this->harga_asli,
            'status' => $this->status,
        ];

        if ($this->fotoBaru) {
            $data['foto'] = $this->fotoBaru->store('kamar', 'public');
        }

        return $data;
    }

    private function sinkronGaleri(Kamar $kamar): void
    {
        if ($this->hapusGaleriIds !== []) {
            $hapus = $kamar->fotos()->whereIn('id', $this->hapusGaleriIds)->get();

            foreach ($hapus as $foto) {
                Storage::disk('public')->delete($foto->path);
                $foto->delete();
            }

            $this->hapusGaleriIds = [];
        }

        $sisa = 10 - $kamar->fotos()->count();

        if ($sisa > 0 && $this->galeriBaru) {
            $urutan = (int) ($kamar->fotos()->max('urutan') ?? -1) + 1;

            foreach (array_slice($this->galeriBaru, 0, $sisa) as $file) {
                $path = $file->store('kamar_galeri', 'public');
                $isCover = ! $kamar->fotos()->exists();

                $kamar->fotos()->create([
                    'path' => $path,
                    'urutan' => $urutan++,
                    'is_cover' => $isCover,
                ]);

                if ($isCover) {
                    $kamar->update(['foto' => $path]);
                }
            }
        }

        if (! $kamar->fotos()->where('is_cover', true)->exists()) {
            $pertama = $kamar->fotos()->orderBy('urutan')->first();

            if ($pertama) {
                $pertama->update(['is_cover' => true]);
                $kamar->update(['foto' => $pertama->path]);
            }
        }
    }

    public function editKamar(int $id): void
    {
        $kamar = Kamar::where('id', $id)->where('properti_id', $this->properti->id)->first();

        if (! $kamar) {
            return;
        }

        $this->mode = 'ubah';
        $this->kamarId = $kamar->id;
        $this->nama = $kamar->nama;
        $this->kapasitas = (int) $kamar->kapasitas;
        $this->harga = $kamar->harga_sewa_bulanan;
        $this->harga_mingguan = $kamar->harga_sewa_mingguan ?? null;
        $this->harga_harian = $kamar->harga_sewa_harian ?? null;
        $this->harga_asli = $kamar->harga_asli ?? null;
        $this->status = $kamar->status;
        $this->fotoBaru = null;
        $this->galeriBaru = [];
        $this->hapusGaleriIds = [];
        $this->galeriEdit = $kamar->fotos()->orderBy('urutan')->get()->map(fn ($f) => [
            'id' => $f->id, 'path' => $f->path, 'is_cover' => (bool) $f->is_cover,
        ])->all();
    }

    public function resetForm(): void
    {
        $this->mode = 'buat';
        $this->kamarId = null;
        $this->nama = '';
        $this->kapasitas = 1;
        $this->harga = '';
        $this->harga_mingguan = null;
        $this->harga_harian = null;
        $this->harga_asli = null;
        $this->status = 'tersedia';
        $this->fotoBaru = null;
        $this->galeriBaru = [];
        $this->hapusGaleriIds = [];
        $this->galeriEdit = [];
        $this->resetErrorBag();
    }

    public function hapusKamar(int $id): void
    {
        $kamar = Kamar::where('id', $id)->where('properti_id', $this->properti->id)->first();

        if (! $kamar) {
            return;
        }

        if ($kamar->foto) {
            Storage::disk('public')->delete($kamar->foto);
        }

        foreach ($kamar->fotos as $foto) {
            Storage::disk('public')->delete($foto->path);
        }

        $kamar->delete();
        $this->pesan = "Kamar {$kamar->nama} dihapus.";
    }

    public function tandaiHapusGaleriKamar(int $fotoId): void
    {
        if (! in_array($fotoId, $this->hapusGaleriIds, true)) {
            $this->hapusGaleriIds[] = $fotoId;
        }

        $this->galeriEdit = array_values(array_filter(
            $this->galeriEdit,
            fn ($g) => ($g['id'] ?? null) !== $fotoId
        ));
    }

    public function jadikanCoverKamar(int $fotoId): void
    {
        $kamar = $this->mode === 'ubah' && $this->kamarId
            ? Kamar::where('id', $this->kamarId)->where('properti_id', $this->properti->id)->first()
            : null;

        if (! $kamar) {
            return;
        }

        $foto = $kamar->fotos()->find($fotoId);

        if (! $foto) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($kamar, $foto) {
            $kamar->fotos()->update(['is_cover' => false]);
            $foto->update(['is_cover' => true, 'urutan' => 0]);
            $kamar->update(['foto' => $foto->path]);
        });

        $this->galeriEdit = $kamar->fotos()->orderBy('urutan')->get()->map(fn ($f) => [
            'id' => $f->id, 'path' => $f->path, 'is_cover' => (bool) $f->is_cover,
        ])->all();

        $this->pesan = 'Foto cover kamar berhasil diganti.';
    }
}; ?>

<div class="py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('pemilik.properti') }}" wire:navigate class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:text-teal-500 dark:hover:text-teal-300 inline-flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke daftar kos
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">Kelola Kamar — {{ $properti->nama }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan, ubah, atau hapus kamar kos Anda.</p>
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

        <!-- Form Kamar -->
        <form wire:submit="simpanKamar" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-6 space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $mode === 'ubah' ? 'Ubah Kamar' : 'Tambah Kamar' }}</h2>
                @if ($mode === 'ubah')
                    <button type="button" wire:click="resetForm" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Batal ubah</button>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="nama" value="Nama Kamar" />
                    <x-text-input wire:model="nama" id="nama" class="mt-1 block w-full" placeholder="Contoh: A1" />
                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="kapasitas" value="Kapasitas (orang)" />
                    <x-text-input wire:model="kapasitas" id="kapasitas" class="mt-1 block w-full" type="number" min="1" max="10" />
                    <x-input-error :messages="$errors->get('kapasitas')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="harga" value="Harga per Bulan (Rp)" />
                    <x-text-input wire:model="harga" id="harga" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 1000000" />
                    <x-input-error :messages="$errors->get('harga')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="harga_mingguan" value="Harga per Minggu (Rp) — opsional" />
                    <x-text-input wire:model="harga_mingguan" id="harga_mingguan" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 300000" />
                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Kosongkan bila tidak menerima sewa mingguan.</p>
                    <x-input-error :messages="$errors->get('harga_mingguan')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="harga_harian" value="Harga per Hari (Rp) — opsional" />
                    <x-text-input wire:model="harga_harian" id="harga_harian" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 50000" />
                    <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Kosongkan bila tidak menerima sewa harian.</p>
                    <x-input-error :messages="$errors->get('harga_harian')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="harga_asli" value="Harga Asli / Sebelum Diskon (Rp)" />
                    <x-text-input wire:model="harga_asli" id="harga_asli" class="mt-1 block w-full" type="number" min="0" placeholder="Contoh: 1200000 (dicoret)" />
                    <x-input-error :messages="$errors->get('harga_asli')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" value="Status" />
                    <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="tersedia">Tersedia</option>
                        <option value="terisi">Terisi</option>
                        <option value="perbaikan">Perbaikan</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="fotoBaru" value="Foto Kamar" />
                <input wire:model="fotoBaru" id="fotoBaru" type="file" accept="image/*"
                    class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-600 dark:file:text-teal-300 hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                <x-input-error :messages="$errors->get('fotoBaru')" class="mt-2" />
                <div wire:loading wire:target="fotoBaru" class="mt-3 flex items-center gap-2 text-sm font-medium text-teal-600">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Mengunggah foto...
                </div>

                @if ($fotoBaru)
                    <div class="mt-3">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Pratinjau foto baru:</p>
                        <img src="{{ $fotoBaru->temporaryUrl() }}" class="h-40 w-full sm:w-72 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-600" alt="Pratinjau foto kamar">
                    </div>
                @elseif ($kamarEdit?->foto)
                    <div class="mt-3">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Foto saat ini:</p>
                        <img src="{{ asset('storage/' . $kamarEdit->foto) }}" class="h-40 w-full sm:w-72 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-600" alt="Foto kamar saat ini">
                    </div>
                @endif
            </div>

            <div>
                <x-input-label for="galeriBaru" value="Galeri Foto Kamar (maks 10 foto)" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bisa tambah beberapa foto sekaligus; tampil bisa digeser di halaman detail.</p>
                <input wire:model="galeriBaru" id="galeriBaru" type="file" accept="image/*" multiple
                    class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 dark:file:bg-teal-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-600 dark:file:text-teal-300 hover:file:bg-teal-100 dark:hover:file:bg-teal-500/20">
                <x-input-error :messages="$errors->get('galeriBaru')" class="mt-2" />
                <x-input-error :messages="$errors->get('galeriBaru.*')" class="mt-2" />

                @if ($galeriBaru)
                    <div class="mt-3 grid grid-cols-4 sm:grid-cols-6 gap-2">
                        @foreach ($galeriBaru as $i => $file)
                            <div class="h-16 rounded-lg overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600">
                                <img src="{{ $file->temporaryUrl() }}" class="h-full w-full object-cover" alt="Pratinjau {{ $i + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($mode === 'ubah' && $galeriEdit !== [])
                    <div class="mt-3 grid grid-cols-4 sm:grid-cols-6 gap-2">
                        @foreach ($galeriEdit as $g)
                            <div class="relative h-16 rounded-lg overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600 group">
                                <img src="{{ asset('storage/' . $g['path']) }}" class="h-full w-full object-cover" alt="Galeri">
                                @if ($g['is_cover'])
                                    <span class="absolute top-1 left-1 rounded-full bg-teal-600 px-1.5 py-0.5 text-[9px] font-bold text-white">Cover</span>
                                @else
                                    <button type="button" wire:click="jadikanCoverKamar({{ $g['id'] }})" class="absolute bottom-1 left-1 rounded bg-black/60 px-1.5 py-0.5 text-[9px] font-semibold text-white opacity-0 group-hover:opacity-100 transition">Cover</button>
                                @endif
                                <button type="button" wire:click="tandaiHapusGaleriKamar({{ $g['id'] }})" class="absolute top-1 right-1 h-5 w-5 rounded-full bg-black/60 text-white text-xs leading-none opacity-0 group-hover:opacity-100 transition">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-end">
                <x-primary-button wire:loading.attr="disabled">
                    {{ $mode === 'ubah' ? 'Simpan Perubahan' : 'Tambah Kamar' }}
                </x-primary-button>
            </div>
        </form>

        <!-- Daftar Kamar -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Daftar Kamar ({{ $kamars->count() }})</h2>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($kamars as $kamar)
                    <div wire:key="kamar-{{ $kamar->id }}" class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="h-14 w-20 shrink-0 rounded-lg bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-800 overflow-hidden relative">
                            @php $coverKamar = $kamar->fotoCover(); @endphp
                            @if ($coverKamar)
                                <img src="{{ $coverKamar }}" alt="Kamar {{ $kamar->nama }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <svg class="h-6 w-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" /></svg>
                                </div>
                            @endif
                            @if (count($kamar->galeriUrls()) > 1)
                                <span class="absolute bottom-1 left-1 rounded-full bg-black/50 px-1 py-px text-[9px] font-bold text-white">{{ count($kamar->galeriUrls()) }} foto</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Kamar {{ $kamar->nama }}</h3>
                                <x-status-badge :status="$kamar->status" />
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                {{ $kamar->kapasitas }} orang
                                @if ($kamar->harga_asli && $kamar->harga_asli > $kamar->harga_sewa_bulanan)
                                    <span class="line-through text-gray-400 dark:text-gray-500">Rp{{ number_format($kamar->harga_asli, 0, ',', '.') }}</span>
                                @endif
                                &middot; Rp{{ number_format($kamar->harga_sewa_bulanan, 0, ',', '.') }}/{{ $kamar->jenis_harga === 'harian' ? 'hari' : 'bulan' }}
                                @if ($kamar->harga_sewa_harian)
                                    <span class="text-teal-600 dark:text-teal-400">| Rp{{ number_format($kamar->harga_sewa_harian, 0, ',', '.') }}/hari</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button wire:click="editKamar({{ $kamar->id }})"
                                class="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Ubah
                            </button>
                            <button wire:click="hapusKamar({{ $kamar->id }})"
                                    wire:confirm="Hapus kamar ini?"
                                class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada kamar. Tambahkan kamar pertama lewat form di atas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>