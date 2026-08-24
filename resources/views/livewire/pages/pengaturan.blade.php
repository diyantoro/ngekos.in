<?php

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $tab = 'profil';

    public string $situsNama = '';

    public string $situsDeskripsi = '';

    public string $situsEmail = '';

    public string $situsTelepon = '';

    public string $situsAlamat = '';

    public string $kosJatuhTempo = 'akhir';

    public string $kosDendaPerHari = '0';

    public array $notifikasi = [];

    public ?string $pesan = null;

    public function mount(): void
    {
        if ($this->bolehKelola()) {
            $this->tab = 'situs';
            $this->situsNama = (string) Pengaturan::ambil('situs.nama', config('app.name', 'Ngekos.in'));
            $this->situsDeskripsi = (string) Pengaturan::ambil('situs.deskripsi', '');
            $this->situsEmail = (string) Pengaturan::ambil('situs.email', '');
            $this->situsTelepon = (string) Pengaturan::ambil('situs.telepon', '');
            $this->situsAlamat = (string) Pengaturan::ambil('situs.alamat', '');

            $jatuhTempo = (string) Pengaturan::ambil('kos.jatuh_tempo', 'akhir');
            $this->kosJatuhTempo = $jatuhTempo === 'akhir' ? 'akhir' : (string) max(1, min(28, (int) $jatuhTempo));
            $this->kosDendaPerHari = (string) Pengaturan::dendaPerHari();
        }

        foreach (array_keys(User::daftarNotifikasi()) as $kunci) {
            $this->notifikasi[$kunci] = Auth::user()->notif($kunci);
        }
    }

    public function bolehKelola(): bool
    {
        return Auth::user()->hasPermissionTo('konfigurasi.kelola');
    }

    public function simpanSitus(): void
    {
        abort_unless($this->bolehKelola(), 403);

        $this->validate([
            'situsNama' => 'required|string|max:100',
            'situsDeskripsi' => 'nullable|string|max:255',
            'situsEmail' => 'nullable|email|max:150',
            'situsTelepon' => 'nullable|string|max:30',
            'situsAlamat' => 'nullable|string|max:255',
        ], [], [
            'situsNama' => 'nama situs',
            'situsDeskripsi' => 'deskripsi',
            'situsEmail' => 'email',
            'situsTelepon' => 'telepon',
            'situsAlamat' => 'alamat',
        ]);

        Pengaturan::simpanBanyak([
            'situs.nama' => $this->situsNama,
            'situs.deskripsi' => $this->situsDeskripsi,
            'situs.email' => $this->situsEmail,
            'situs.telepon' => $this->situsTelepon,
            'situs.alamat' => $this->situsAlamat,
        ]);

        $this->pesan = 'Pengaturan situs berhasil disimpan.';
    }

    public function simpanKos(): void
    {
        abort_unless($this->bolehKelola(), 403);

        $this->validate([
            'kosJatuhTempo' => 'required|in:akhir,'.implode(',', range(1, 28)),
            'kosDendaPerHari' => 'required|numeric|min:0',
        ], [], [
            'kosJatuhTempo' => 'jatuh tempo tagihan',
            'kosDendaPerHari' => 'denda keterlambatan',
        ]);

        Pengaturan::simpanBanyak([
            'kos.jatuh_tempo' => $this->kosJatuhTempo,
            'kos.denda_per_hari' => number_format((float) $this->kosDendaPerHari, 2, '.', ''),
        ]);

        $this->pesan = 'Pengaturan kos berhasil disimpan.';
    }

    public function simpanNotifikasi(): void
    {
        $preferensi = [];

        foreach (array_keys(User::daftarNotifikasi()) as $kunci) {
            $preferensi[$kunci] = (bool) ($this->notifikasi[$kunci] ?? false);
        }

        Auth::user()->update(['preferensi_notifikasi' => $preferensi]);

        $this->pesan = 'Preferensi notifikasi berhasil disimpan.';
    }
}; ?>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <x-dashboard-greeting
            roleLabel="Pengaturan"
            description="Kelola konfigurasi aplikasi, keamanan akun, dan preferensi notifikasi."
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'
        />

        @if ($pesan)
            <x-notifikasi-popup :pesan="$pesan" judul="Berhasil!" />
        @endif

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 pb-3 border-b border-gray-100">
                <div class="flex flex-wrap gap-2">
                    @if ($this->bolehKelola())
                        <button wire:click="$set('tab', 'situs')" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'situs' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Situs
                        </button>
                        <button wire:click="$set('tab', 'kos')" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'kos' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Kos
                        </button>
                    @endif
                    <button wire:click="$set('tab', 'profil')" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'profil' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Profil &amp; Keamanan
                    </button>
                    <button wire:click="$set('tab', 'notifikasi')" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === 'notifikasi' ? 'bg-teal-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Notifikasi
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @if ($tab === 'situs' && $this->bolehKelola())
                    <form wire:submit="simpanSitus" class="max-w-2xl space-y-5">
                        <div>
                            <label for="situsNama" class="block text-sm font-medium text-gray-700">Nama Aplikasi</label>
                            <input type="text" id="situsNama" wire:model="situsNama"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('situsNama') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-400">Tampil di judul browser, logo, dan footer.</p>
                        </div>
                        <div>
                            <label for="situsDeskripsi" class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                            <textarea id="situsDeskripsi" wire:model="situsDeskripsi" rows="2"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500"></textarea>
                            @error('situsDeskripsi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-400">Muncul di footer halaman publik.</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="situsEmail" class="block text-sm font-medium text-gray-700">Email Kontak</label>
                                <input type="email" id="situsEmail" wire:model="situsEmail"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('situsEmail') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="situsTelepon" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                                <input type="text" id="situsTelepon" wire:model="situsTelepon"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                                @error('situsTelepon') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="situsAlamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <input type="text" id="situsAlamat" wire:model="situsAlamat"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('situsAlamat') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                Simpan Pengaturan Situs
                            </button>
                            <span wire:loading.delay class="text-xs text-gray-400">Menyimpan...</span>
                        </div>
                    </form>
                @elseif ($tab === 'kos' && $this->bolehKelola())
                    <form wire:submit="simpanKos" class="max-w-2xl space-y-5">
                        <div>
                            <label for="kosJatuhTempo" class="block text-sm font-medium text-gray-700">Tanggal Jatuh Tempo Tagihan</label>
                            <select id="kosJatuhTempo" wire:model="kosJatuhTempo"
                                class="mt-1 block w-full sm:w-64 rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                                <option value="akhir">Akhir bulan (default)</option>
                                @foreach (range(1, 28) as $hari)
                                    <option value="{{ $hari }}">Tanggal {{ $hari }} setiap bulan</option>
                                @endforeach
                            </select>
                            @error('kosJatuhTempo') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-400">Berlaku untuk tagihan baru yang dibuat sistem maupun saat check-in.</p>
                        </div>
                        <div>
                            <label for="kosDendaPerHari" class="block text-sm font-medium text-gray-700">Denda Keterlambatan Default (Rp/hari)</label>
                            <input type="number" id="kosDendaPerHari" wire:model="kosDendaPerHari" min="0" step="0.01"
                                class="mt-1 block w-full sm:w-64 rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
                            @error('kosDendaPerHari') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-400">Dipakai bila properti tidak menetapkan denda sendiri (pengaturan per properti tetap diutamakan).</p>
                        </div>
                        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                Simpan Pengaturan Kos
                            </button>
                            <span wire:loading.delay class="text-xs text-gray-400">Menyimpan...</span>
                        </div>
                    </form>
                @elseif ($tab === 'notifikasi')
                    <form wire:submit="simpanNotifikasi" class="max-w-2xl space-y-4">
                        @foreach (User::daftarNotifikasi() as $kunci => $label)
                            <label class="flex items-start gap-3 rounded-xl ring-1 ring-gray-100 bg-gray-50/60 p-4 cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" wire:model="notifikasi.{{ $kunci }}"
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span class="text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                        <p class="text-xs text-gray-400">Preferensi ini dipakai sebagai acuan pengiriman pemberitahuan aplikasi.</p>
                        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                            <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition disabled:opacity-50">
                                Simpan Preferensi
                            </button>
                            <span wire:loading.delay class="text-xs text-gray-400">Menyimpan...</span>
                        </div>
                    </form>
                @else
                    <div class="max-w-2xl space-y-6">
                        <livewire:profile.update-profile-information-form />
                        <livewire:profile.update-password-form />
                        <livewire:profile.delete-user-form />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
