<?php

use App\Http\Controllers\Admin\AccountInformationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeleteMultipleDataController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebsiteUrlController;
use App\Http\Controllers\Auth\OtpCodeController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Middleware\SubscribeMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/login', [SessionController::class, 'create']);
    Route::post('/otp-codes/verify', [OtpCodeController::class, 'verifyOtpCode']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/logout', [SessionController::class, 'destroy']);

        Route::middleware(SubscribeMiddleware::class)->group(function () {
            Route::get('/users/show/{user}', [UserController::class, 'show']);
            Route::delete('/users/{user}', [UserController::class, 'destroy']);
            Route::post('/users', [UserController::class, 'store']);
            Route::post('/users/update', [UserController::class, 'update']);
            Route::put('/users/status/{user}', [UserController::class, 'userStatus']);
            Route::get('/users/{firstCondition?}/{secondCondition?}', [UserController::class, 'index']);

            Route::get('/domains/{condition?}', [DomainController::class, 'index']);
            Route::get('/domains/show/{domain?}', [DomainController::class, 'show']);
            Route::delete('/domains/{domain}', [DomainController::class, 'destroy']);
            Route::post('/domains', [DomainController::class, 'store']);
            Route::post('/domains/update', [DomainController::class, 'updateDomain']);
            Route::put('/domains/status/{domain}', [DomainController::class, 'domainStatus']);
            Route::get('/domains/get/{user}', [DomainController::class, 'userDomains']);

            Route::get('/roles', [UserController::class, 'roles']);

            Route::controller(WebsiteUrlController::class)->prefix('website-urls')->group(function () {
                Route::get('/{site?}/{category?}', 'index');
                Route::post('/', 'store');
            });

            Route::get('/account-information', [AccountInformationController::class, 'index']);
            Route::delete('/account-information/{accountInformation}', [AccountInformationController::class, 'destroy']);

            Route::post('/multiple-delete-data/{table}', DeleteMultipleDataController::class);

            Route::post('/verifications/send-otp', [OtpCodeController::class, 'sendOTPCode']);
        });

        Route::get('/orders/{condition?}', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/status/{order}', [OrderController::class, 'orderStatus']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

        Route::get('/packages', [PackageController::class, 'index']);

        Route::apiResource('/notices', NoticeController::class);
        Route::apiResource('/supports', SupportController::class);
        Route::post('/supports/update', [SupportController::class, 'update']);

        Route::get('/notifications', [DashboardController::class, 'index']);

        Route::controller(ProfileController::class)->prefix('profiles')->group(function () {
            Route::put('/update', 'update');
            Route::post('/update-picture', 'changeProfilePicture');
            Route::put('/two-steps', 'handleTwoSteps');
            Route::put('/update-password', 'updatePassword');
        });
    });
});
