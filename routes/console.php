<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar backups 4 veces al día en horarios estratégicos de oficina
foreach (['08:00', '12:00', '15:00', '18:00'] as $time) {
    Schedule::command('backup:telegram')->at($time);
}


