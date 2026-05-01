<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-deactivate expired consignments. Runs every 4 hours.
Schedule::command('consignments:auto-deactivate')
    ->everyFourHours()
    ->withoutOverlapping(15)
    ->onOneServer()
    ->runInBackground();
