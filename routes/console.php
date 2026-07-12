<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reports:weekly-dean-email')
    ->weeklyOn(1, '8:00')
    ->timezone(config('app.timezone', 'Asia/Manila'))
    ->withoutOverlapping()
    ->onOneServer();
