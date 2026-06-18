<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\GenerateMonthlyRentPayments;
use App\Jobs\GenerateLatePaymentFines;
use Illuminate\Support\Facades\Schedule;

// Expire overdue agreements and vacate their units daily at midnight
Schedule::command('agreements:expire')->daily()->at('00:05');

Schedule::job(new GenerateMonthlyRentPayments)->weekdays()->at('00:10');
Schedule::job(new GenerateLatePaymentFines)->weekdays()->at('00:10');
