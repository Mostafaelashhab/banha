<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hourly: delete expired stories + their images, archive past events, expire stale listings
Schedule::command('banha:cleanup')->hourly()->withoutOverlapping();
