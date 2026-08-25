<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic stock synchronization with Dropi hourly
\Illuminate\Support\Facades\Schedule::command('dropi:sync-stock')->hourly();
