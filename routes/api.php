<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\FamilyApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/families', [FamilyApiController::class, 'index']);
Route::post('/join-family', [FamilyApiController::class, 'joinFamily']);
Route::get('/join-family/qrcode/{familyId}', [FamilyApiController::class, 'generateQRCode']);
Route::get('/join-family/qrcode/image/{familyId}', [FamilyApiController::class, 'generateQRCodeImage'])->name('api.family.qr');
Route::get('/reverse-geocode', [GeocodeController::class, 'reverseGeocode']);
Route::get('/geocode', [GeocodeController::class, 'geocode']);