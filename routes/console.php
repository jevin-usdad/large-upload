<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:database-backup')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
