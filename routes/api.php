<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\FamilyApiController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/families', [FamilyApiController::class, 'index']);
Route::post('/join-family', [FamilyApiController::class, 'joinFamily']);
Route::get('/join-family/qrcode/{familyId}', [FamilyApiController::class, 'generateQRCode']);
Route::get('/join-family/qrcode/image/{familyId}', [FamilyApiController::class, 'generateQRCodeImage'])->name('api.family.qr');
Route::get('family/{familyId}', [FamilyApiController::class, 'show']);
Route::post('/users/temp', [UserController::class, 'createTempUser']);
Route::post('/users/{userId}/complete', [UserController::class, 'completeRegistration']);
Route::delete('/users/cleanup-pending', [UserController::class, 'cleanupPendingUsers']);

Route::get('/reverse-geocode', [GeocodeController::class, 'reverseGeocode']);
Route::get('/geocode', [GeocodeController::class, 'geocode']);