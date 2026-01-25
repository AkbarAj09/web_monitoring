<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\GetDataController;
use App\Http\Controllers\PanenPoinController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\LogbookDailyController;
use App\Http\Controllers\LeadsMasterController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::call(function () {
//     app(FrontController::class)->refreshSummarySimpatiTiktok();
// })->everyMinute()->name('refreshSummarySimpatiTiktok');
// Schedule::call(function () {
//     app(FrontController::class)->refreshSummaryPadiUmkm();
// })->everyMinute()->name('refreshSummaryPadiUmkm');

Schedule::call(function () {
    app(PanenPoinController::class)->refreshSummaryPanenPoin();
})->everyMinute()->name('refreshSummaryPanenPoin');


Schedule::call(function () {
    app(LogbookController::class)->refreshLogbookStatus();
})->everyMinute()->name('refreshLogbookStatus');

Schedule::call(function () {
    app(LogbookDailyController::class)->refreshLogbookDaily();
})->everyMinute()->name('refreshLogbookDaily');

Schedule::call(function () {
    app(LeadsMasterController::class)->syncLeadsWithRegistration();
})->everyTenMinutes()->name('syncLeadsWithRegistration');

Schedule::call(function () {
    app(LeadsMasterController::class)->syncLeadsWithRegional();
})->everyTenMinutes()->name('syncLeadsWithRegional');

Schedule::call(function () {
    app(LeadsMasterController::class)->refreshDetailLeadsSummary();
})->everyTwoMinutes()->name('refreshDetailLeadsSummary');

// Schedule::call(function () {
//     app(GetDataController::class)->getDataCreatorPartner();
// })->everyMinute()->name('getDataCreatorPartner');

// Schedule::call(function () {
//     app(GetDataController::class)->getDataPadiUmkm();
// })->everyMinute()->name('getDataPadiUmkm');

// Schedule::call(function () {
//     app(GetDataController::class)->getDataReferralChampionAm();
// })->everyMinute()->name('getDataReferralChampionAm');

// Schedule::call(function () {
//     app(GetDataController::class)->getDataSimpatiTiktok();
// })->everyMinute()->name('getDataSimpatiTiktok');

// Schedule::call(function () {
//     app(GetDataController::class)->getDataSultamRacing();
// })->everyMinute()->name('getDataSultamRacing');