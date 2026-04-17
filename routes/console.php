<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('storage:cleanup')->daily()->at('04:00');

// 0 и меньше — автозапуск выключен.
$hours = (int) config('gamemag.schedule_every_hours', 1);
if ($hours > 0) {
    $event = Schedule::command('gamemag:import-news')
        ->withoutOverlapping()
        ->runInBackground();

    if ($hours === 1) {
        $event->hourly();
    } elseif ($hours >= 24) {
        $event->daily();
    } else {
        $event->cron('0 */' . $hours . ' * * *');
    }
}
