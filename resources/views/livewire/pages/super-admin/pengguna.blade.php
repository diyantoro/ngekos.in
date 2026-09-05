<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $cari = '';

    public ?string $pesan = null;

    public ?string $galat = null;

    public function with(): array
    {
        return [
            'users' => User::with('roles')
                ->when($this->cari, fn ($q) => $q->where(fn ($q) => $q
                    ->where('nama', 'like', "%{$this->cari}%")
                    ->orWhere('email', 'like', "%{$this->cari}%")))
                ->orderBy('nama')
                ->get(),
        ];
    }

    public function toggleNonaktif(int $userId): void
    {
        $user = User::find($userId);

        if (! $user) {
            return;
        }

        if ($user->id === auth()->id()) {
            $this->galat = 'Anda tidak bisa menonaktifkan akun sendiri.';

            return;
        }

        if ($user->is_super_admin) {
            $this->galat = 'Akun Super Admin tidak bisa dinonaktifkan.';

            return;
        }

        if ($user->aktif()) {
            $user->update(['dinonaktifkan_pada' => now()]);
            $this->pesan = "Akun {$user->nama} dinonaktifkan. Sesi aktifnya akan otomatis keluar.";
        } else {
            $user->update(['dinonaktifkan_pada' => null]);
            $this->pesan = "Akun {$user->nama} diaktifkan kembali.";
        }
    }
}; ?>

<div class="py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Kelola Pengguna &amp; Peran</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Lihat peran pengguna atau nonaktifkan akun. Hanya Super Admin yang bisa mengakses halaman ini.
                Akun Super Admin lain tidak dapat diubah demi keamanan.
            </p>
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

        <input type="text" wire:model.live.debounce.300ms="cari" placeholder="Cari nama atau email..."
            class="w-full sm:w-72 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-teal-500 focus:border-teal-500">

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition" wire:key="user-{{ $user->id }}">
                                <td class="px-4 py-4">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $user->nama }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @foreach ($user->roles as $role)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $role->name === 'super_admin' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-500/30' : 'bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 ring-teal-200 dark:ring-teal-500/30' }}">
                                                {{ str($role->name)->replace('_', ' ')->title() }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($user->aktif())
                                        <x-status-badge status="aktif" />
                                    @else
                                        <x-status-badge status="nonaktif" />
                                        <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">{{ $user->dinonaktifkan_pada?->translatedFormat('d M Y, H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right">
                                    @if ($user->id === auth()->id())
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">Akun Anda</span>
                                    @elseif ($user->is_super_admin)
                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">&mdash;</span>
                                    @elseif ($user->aktif())
                                        <button type="button" wire:click="toggleNonaktif({{ $user->id }})"
                                            wire:confirm="Nonaktifkan akun {{ $user->nama }}? Sesi aktifnya akan langsung dikeluarkan."
                                            class="inline-flex items-center rounded-lg border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="button" wire:click="toggleNonaktif({{ $user->id }})"
                                            class="inline-flex items-center rounded-lg border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition">
                                            Aktifkan Kembali
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">Tidak ada pengguna yang cocok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
