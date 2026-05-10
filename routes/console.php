<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hourly: delete expired stories + their images, archive past events, expire stale listings
Schedule::command('banha:cleanup')->hourly()->withoutOverlapping();

// Every minute: check if a prayer time just hit; push to opted-in users
Schedule::command('banha:send-prayer-pushes')->everyMinute()->withoutOverlapping();

// Weekly: refresh city-app imports in safe chunks (skips already-imported slugs).
// On shared hosting where each request is short-lived, a small chunk keeps it inside the time budget.
Schedule::command('scrape:cityapp --skip-existing --limit=50 --sleep=400')
    ->weeklyOn(1, '03:00')      // Mondays at 3am local
    ->withoutOverlapping()
    ->runInBackground();
