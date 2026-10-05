<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Subscription;
use App\Models\User;

$u = User::where('email', 'trialhabisx@test.id')->first();
if (!$u) {
    var_dump('no');
    exit;
}
var_dump($u->id);
Subscription::where('user_id', $u->id)->get()->each(function ($s) {
    echo $s->id.' plan='.$s->plan.' status='.$s->status.' trial='.$s->is_trial.' exp='.$s->expires_at.PHP_EOL;
});
