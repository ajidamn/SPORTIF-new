<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Backup Database Otomatis harian
Schedule::command('backup:run --only-db')->dailyAt('02:00');

// Backup Full (DB + Files) mingguan
Schedule::command('backup:run')->weekly()->sundays()->at('03:00');

// Bersihkan backup lama
Schedule::command('backup:clean')->dailyAt('01:00');

// Monitor kesehatan backup
Schedule::command('backup:monitor')->dailyAt('06:00');
