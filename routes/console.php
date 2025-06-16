<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic cancellation of expired discoveries
Schedule::command('discoveries:cancel-expired')
    ->dailyAt('00:00')
    ->timezone('Europe/Istanbul')
    ->description('Cancel discoveries with expired offer dates');
