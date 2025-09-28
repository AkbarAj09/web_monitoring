<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\GetDataController;

Route::get('/', [FrontController::class, 'index'])->name('home');
Route::post('/login', [BackController::class, 'login'])->name('login');
Route::get('/register', [FrontController::class, 'register'])->name('register');
Route::post('/register-simpan', [BackController::class, 'registerStore'])->name('register.store');

Route::get('/get-data-rahasia-bisnis-kuesioner', [GetDataController::class, 'getDataRahasiaBisnis']);
Route::get('/get-data-padi-umkm', [GetDataController::class, 'getDataPadiUmkm']);
Route::get('/get-data-creator-partner', [GetDataController::class, 'getDataCreatorPartner']);
// routes/web.php
Route::get('/get-regionals', [FrontController::class, 'getRegionals'])->name('get_regionals');

Route::get('/get-data-simpati-tiktok', [GetDataController::class, 'getDataSimpatiTiktok']);
Route::get('/get-data-referral-champion-am', [GetDataController::class, 'getDataReferralChampionAm']);
Route::get('/get-data-sultam-racing', [GetDataController::class, 'getDataSultamRacing']);
Route::get('/get-data-event-sponsorship', [GetDataController::class, 'getDataEventSponsorhip']);
Route::get('/get-data-rekruter-kol', [GetDataController::class, 'getDataRekruterKol']);

Route::get('/summary-padi-umkm', [FrontController::class, 'refreshSummaryPadiUmkm']);
Route::get('/summary-tiktok', [FrontController::class, 'refreshSummarySimpatiTiktok']);


Route::get('/logout', [FrontController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'checkrole:Admin'])->group(function () {
    Route::get('/admin/home', [FrontController::class, 'homeAdmin'])->name('admin.home');

    Route::get('/upload-file-myads', [FrontController::class, 'uploadMyAds'])->name('admin.upload');
    Route::post('/store-csv-myads', [BackController::class, 'storeUploadMyAds'])->name('upload.myads.store');
    Route::post('/get-table-name', [BackController::class, 'getTableName'])->name('upload.myads.getTableName');
    Route::get('/download-format/{table}', [BackController::class, 'downloadFormat'])->name('upload.myads.downloadFormat');

    // Padi UMKM
    Route::get('/monitoring-padi-umkm', [FrontController::class, 'monitoring_padi_umkm'])->name('admin.monitoring.padi_umkm');
    Route::get('/get-padi-umkm-data', [BackController::class, 'getPadiUmkmData'])->name('padi_umkm_data');
    Route::get('/padi-umkm/summary', [BackController::class, 'getPadiUmkmSummary'])
    ->name('padi_umkm.summary');

    // Event Sponsorship
    Route::get('/monitoring-event-sponsorship', [FrontController::class, 'monitoringEventSponsorship'])->name('admin.monitoring.event_sponsorship');
    Route::get('/get-event-sponsorship-data', [BackController::class, 'getEventSponsorship'])->name('event_sponsorship_data');

    // Creator Partner
    Route::get('/monitoring-creator-partner', [FrontController::class, 'monitoringCreatorPartner'])->name('admin.monitoring.creator_partner');
    Route::get('/get-creator-partner-data', [BackController::class, 'getCreatorPartner'])->name('creator_partner_data');

    // Rekruter Kol Buzzer
    Route::get('/monitoring-rekruter-kol-buzzer', [FrontController::class, 'rekruterKolBuzzer'])->name('admin.monitoring.rekruter_kol_buzzer');
    Route::get('/get-rekruter-kol-buzzer-data', [BackController::class, 'getRekrutBuzzer'])->name('rekruter_kol_buzzer_data');
    
    // Rekruter Kol Influencer
    Route::get('/monitoring-rekruter-kol-influencer', [FrontController::class, 'rekruterKolInfluencer'])->name('admin.monitoring.rekruter_kol_influencer');
    Route::get('/get-rekruter-kol-influencer-data', [BackController::class, 'getRekruterInfluencer'])->name('rekruter_kol_influencer_data');

    // Rekruter KOL Area Marcom
    Route::get('/monitoring-kol-area-marcom', [FrontController::class, 'areaMarkomKol'])->name('admin.monitoring.area_marcom');
    Route::get('/get-monitoring-kol-area-marcom-data', [BackController::class, 'getAreaMarcom'])->name('area_marcom_kol_data');

    
    // Simpati Tiktok
    Route::get('/monitoring-simpati-tiktok', [FrontController::class, 'monitoringSimpatiTiktok'])->name('admin.monitoring.simpati_tiktok');
    Route::get('/get-simpati-tiktok-data', [BackController::class, 'getSimpatiTiktok'])->name('simpati_tiktok_data');

    // Referral Champion AM
    Route::get('/monitoring-referral-champion-am', [FrontController::class, 'monitoringReferralChampionAm'])->name('admin.monitoring.referral_champion');
    Route::get('/get-referral-champion-am-data', [BackController::class, 'getReferralChampionAm'])->name('referral_champion_am_data');

    // Sultam Racing
    Route::get('/monitoring-sultam-racing', [FrontController::class, 'monitoringSultamRacing'])->name('admin.monitoring.sultam_racing');
    Route::get('/get-sultam-racing-data', [BackController::class, 'getSultamRacing'])->name('sultam_racing_data');
});
