<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bayupay:check-pending')->everyFiveMinutes()->withoutOverlapping()->runInBackground();


Schedule::command('documents:purge-temp-uploads')->daily();
 