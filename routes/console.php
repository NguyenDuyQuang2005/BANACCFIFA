<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Needs the server cron: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
*/
Schedule::command('products:prune-sold')
    ->dailyAt('03:30')
    ->timezone('Asia/Ho_Chi_Minh')
    ->withoutOverlapping()
    ->onOneServer();
