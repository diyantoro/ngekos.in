<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $nama = '';
    public string $email = '';
    public string $no_hp = '';
    public $fotoProfil = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->nama = Auth::user()->nama;
        $this->email = Auth::user()->email;
        $this->no_hp = Auth::user()->no_hp ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'fotoProfil' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->fotoProfil) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $this->fotoProfil->store('avatar', 'public');
        }

        $user->fill(collect($validated)->except('fotoProfil')->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->reset('fotoProfil');

        $this->dispatch('profile-updated', nama: $user->nama);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Informasi Profil
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Perbarui informasi profil dan alamat email akun Anda.
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
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
                    if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
                    const img = document.getElementById('cropAvatar');
                    const buat = (Crop) => {
                        img.onload = () => {
                            if (this.cropper) this.cropper.destroy();
                            this.cropper = new Crop(img, { viewMode: 1, aspectRatio: 1, autoCropArea: 0.9 });
                        };
                        img.src = this.tempUrl;
                    };
                    if (window.Cropper) { buat(window.Cropper); return; }
                    if (typeof window.ensureCropper === 'function') { window.ensureCropper().then(buat).catch(() => {}); }
                });
            },
            batalCrop() {
                if (this.tempUrl) URL.revokeObjectURL(this.tempUrl);
                this.tempUrl = null;
                this.showCrop = false;
                if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
                const input = document.getElementById('fotoProfil');
                if (input) input.value = '';
            },
            terapkanCrop() {
                if (!this.cropper) return;
                const canvas = this.cropper.getCroppedCanvas({ maxWidth: 512, maxHeight: 512, imageSmoothingQuality: 'high' });
                const wire = this.$wire || null;
                canvas.toBlob((blob) => {
                    if (!blob) return;
                    const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                    if (wire) wire.upload('fotoProfil', file, () => this.batalCrop());
                }, 'image/jpeg', 0.92);
            }
        }">
            <x-input-label for="fotoProfil" value="Foto Profil" />
            <div class="mt-2 flex items-center gap-4">
                @if ($fotoProfil)
                    <img src="{{ $fotoProfil->temporaryUrl() }}" class="h-20 w-20 rounded-full object-cover ring-2 ring-teal-500" alt="Pratinjau foto profil">
                @else
                    <x-user-avatar :user="auth()->user()" size="xl" />
                @endif
                <div>
                    <label for="fotoProfil" class="inline-flex cursor-pointer items-center rounded-lg bg-gray-100 dark:bg-gray-700 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Pilih foto baru
                    </label>
                    @if ($fotoProfil)
                        <button type="button" wire:click="$set('fotoProfil', null)" class="ml-2 text-xs font-medium text-rose-600 dark:text-rose-400 hover:underline">Batalkan</button>
                    @endif
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Wajib potong kotak 1:1 dulu sebelum disimpan.</p>
                </div>
            </div>
            <input type="file" id="fotoProfil" accept="image/*" @change="onSelect($event)" class="sr-only">
            <x-input-error class="mt-2" :messages="$errors->get('fotoProfil')" />
            <div wire:loading wire:target="fotoProfil" class="mt-2 text-xs font-medium text-teal-600 dark:text-teal-400">Mengunggah foto...</div>

            {{-- Modal Crop Avatar (kotak 1:1) --}}
            <div x-show="showCrop" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70">
                <div class="w-full max-w-lg max-h-[90vh] flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Potong Foto Profil</h3>
                        <button type="button" @click="batalCrop" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-hidden bg-gray-100 dark:bg-gray-900 flex items-center justify-center p-4">
                        <img id="cropAvatar" src="" alt="Pratinjau crop" class="block max-h-[55vh] max-w-full">
                    </div>
                    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-3">
                        <button type="button" @click="batalCrop" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100">Batal</button>
                        <button type="button" @click="terapkanCrop" class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500 transition">Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="nama" :value="__('Nama Lengkap')" />
            <x-text-input wire:model="nama" id="nama" name="nama" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('nama')" />
        </div>

        <div>
            <x-input-label for="no_hp" :value="__('No. HP')" />
            <x-text-input wire:model="no_hp" id="no_hp" name="no_hp" type="text" class="mt-1 block w-full" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        Alamat email Anda belum diverifikasi.

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            Tautan verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                Tersimpan.
            </x-action-message>
        </div>
    </form>
</section>
