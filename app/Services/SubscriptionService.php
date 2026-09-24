<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\Properti;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Models\User;

class SubscriptionService
{
    /**
     * Fitur PRO yang ikut terbuka selama trial Free 7 hari yang diklaim.
     * Trial = PRO penuh agar pemilik merasakan nilai paket berbayar.
     */
    public const FITUR_TRIAL_PRO = [
        'advanced_analytics',
        'advanced_report',
        'export_report',
    ];
    public static function getSubscription(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)->latest('id')->first();
    }

    public static function getPlan(User $user): string
    {
        $subscription = self::getSubscription($user);

        if ($subscription && $subscription->isActive()) {
            return $subscription->plan;
        }

        return 'free';
    }

    public static function isPremium(User $user): bool
    {
        return in_array(self::getPlan($user), ['pro', 'business'], true);
    }

    public static function hasFeature(User $user, string $feature): bool
    {
        $plan = self::getPlan($user);
        $plans = config('plans', []);

        if (in_array($feature, $plans[$plan]['features'] ?? [], true)) {
            return true;
        }

        // Trial yang diklaim = PRO penuh.
        if (self::trialAktif($user) !== null && in_array($feature, self::FITUR_TRIAL_PRO, true)) {
            return true;
        }

        if ($plan === 'business' && in_array($feature, $plans['pro']['features'] ?? [], true)) {
            return true;
        }

        if (in_array($plan, ['pro', 'business'], true) && in_array($feature, $plans['free']['features'] ?? [], true)) {
            return true;
        }

        return false;
    }

    public static function limitFor(string $plan, string $resource): ?int
    {
        $key = match ($resource) {
            'property', 'properties', 'properti' => 'properties',
            'room', 'rooms', 'kamar' => 'rooms',
            default => $resource,
        };

        return config("plans.{$plan}.limits.{$key}");
    }

    public static function usage(User $user, string $resource): int
    {
        return match ($resource) {
            'property', 'properties', 'properti' => Properti::where('pemilik_id', $user->id)->count(),
            'room', 'rooms', 'kamar' => Kamar::whereHas('properti', fn ($q) => $q->where('pemilik_id', $user->id))->count(),
            default => 0,
        };
    }

    public static function isExempt(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }

    public static function getFeatureRequiredPlan(string $feature): ?string
    {
        $plans = config('plans', []);

        if (in_array($feature, $plans['free']['features'] ?? [], true)) {
            return null;
        }

        if (in_array($feature, $plans['pro']['features'] ?? [], true)) {
            return 'pro';
        }

        if (in_array($feature, $plans['business']['features'] ?? [], true)) {
            return 'business';
        }

        return null;
    }

    public static function featureCheck(User $user, string $feature): array
    {
        if (self::isExempt($user)) {
            return ['allowed' => true, 'plan' => self::getPlan($user), 'required_plan' => null, 'message' => null];
        }

        if (self::hasFeature($user, $feature)) {
            return ['allowed' => true, 'plan' => self::getPlan($user), 'required_plan' => null, 'message' => null];
        }

        $required = self::getFeatureRequiredPlan($feature) ?? 'pro';
        $planName = strtoupper($required);

        return [
            'allowed' => false,
            'plan' => self::getPlan($user),
            'required_plan' => $required,
            'message' => "Fitur ini membutuhkan paket {$planName}.",
        ];
    }

    public static function requireFeature(User $user, string $feature): void
    {
        $cek = self::featureCheck($user, $feature);

        if (! $cek['allowed']) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json([
                    'message' => $cek['message'],
                    'required_plan' => $cek['required_plan'],
                ], 403)
            );
        }
    }

    /**
     * Total revenue platform (rupiah) dari upgrade paket yang disetujui.
     * Satu-satunya nominal tercatat di subscription_requests.amount;
     * baris tanpa amount dihitung 0. Status selain approved diabaikan.
     */
    public static function platformRevenue(): int
    {
        return (int) (SubscriptionRequest::where('status', 'approved')->sum('amount') ?? 0);
    }

    public static function jumlahUpgradeApproved(): int
    {
        return (int) SubscriptionRequest::where('status', 'approved')->count();
    }

    /**
     * Konversi premium: pemilik berlangganan PRO/Business aktif
     * dibagi total pemilik. Trial Free tidak dihitung premium.
     *
     * @return array{total_pemilik:int, premium_aktif:int, persen:float}
     */
    public static function konversiPremium(): array
    {
        $pemilikIds = User::role('pemilik')->pluck('id');
        $total = $pemilikIds->count();

        if ($total === 0) {
            return ['total_pemilik' => 0, 'premium_aktif' => 0, 'persen' => 0.0];
        }

        $premium = Subscription::whereIn('user_id', $pemilikIds)
            ->whereIn('plan', ['pro', 'business'])
            ->where('status', 'active')
            ->get()
            ->filter(fn (Subscription $s) => $s->isActive())
            ->pluck('user_id')
            ->unique()
            ->count();

        return [
            'total_pemilik' => $total,
            'premium_aktif' => $premium,
            'persen' => round($premium / $total * 100, 1),
        ];
    }

    public static function history(User $user, int $limit = 20)
    {
        return Subscription::where('user_id', $user->id)->latest()->limit($limit)->get();
    }

    public static function trialDays(): int
    {
        return (int) config('plans.trial_days', 7);
    }

    public static function pernahTrial(User $user): bool
    {
        return Subscription::where('user_id', $user->id)->where('is_trial', true)->exists();
    }

    public static function trialAktif(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)
            ->where('is_trial', true)
            ->latest('id')
            ->get()
            ->first(fn (Subscription $s) => $s->isActive() && $s->plan === 'free');
    }

    public static function sisaTrialHari(User $user): ?int
    {
        if (self::isExempt($user)) {
            return null;
        }

        // Trial hanya relevan selama paket masih free.
        // Begitu upgrade ke PRO/Business, sisa trial dianggap tidak ada.
        if (self::getPlan($user) !== 'free') {
            return null;
        }

        $trial = self::trialAktif($user);

        if (! $trial || ! $trial->expires_at) {
            return null;
        }

        return max(0, (int) ceil(now()->floatDiffInDays($trial->expires_at)));
    }

    public static function trialExpired(User $user): bool
    {
        if (self::isExempt($user)) {
            return false;
        }

        // Masih masa coba hanya bila paket masih free DAN ada trial aktif.
        // Begitu upgrade ke PRO/Business (atau trial kedaluwarsa),
        // masa trial dianggap habis.
        if (self::getPlan($user) === 'free' && self::trialAktif($user) !== null) {
            return false;
        }

        return true;
    }

    /**
     * Halaman Laporan (Grafik & Rekap) dikunci bila paket free dan masa coba habis.
     * Data tidak dihapus, tapi konten analitik + ekspor rekap tidak bisa dibuka
     * sampai upgrade ke PRO/Business. Admin/super_admin selalu lolos.
     */
    public static function laporanDikunci(User $user): bool
    {
        if (self::isExempt($user)) {
            return false;
        }

        return self::getPlan($user) === 'free' && self::trialExpired($user);
    }

    public static function cekLaporan(User $user): array
    {
        if (self::isExempt($user)) {
            return ['allowed' => true, 'plan' => self::getPlan($user), 'required_plan' => null, 'message' => null];
        }

        if (self::laporanDikunci($user)) {
            return [
                'allowed' => false,
                'plan' => 'free',
                'required_plan' => 'pro',
                'message' => 'Masa coba 7 hari sudah habis atau belum diklaim. Data tidak hilang, tapi halaman Laporan dikunci.',
            ];
        }

        return ['allowed' => true, 'plan' => self::getPlan($user), 'required_plan' => null, 'message' => null];
    }

    public static function mulaiTrialFree(User $user): ?Subscription
    {
        if (self::pernahTrial($user)) {
            return null;
        }

        // Jangan mulai trial baru bila sudah PRO/Business aktif.
        if (self::getPlan($user) !== 'free') {
            return null;
        }

        $hari = self::trialDays();

        return Subscription::create([
            'user_id' => $user->id,
            'plan' => 'free',
            'status' => 'active',
            'is_trial' => true,
            'starts_at' => now(),
            'expires_at' => now()->addDays($hari),
        ]);
    }

    /**
     * Apakah user boleh mengklaim trial 7 hari sekarang?
     * Syarat: pemilik, bukan exempt, masih free, belum pernah trial, tidak ada trial aktif.
     */
    public static function bisaKlaimTrial(User $user): bool
    {
        if (self::isExempt($user)) {
            return false;
        }

        if (! $user->hasRole('pemilik')) {
            return false;
        }

        if (self::getPlan($user) !== 'free') {
            return false;
        }

        if (self::pernahTrial($user)) {
            return false;
        }

        return self::trialAktif($user) === null;
    }

    /**
     * Klaim trial 7 hari (sekali per akun). Return null bila tidak eligible.
     */
    public static function klaimTrialFree(User $user): ?Subscription
    {
        if (! self::bisaKlaimTrial($user)) {
            return null;
        }

        return self::mulaiTrialFree($user);
    }

    public static function reportTier(User $user): string
    {
        if (self::isExempt($user)) {
            return 'business';
        }

        return match (self::getPlan($user)) {
            'business' => 'business',
            'pro' => 'pro',
            // Trial klaim = tier PRO agar laporan premium + 12 bulan terbuka.
            default => self::trialAktif($user) !== null ? 'pro' : 'basic',
        };
    }

    public static function reportPlanKey(User $user): string
    {
        if (self::isExempt($user)) {
            return 'business';
        }

        return match (self::getPlan($user)) {
            'business' => 'business',
            'pro' => 'pro',
            // Trial klaim memakai batas & sheet paket PRO.
            default => self::trialAktif($user) !== null ? 'pro' : 'free',
        };
    }

    public static function maxPeriode(User $user): int
    {
        $key = self::reportPlanKey($user);

        return (int) (config("plans.{$key}.report.max_periode")
            ?? match ($key) {
                'business' => 24,
                'pro' => 12,
                default => 3,
            });
    }

    public static function clampPeriode(User $user, int $minta): int
    {
        return max(1, min(self::maxPeriode($user), $minta));
    }

    public static function canExportPdf(User $user): bool
    {
        if (self::isExempt($user)) {
            return true;
        }

        return (bool) (config('plans.'.self::reportPlanKey($user).'.report.pdf', true));
    }

    public static function canExportExcel(User $user): bool
    {
        if (self::isExempt($user)) {
            return true;
        }

        return (bool) (config('plans.'.self::reportPlanKey($user).'.report.excel', true));
    }

    public static function perluWatermark(User $user): bool
    {
        if (self::isExempt($user)) {
            return false;
        }

        // Selama trial klaim aktif, tanpa watermark seperti paket PRO.
        if (self::trialAktif($user) !== null) {
            return false;
        }

        return self::getPlan($user) === 'free';
    }

    /**
     * Simpan langganan baru untuk user (dipakai Super Admin web & API).
     * Setiap penyimpanan menjadi satu baris => tercatat di subscription history.
     *
     * Periode yang "real": langganan aktif wajib punya tanggal mulai &
     * berakhir yang logis (berakhir setelah mulai, durasi maks 366 hari,
     * mulai maks 30 hari ke depan). Tanpa ini periode sembarangan bisa
     * tersimpan dan merusak status paket pemilik.
     */
    public static function store(int $userId, array $attrs): Subscription
    {
        if (! in_array($attrs['plan'] ?? null, ['free', 'pro', 'business'], true)) {
            throw new \InvalidArgumentException('Plan tidak valid.');
        }

        if (! in_array($attrs['status'] ?? null, ['active', 'expired', 'cancelled'], true)) {
            throw new \InvalidArgumentException('Status tidak valid.');
        }

        if (($attrs['status'] ?? null) === 'active') {
            try {
                $mulai = ! empty($attrs['starts_at']) ? \Carbon\Carbon::parse($attrs['starts_at']) : null;
                $akhir = ! empty($attrs['expires_at']) ? \Carbon\Carbon::parse($attrs['expires_at']) : null;
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException('Tanggal periode tidak valid.');
            }

            if (! $mulai || ! $akhir) {
                throw new \InvalidArgumentException('Langganan aktif wajib punya tanggal mulai & berakhir.');
            }

            if ($akhir->lte($mulai)) {
                throw new \InvalidArgumentException('Tanggal berakhir harus setelah tanggal mulai.');
            }

            if ($mulai->diffInDays($akhir) > 366) {
                throw new \InvalidArgumentException('Durasi langganan maksimal 366 hari.');
            }

            if ($mulai->gt(now()->addDays(30))) {
                throw new \InvalidArgumentException('Tanggal mulai maksimal 30 hari ke depan.');
            }
        }

        return Subscription::create([
            'user_id' => $userId,
            'plan' => $attrs['plan'],
            'status' => $attrs['status'],
            'is_trial' => (bool) ($attrs['is_trial'] ?? false),
            'starts_at' => $attrs['starts_at'] ?? null,
            'expires_at' => $attrs['expires_at'] ?? null,
        ]);
    }

    /**
     * Perpanjang langganan aktif (atau aktifkan kembali) N hari dari tanggal
     * kedaluwarsa saat ini bila masih di masa depan, selain itu dari hari ini.
     */
    public static function perpanjang(Subscription $subscription, int $hari = 30): Subscription
    {
        $basis = $subscription->expires_at && $subscription->expires_at->isFuture()
            ? $subscription->expires_at
            : now();

        $subscription->update([
            'status' => 'active',
            'starts_at' => $subscription->starts_at ?? now(),
            'expires_at' => $basis->copy()->addDays($hari),
        ]);

        return $subscription;
    }

    /**
     * Akhiri langganan (status expired). Data user & langganan tetap tersimpan.
     */
    public static function akhiri(Subscription $subscription): Subscription
    {
        $subscription->update(['status' => 'expired']);

        return $subscription;
    }

    /**
     * Pemilik mengajukan upgrade ke paket pro/business. Wajib diverifikasi
     * (approve/reject) oleh Super Admin sebelum mendapat akses premium.
     */
    public static function requestUpgrade(User $user, string $plan, ?string $keterangan = null, array $data = []): SubscriptionRequest
    {
        if (! in_array($plan, ['pro', 'business'], true)) {
            throw new \InvalidArgumentException('Paket tujuan tidak valid.');
        }

        $pending = SubscriptionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            throw new \InvalidArgumentException('Masih ada permintaan upgrade yang menunggu persetujuan admin.');
        }

        return SubscriptionRequest::create([
            'user_id' => $user->id,
            'requested_plan' => $plan,
            'status' => 'pending',
            'keterangan' => $keterangan,
            'amount' => $data['amount'] ?? null,
            'payment_method' => $data['payment_method'] ?? 'qris',
            'bukti_path' => $data['bukti_path'] ?? null,
            'paid_at' => $data['paid_at'] ?? null,
        ]);
    }

    /**
     * Setujui permintaan upgrade: buat langganan aktif baru (menjadi riwayat)
     * lalu tandai permintaan sebagai approved.
     */
    public static function approveRequest(SubscriptionRequest $request, int $hari = 30): void
    {
        if ($request->status !== 'pending') {
            throw new \InvalidArgumentException('Permintaan ini sudah diproses.');
        }

        self::store($request->user_id, [
            'plan' => $request->requested_plan,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays($hari),
        ]);

        // Upgrade ke PRO/Business mengakhiri masa trial: tandai trial aktif lama sebagai expired
        // agar trialAktif() null dan trialExpired() true.
        Subscription::where('user_id', $request->user_id)
            ->where('is_trial', true)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $request->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Tolak permintaan upgrade: user tetap pada paket sebelumnya.
     */
    public static function rejectRequest(SubscriptionRequest $request): void
    {
        if ($request->status !== 'pending') {
            throw new \InvalidArgumentException('Permintaan ini sudah diproses.');
        }

        $request->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    public static function checkLimit(User $user, string $resource): array
    {
        if (self::isExempt($user)) {
            return ['allowed' => true, 'used' => 0, 'limit' => null, 'plan' => self::getPlan($user), 'required_plan' => null, 'message' => null];
        }

        $plan = self::getPlan($user);
        $limit = self::limitFor($plan, $resource);
        $used = self::usage($user, $resource);

        // Free murni tetap boleh tambah sampai batas paket (1 properti / 10 kamar)
        // walau belum/tidak klaim trial. Trial hanya membuka fitur premium,
        // bukan syarat tambah dasar. Habis trial -> kembali ke batas Free.

        if ($limit === null) {
            return ['allowed' => true, 'used' => $used, 'limit' => null, 'plan' => $plan, 'required_plan' => null, 'message' => null];
        }

        if ($used < $limit) {
            return ['allowed' => true, 'used' => $used, 'limit' => $limit, 'plan' => $plan, 'required_plan' => null, 'message' => null];
        }

        $required = $plan === 'free' ? 'pro' : 'business';
        $label = match ($resource) {
            'property', 'properties', 'properti' => 'properti',
            default => 'kamar',
        };
        $planName = config("plans.{$plan}.name", ucfirst($plan));

        return [
            'allowed' => false,
            'used' => $used,
            'limit' => $limit,
            'plan' => $plan,
            'required_plan' => $required,
            'message' => "Batas {$label} paket {$planName} telah tercapai.",
        ];
    }
}
