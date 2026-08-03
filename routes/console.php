<?php

use App\Modules\Task\Jobs\NotifyOverdueTasks;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new NotifyOverdueTasks)
    ->dailyAt('08:00')
    ->withoutOverlapping();
