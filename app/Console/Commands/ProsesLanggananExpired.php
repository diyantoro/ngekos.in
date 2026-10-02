<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProsesLanggananExpired extends Command
{
    protected $signature = 'app:proses-langganan-expired {--dry-run : Tampilkan yang akan diproses tanpa mengubah data}';

    protected $description = 'Tandai langganan kedaluwarsa sebagai expired + hapus (soft-delete) semua kamar pemiliknya';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $count = \App\Models\Subscription::where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count();
            $this->info("Dry-run: {$count} langganan akan diproses.");
            return self::SUCCESS;
        }

        $hasil = SubscriptionService::prosesExpired();

        $this->info("Langganan kedaluwarsa diproses: {$hasil['kedaluwarsa']}, kamar dihapus: {$hasil['kamar_dihapus']}.");

        return self::SUCCESS;
    }
}
