<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\CaseController;
use App\Http\Controllers\Api\V1\DeskController;
use App\Http\Controllers\Api\V1\DeskProfileController;
use App\Http\Controllers\Api\V1\PortalController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\InstagramMediaController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\TopicFollowController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
    ]));

    Route::get('/portal', [PortalController::class, 'show']);

    Route::get('/cases', [CaseController::class, 'index']);
    Route::get('/cases/{slug}', [CaseController::class, 'show']);

    Route::get('/perfiles', [ProfileController::class, 'index']);
    Route::get('/perfiles/{slug}', [ProfileController::class, 'show']);

    Route::get('/stats/portfolio', [StatsController::class, 'portfolio']);

    Route::get('/instagram-media', [InstagramMediaController::class, 'show'])
        ->middleware('throttle:60,1');

    Route::post('/analytics/events', [AnalyticsController::class, 'store'])
        ->middleware('throttle:60,1');

    Route::post('/cases/{slug}/follow', [TopicFollowController::class, 'store']);
    Route::delete('/cases/{slug}/follow', [TopicFollowController::class, 'destroy']);

    Route::post('/desk/gate', [DeskController::class, 'gate'])->middleware('throttle:8,1');
    Route::post('/desk/session', [DeskController::class, 'session'])->middleware('throttle:8,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/desk/cases', [DeskController::class, 'cases']);
        Route::patch('/desk/cases/{slug}', [DeskController::class, 'update']);
        Route::post('/desk/cases/{slug}/cover', [DeskController::class, 'uploadCover']);

        Route::get('/desk/profiles', [DeskProfileController::class, 'index']);
        Route::post('/desk/profiles', [DeskProfileController::class, 'store']);
        Route::get('/desk/profiles/{slug}', [DeskProfileController::class, 'show']);
        Route::patch('/desk/profiles/{slug}', [DeskProfileController::class, 'update']);
        Route::post('/desk/profiles/{slug}/refresh-avatar', [DeskProfileController::class, 'refreshAvatar']);
        Route::post('/desk/profiles/{slug}/aliases', [DeskProfileController::class, 'storeAlias']);
        Route::delete('/desk/profiles/{slug}/aliases/{aliasId}', [DeskProfileController::class, 'destroyAlias']);
        Route::post('/desk/profiles/{slug}/roles', [DeskProfileController::class, 'storeRole']);
        Route::post('/desk/profiles/{slug}/social-accounts', [DeskProfileController::class, 'storeSocialAccount']);

        Route::get('/desk/profile-mentions', [DeskProfileController::class, 'mentions']);
        Route::post('/desk/profile-mentions', [DeskProfileController::class, 'storeMention']);
        Route::patch('/desk/profile-mentions/{id}', [DeskProfileController::class, 'updateMention']);
        Route::delete('/desk/profile-mentions/{id}', [DeskProfileController::class, 'destroyMention']);
    });
});
