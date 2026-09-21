<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class BackfillTrialFree extends Command
{
    protected $signature = 'app:backfill-trial-free {--dry-run : tampilkan saja tanpa menyimpan}';

    protected $description = 'Buat trial free 7 hari untuk pemilik lama tanpa riwayat subscription (sekali saja)';

    public function handle(): int
    {
        $kering = (bool) $this->option('dry-run');
        $dibuat = 0;
        $dilewati = 0;

        User::role('pemilik')->chunkById(200, function ($users) use ($kering, &$dibuat, &$dilewati) {
            foreach ($users as $user) {
                if (SubscriptionService::pernahTrial($user) || SubscriptionService::history($user)->isNotEmpty()) {
                    $dilewati++;

                    continue;
                }

                if ($kering) {
                    $dibuat++;

                    continue;
                }

                if (SubscriptionService::mulaiTrialFree($user)) {
                    $dibuat++;
                } else {
                    $dilewati++;
                }
            }
        });

        $this->info("Trial dibuat: {$dibuat}, dilewati: {$dilewati}.");

        return self::SUCCESS;
    }
}
