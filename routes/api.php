<?php

use App\Http\Controllers\Api\GetApiDataController;
use App\Http\Controllers\Api\VisitorInformationController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountInformationController;

Route::prefix('v1')->group(function () {
    Route::get('/accounts/testing', function () {
        return response()->json(['message' => 'Testing API'], 200);
    });

    Route::post('/accounts/store', [AccountInformationController::class, 'store']);

    Route::post('/accounts/update', [AccountInformationController::class, 'update']);

    Route::get('/accounts/show-account/{access_token}', [AccountInformationController::class, 'show']);

    Route::post('/visitor-information/store', [VisitorInformationController::class, 'store']);

    Route::get('/url-information/{site}/{videoCalling}/{user_access_token}', [GetApiDataController::class, 'getSiteAndVideoCallingCredentials']);
});
