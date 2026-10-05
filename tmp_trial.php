<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\SubscriptionService;

$u = User::where('email', 'trialhabisx@test.id')->first();
if (!$u) {
    echo 'no';
    exit;
}
var_dump(SubscriptionService::trialExpired($u));
var_dump(SubscriptionService::trialAktif($u)?->id);
var_dump(SubscriptionService::getPlan($u));
var_dump(SubscriptionService::checkLimit($u, 'property'));
