<?php

use Illuminate\Support\Facades\Schedule;

// Tagihan bulanan + denda keterlambatan, dijalankan tiap hari.
Schedule::command('app:proses-tagihan')->dailyAt('00:05');
