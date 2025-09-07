<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\BackController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    app(BackController::class)->getDataCreatorPartner();
})->everyMinute()->name('getDataCreatorPartner');

Schedule::call(function () {
    app(BackController::class)->getDataPadiUmkm();
})->everyMinute()->name('getDataPadiUmkm');

Schedule::call(function () {
    app(BackController::class)->getDataReferralChampionAm();
})->everyMinute()->name('getDataReferralChampionAm');

Schedule::call(function () {
    app(BackController::class)->getDataSimpatiTiktok();
})->everyMinute()->name('getDataSimpatiTiktok');

Schedule::call(function () {
    app(BackController::class)->getDataSultamRacing();
})->everyMinute()->name('getDataSultamRacing');