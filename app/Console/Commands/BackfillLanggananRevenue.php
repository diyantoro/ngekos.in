<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class BackfillLanggananRevenue extends Command
{
    protected $signature = 'app:backfill-langganan-revenue {--dry-run : tampilkan saja tanpa mengubah data}';

    protected $description = 'Sinkronkan revenue: approve pending berbukti, buatkan request approved untuk langganan aktif, perbaiki amount 0';

    public function handle(): int
    {
        $kering = (bool) $this->option('dry-run');
        $approve = 0;
        $buat = 0;
        $perbaiki = 0;

        SubscriptionRequest::where('status', 'pending')
            ->whereNotNull('bukti_path')
            ->whereNotNull('paid_at')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($kering, &$approve) {
                foreach ($rows as $req) {
                    if ($kering) {
                        $approve++;

                        continue;
                    }

                    try {
                        SubscriptionService::approveRequest($req, 30);
                        $approve++;
                    } catch (\Throwable $e) {
                        $this->warn("Lewati request #{$req->id}: {$e->getMessage()}");
                    }
                }
            });

        Subscription::whereIn('plan', ['pro', 'business'])
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>=', now())
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($kering, &$buat) {
                foreach ($rows as $sub) {
                    if (! $sub->isActive()) {
                        continue;
                    }

                    $ada = SubscriptionRequest::where('user_id', $sub->user_id)
                        ->where('requested_plan', $sub->plan)
                        ->where('status', 'approved')
                        ->exists();

                    if ($ada) {
                        continue;
                    }

                    if ($kering) {
                        $buat++;

                        continue;
                    }

                    SubscriptionRequest::create([
                        'user_id' => $sub->user_id,
                        'requested_plan' => $sub->plan,
                        'status' => 'approved',
                        'keterangan' => 'Backfill: langganan aktif tanpa request.',
                        'amount' => (int) config("plans.{$sub->plan}.price", 0),
                        'payment_method' => 'manual',
                        'paid_at' => $sub->created_at ?? now(),
                        'approved_at' => now(),
                    ]);
                    $buat++;
                }
            });

        SubscriptionRequest::where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('amount')->orWhere('amount', 0);
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($kering, &$perbaiki) {
                foreach ($rows as $req) {
                    $harga = (int) config("plans.{$req->requested_plan}.price", 0);

                    if ($harga <= 0) {
                        continue;
                    }

                    if ($kering) {
                        $perbaiki++;

                        continue;
                    }

                    $req->update(['amount' => $harga]);
                    $perbaiki++;
                }
            });

        $mode = $kering ? 'Dry-run' : 'Backfill';
        $this->info("{$mode}: approve {$approve}, request dibuat {$buat}, amount diperbaiki {$perbaiki}.");

        return self::SUCCESS;
    }
}
