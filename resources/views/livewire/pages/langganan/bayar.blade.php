<?php

use App\Models\Pengaturan;
use App\Models\SubscriptionRequest;
use App\Services\BuktiStorage;
use App\Services\LanggananNotifier;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public string $plan = 'pro';

    public $bukti;

    public ?string $galat = null;

    public function mount(): void
    {
        if (! auth()->user()?->hasRole('pemilik')) {
            $this->redirect(route('dashboard'));
        }

        if (! in_array($this->plan, ['pro', 'business'], true)) {
            $this->redirect(route('langganan.plans'));
        }
    }

    public function with(): array
    {
        $qris = (string) Pengaturan::ambil('pay.qris', '');
        $qrisImage = (string) Pengaturan::ambil('pay.qris_image', '');
        $harga = (int) config("plans.{$this->plan}.price", 0);

        return [
            'plan' => $this->plan,
            'nama' => (string) data_get(config('plans'), "{$this->plan}.name"),
            'harga' => $harga,
            'qris' => $qris,
            'qrisImage' => $qrisImage,
            'qrisAktif' => $qris !== '' || $qrisImage !== '',
            'petunjuk' => (string) Pengaturan::ambil('pay.petunjuk', ''),
        ];
    }

    public function bayar(): void
    {
        $user = auth()->user();

        if (! $user?->hasRole('pemilik')) {
            $this->redirect(route('dashboard'));

            return;
        }

        $this->validate([
            'plan' => ['required', 'in:pro,business'],
            'bukti' => ['required', 'image', 'max:2048'],
        ], [], [
            'bukti' => 'bukti pembayaran',
        ]);

        // Nominal dikunci server-side sesuai harga paket, tidak bisa diubah user.
        $harga = (int) config("plans.{$this->plan}.price", 0);

        // Cek pending SEBELUM simpan file agar tidak ada file orphan
        // dan input file tidak ikut ter-reset sia-sia.
        $adaPending = SubscriptionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($adaPending) {
            $this->galat = 'Masih ada permintaan upgrade yang menunggu persetujuan admin.';

            return;
        }

        $path = BuktiStorage::simpan($this->bukti, 'bukti');

        try {
            $permintaan = SubscriptionService::requestUpgrade($user, $this->plan, null, [
                'amount' => $harga,
                'payment_method' => 'qris',
                'bukti_path' => $path,
            ]);

            // Menunggu verifikasi: paket BARU aktif setelah disetujui superadmin.
            // Notifikasi ke admin tetap dikirim agar ajuan segera diproses.
            try {
                LanggananNotifier::sebarkanPembayaranBaru($permintaan);
            } catch (\Throwable $e) {
                report($e);
            }
        } catch (\InvalidArgumentException $e) {
            // Hapus file yang terlanjur tersimpan agar tidak jadi sampah.
            // Jangan reset('bukti') agar file pilihan user tidak hilang
            // dan tidak perlu pilih ulang.
            BuktiStorage::hapus($path);
            $this->galat = $e->getMessage();

            return;
        }

        $this->redirect(route('langganan.plans'));
    }
}; ?>

<div class="py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Bayar {{ $nama }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Scan QRIS sebesar nominal di bawah lalu unggah bukti pembayaran. Paket aktif setelah disetujui superadmin.</p>
        </div>

        @if ($galat)
            <div class="flex items-center justify-between gap-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 ring-1 ring-rose-200 dark:ring-rose-500/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                <span>{{ $galat }}</span>
                <button wire:click="$set('galat', null)" class="font-bold">&times;</button>
            </div>
        @endif

        <div class="card p-6 text-center">
            <p class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Rp{{ number_format($harga, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Paket {{ $nama }} · 1 bulan · via QRIS</p>
        </div>

        @if (! $qrisAktif)
            <div class="flex items-start gap-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 ring-1 ring-amber-200 dark:ring-amber-500/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
                <span>Admin belum mengatur pembayaran QRIS. Hubungi admin untuk mengaktifkannya di Pengaturan → Pembayaran.</span>
            </div>
        @else
            <div class="card p-6 space-y-4">
                <div class="flex flex-col items-center gap-3">
                    @if ($qrisImage)
                        <img id="gambarQris" src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS {{ $nama }}"
                            class="h-52 w-52 rounded-xl object-contain ring-1 ring-gray-200 dark:ring-gray-700 bg-white p-2">
                    @else
                        <img id="gambarQris" src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=12&data={{ urlencode($qris) }}"
                            alt="QRIS {{ $nama }}"
                            class="h-52 w-52 rounded-xl ring-1 ring-gray-200 dark:ring-gray-700 bg-white p-2"
                            loading="lazy">
                    @endif
                    <a id="unduhQrisBtn" href="{{ route('langganan.qris-unduh', ['plan' => $plan]) }}" download
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-500 transition active:scale-[0.98] shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Unduh QRIS
                    </a>
                    <p class="text-xs text-gray-400">Bayar tepat <span class="font-bold text-gray-700 dark:text-gray-200">Rp{{ number_format($harga, 0, ',', '.') }}</span> sesuai nominal di atas.</p>
                </div>

                @if ($petunjuk)
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $petunjuk }}</p>
                @else
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">Setelah membayar, unggah bukti di bawah ini.</p>
                @endif
            </div>

            <form wire:submit="bayar" class="card p-6 space-y-4">
                <div>
                    <x-input-label for="bukti" value="Bukti Pembayaran" />
                    <input type="file" id="bukti" wire:model="bukti" accept="image/*"
                        class="mt-2 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-600 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-teal-500">
                    <x-input-error :messages="$errors->get('bukti')" class="mt-2" />
                    <div wire:loading wire:target="bukti" class="mt-2 text-xs font-semibold text-amber-600 dark:text-amber-400">Mengunggah... tunggu sampai selesai sebelum klik Kirim.</div>
                    @if ($bukti)
                        <p class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">File terpilih: {{ method_exists($bukti, 'getClientOriginalName') ? $bukti->getClientOriginalName() : 'bukti pembayaran' }}. Pastikan pratinjau di bawah muncul sebelum klik Kirim.</p>
                        @if (method_exists($bukti, 'temporaryUrl'))
                            <img src="{{ $bukti->temporaryUrl() }}" alt="Pratinjau bukti" class="mt-2 h-32 w-auto rounded-xl object-contain ring-1 ring-gray-200 dark:ring-gray-700 bg-white p-1">
                        @endif
                    @endif
                </div>
                <button type="submit" wire:loading.attr="disabled" wire:target="bukti,bayar"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-5 py-3 text-sm font-bold text-white hover:bg-teal-500 transition disabled:opacity-50 active:scale-[0.98]">
                    <span wire:loading.remove wire:target="bukti,bayar">Kirim Pembayaran</span>
                    <span wire:loading wire:target="bukti">Mengunggah bukti...</span>
                    <span wire:loading wire:target="bayar">Mengirim...</span>
                </button>
                <p class="text-center text-xs text-gray-400">Setelah terkirim, tunggu persetujuan superadmin (status: Menunggu Persetujuan).</p>
            </form>
        @endif

        <div class="text-center">
            <a href="{{ route('langganan.plans') }}" wire:navigate class="text-sm font-semibold text-teal-600 dark:text-teal-400 hover:underline">← Kembali ke daftar paket</a>
        </div>
    </div>
</div>