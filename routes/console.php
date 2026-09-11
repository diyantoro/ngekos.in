<?php

use Illuminate\Support\Facades\Schedule;

// Tagihan bulanan + denda keterlambatan, dijalankan tiap hari.
Schedule::command('app:proses-tagihan')->dailyAt('00:05');

// Pengingat H-3 / H-1 / H0 + peringatan telat harian via chat + push + email.
Schedule::command('app:kirim-pengingat-tagihan')->dailyAt('07:00');
