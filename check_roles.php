<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\App\Models\User::with('roles')->get()->each(function($u) {
    $role = $u->getRoleNames()->first() ?? 'none';
    echo "ID: {$u->id} | {$u->nama} | {$u->email} | Role: {$role} | Aktif: " . ($u->aktif() ? 'ya' : 'tidak') . PHP_EOL;
});