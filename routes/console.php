<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\GenerateMonthlyRentPayments;
use App\Jobs\GenerateLatePaymentFines;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new GenerateMonthlyRentPayments)->weekdays()->at('00:00');
Schedule::job(new GenerateLatePaymentFines)->weekdays()->at('00:00');
