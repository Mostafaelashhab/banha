<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UtilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (v1)
|--------------------------------------------------------------------------
| All routes prefixed with /api by Laravel's `withRouting(api: ...)` and
| /v1 by the group below. Authentication uses Sanctum personal tokens —
| send `Authorization: Bearer <token>`. Issue tokens from /api/v1/login or
| /api/v1/signup.
*/

Route::prefix('v1')->group(function () {
    // ─── Public ──────────────────────────────────────────────────────
    Route::post('login',  [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);

    Route::get('feed',                    [FeedController::class, 'index']);

    Route::get('directory',               [DirectoryController::class, 'index']);
    Route::get('directory/categories',    [DirectoryController::class, 'categories']);
    Route::get('biz/{slug}',              [DirectoryController::class, 'show']);
    Route::post('track/business-click',   [DirectoryController::class, 'trackClick'])
        ->middleware('throttle:60,1');

    Route::get('search',                  [SearchController::class, 'index']);
    Route::get('open-now',                [UtilityController::class, 'openNow']);
    Route::get('offers',                  [UtilityController::class, 'offers']);
    Route::get('areas/nearest',           [UtilityController::class, 'nearestArea'])
        ->middleware('throttle:30,1');

    Route::get('u/{username}',            [ProfileController::class, 'show']);

    // ─── Authenticated (Sanctum token) ───────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout',         [AuthController::class, 'logout']);
        Route::get('me',              [AuthController::class, 'me']);

        Route::get('following',       [FeedController::class, 'following']);

        Route::get('notifications',          [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::get('profile',         [ProfileController::class, 'me']);
    });
});
