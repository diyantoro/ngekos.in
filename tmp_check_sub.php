<?php

use App\Models\Subscription;

$subs = Subscription::with('user:id,nama')->latest('id')->limit(20)->get();
foreach ($subs as $s) {
    echo implode(' | ', [
        $s->id,
        $s->user?->nama,
        $s->plan,
        $s->status,
        'trial='.(int) $s->is_trial,
        'start='.$s->starts_at,
        'exp='.$s->expires_at,
        'aktif='.($s->isActive() ? 'ya' : 'tidak'),
    ]).PHP_EOL;
}