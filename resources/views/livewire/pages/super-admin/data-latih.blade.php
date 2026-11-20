<?php

use App\Models\DataLatih;
use App\Models\Kamar;
use App\Models\TitikAcuan;
use App\Services\SpkTopsisService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public float $luasM2 = 12.0;
    public float $jarakKm = 1.0;
    public string $kawasan = 'Coblong';
    public string $tipePenghuni = 'Campur';
    public array $fasilitasTerpilih = ['WiFi', 'Kasur', 'Lemari'];
    public float $hargaAktual = 1200000;

    public string $filterSumber = '';
    public string $cari = '';
    public ?string $pesan = null;

    public function tambahData(): void
    {
        $this->validate([
            'luasM2' => 'required|numeric|min:4|max:100',
            'jarakKm' => 'required|numeric|min:0.01|max:50',
            'kawasan' => 'required|string|max:100',
            'tipePenghuni' => 'required|string|max:50',
            'hargaAktual' => 'required|numeric|min:100000',
        ]);

        DataLatih::create([
            'sumber' => 'survei',
            'luas_m2' => $this->luasM2,
            'jarak_km' => $this->jarakKm,
            'kawasan' => $this->kawasan,
            'tipe_penghuni' => $this->tipePenghuni,
            'fasilitas' => $this->fasilitasTerpilih,
            'harga_aktual' => $this->hargaAktual,
            'ditandai_outlier' => false,
        ]);

        $this->pesan = 'Baris data latih baru berhasil ditambahkan!';
    }

    public function toggleOutlier(int $id): void
    {
        $data = DataLatih::findOrFail($id);
        $data->update(['ditandai_outlier' => ! $data->ditandai_outlier]);

        $status = $data->ditandai_outlier ? 'ditandai sebagai OUTLIER (dikecualikan dari training)' : 'diaktifkan kembali';
        $this->pesan = "Data ID #{$id} {$status}.";
    }

    public function hapusData(int $id): void
    {
        DataLatih::findOrFail($id)->delete();
        $this->pesan = "Data latih ID #{$id} berhasil dihapus.";
    }

    public function sinkronDariKamar(): void
    {
        $kamars = Kamar::with('properti')->whereNotNull('harga_sewa_bulanan')->get();
        $titik = TitikAcuan::first();
        $ditambahkan = 0;

        foreach ($kamars as $k) {
            $prop = $k->properti;
            if (! $prop) continue;

            // Cek apakah sudah pernah disinkronkan
            if (DataLatih::where('kamar_id', $k->id)->exists()) {
                continue;
            }

            $jarak = 1.0;
            if ($titik && $prop->latitude && $prop->longitude) {
                $jarak = SpkTopsisService::haversine(
                    (float) $prop->latitude, (float) $prop->longitude,
                    (float) $titik->latitude, (float) $titik->longitude
                );
            }

            DataLatih::create([
                'kamar_id' => $k->id,
                'sumber' => 'ngekos',
                'luas_m2' => $k->hitungLuasM2(),
                'jarak_km' => $jarak,
                'kawasan' => $prop->kota ?? 'Coblong',
                'tipe_penghuni' => $prop->tipe_hunian?->value ?? 'Campur',
                'fasilitas' => $k->daftarFasilitas(),
                'harga_aktual' => (float) $k->harga_sewa_bulanan,
                'ditandai_outlier' => false,
            ]);

            $ditambahkan++;
        }

        $this->pesan = "Berhasil menyinkronkan {$ditambahkan} data kamar aktif ke dalam dataset latih!";
    }

    public function with(): array
    {
        $query = DataLatih::query()->latest();

        if ($this->filterSumber !== '') {
            $query->where('sumber', $this->filterSumber);
        }

        if ($this->cari !== '') {
            $query->where(function ($q) {
                $q->where('kawasan', 'like', "%{$this->cari}%")
                    ->orWhere('tipe_penghuni', 'like', "%{$this->cari}%");
            });
        }

        return [
            'daftarData' => $query->paginate(20),
            'totalData' => DataLatih::count(),
            'totalSurvei' => DataLatih::where('sumber', 'survei')->count(),
            'totalNgekos' => DataLatih::where('sumber', 'ngekos')->count(),
            'totalOutlier' => DataLatih::where('ditandai_outlier', true)->count(),
        ];
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">Manajemen Data Latih (JST MLP)</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Kelola dataset survei lapangan dan data riil kamar Ngekos.in, serta tandai outlier sebelum proses pelatihan model (FR-08).
            </p>
        </div>
        <button wire:click="sinkronDariKamar" wire:loading.attr="disabled"
            class="py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition disabled:opacity-50">
            <span wire:loading.remove wire:target="sinkronDariKamar">&darr; Sinkron dari Kamar Aktif</span>
            <span wire:loading wire:target="sinkronDariKamar">Menyinkronkan Data...</span>
        </button>
    </div>

    <!-- Hubungan data latih dengan model & SPK -->
    <div class="rounded-2xl border border-teal-200 dark:border-teal-800 bg-teal-50/70 dark:bg-teal-950/20 p-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-teal-900 dark:text-teal-200">Data di sini adalah bahan training model</p>
            <p class="mt-0.5 text-[11px] text-teal-800 dark:text-teal-300">
                Setelah data cukup (minimal 30 baris non-outlier), latih model di menu <strong>Model AI</strong>
                lalu aktifkan versinya. Model aktif dipakai untuk memprediksi harga wajar di halaman SPK
                dan saran harga untuk pemilik kos.
            </p>
        </div>
        <a href="{{ route('admin.model-ai') }}" wire:navigate
            class="shrink-0 inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 transition">
            Lanjut ke Model AI &rarr;
        </a>
    </div>

    @if ($pesan)
        <div class="p-4 rounded-xl bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800 text-teal-800 dark:text-teal-200 text-sm flex justify-between items-center">
            <span>{{ $pesan }}</span>
            <button wire:click="$set('pesan', null)" class="text-teal-600 font-bold">&times;</button>
        </div>
    @endif

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
            <div class="text-xs text-gray-500">Total Baris Data</div>
            <div class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mt-1">{{ $totalData }}</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
            <div class="text-xs text-gray-500">Sumber Survei Manual</div>
            <div class="text-2xl font-extrabold text-teal-600 mt-1">{{ $totalSurvei }}</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
            <div class="text-xs text-gray-500">Sumber Ngekos.in</div>
            <div class="text-2xl font-extrabold text-teal-700 mt-1">{{ $totalNgekos }}</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
            <div class="text-xs text-gray-500">Ditandai Outlier</div>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ $totalOutlier }}</div>
        </div>
    </div>

    <!-- Form Tambah Data Survei Cepat -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 p-6 space-y-4">
        <h2 class="text-sm font-bold text-gray-900 dark:text-gray-100">Tambah Data Latih Baru (Survei)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400">Luas (m²)</label>
                <input type="number" step="0.5" wire:model="luasM2" class="mt-1 block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400">Jarak Kampus (km)</label>
                <input type="number" step="0.1" wire:model="jarakKm" class="mt-1 block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400">Kawasan</label>
                <input type="text" wire:model="kawasan" class="mt-1 block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400">Tipe Penghuni</label>
                <select wire:model="tipePenghuni" class="mt-1 block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <option value="Campur">Campur</option>
                    <option value="Putra">Putra</option>
                    <option value="Putri">Putri</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400">Harga Aktual / Bln</label>
                <input type="number" wire:model="hargaAktual" class="mt-1 block w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>
            <div class="flex items-end">
                <button wire:click="tambahData" class="w-full py-2 px-3 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition">
                    + Tambahkan
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Data Latih -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Daftar Data Latih</h3>
            <div class="flex items-center gap-2">
                <select wire:model.live="filterSumber" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 py-1 px-2.5">
                    <option value="">Semua Sumber</option>
                    <option value="survei">Survei</option>
                    <option value="ngekos">Ngekos.in</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-750 text-gray-500 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Sumber</th>
                        <th class="px-4 py-3">Luas</th>
                        <th class="px-4 py-3">Jarak</th>
                        <th class="px-4 py-3">Kawasan</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Fasilitas</th>
                        <th class="px-4 py-3">Harga Aktual</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($daftarData as $d)
                        <tr class="{{ $d->ditandai_outlier ? 'bg-rose-50/50 dark:bg-rose-950/20' : 'hover:bg-gray-50/50 dark:hover:bg-gray-750/50' }}">
                            <td class="px-4 py-3 font-mono text-gray-400">#{{ $d->id }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $d->sumber === 'survei' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' }}">
                                    {{ strtoupper($d->sumber) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ $d->luas_m2 }} m²</td>
                            <td class="px-4 py-3">{{ $d->jarak_km }} km</td>
                            <td class="px-4 py-3">{{ $d->kawasan }}</td>
                            <td class="px-4 py-3">{{ $d->tipe_penghuni }}</td>
                            <td class="px-4 py-3 max-w-xs truncate">
                                {{ is_array($d->fasilitas) ? implode(', ', $d->fasilitas) : '-' }}
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100">
                                Rp{{ number_format($d->harga_aktual, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($d->ditandai_outlier)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-300 dark:bg-rose-950/50 dark:text-rose-300">
                                        OUTLIER
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/50 dark:text-teal-300">
                                        VALID
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <button wire:click="toggleOutlier({{ $d->id }})"
                                    class="rounded px-2 py-1 text-[11px] font-semibold transition {{ $d->ditandai_outlier ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                    {{ $d->ditandai_outlier ? 'Batalkan Outlier' : 'Tandai Outlier' }}
                                </button>
                                <button wire:click="hapusData({{ $d->id }})" wire:confirm="Hapus baris data ini?"
                                    class="rounded px-2 py-1 text-[11px] font-semibold text-gray-400 hover:text-rose-600 transition">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-gray-400">Belum ada data latih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($daftarData->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $daftarData->links() }}
            </div>
        @endif
    </div>
</div>
