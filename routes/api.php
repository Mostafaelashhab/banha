<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Api\ZoneController;
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

    Route::get('zones',                   [ZoneController::class, 'index']);
    Route::get('home',                    [HomeController::class, 'index']);
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

    // ─── Community content ───────────────────────────────────────────
    Route::get('alerts',                  [CommunityController::class, 'alerts']);
    Route::get('events',                  [CommunityController::class, 'events']);
    Route::get('posts',                   [CommunityController::class, 'posts']);

    // ─── Marketplace ─────────────────────────────────────────────────
    Route::get('marketplace',             [MarketplaceController::class, 'index']);
    Route::get('marketplace/{id}',        [MarketplaceController::class, 'show'])->whereNumber('id');

    // ─── Business sub-resources (menu / reviews / photos) ────────────
    Route::get('biz/{slug}/menu',         [MenuController::class, 'index']);
    Route::get('biz/{slug}/reviews',      [ReviewController::class, 'index']);
    Route::get('biz/{slug}/photos',       [ReviewController::class, 'photos']);

    // ─── Prices (community price tracking) ───────────────────────────
    Route::get('prices',                  [PriceController::class, 'index']);

    // ─── Authenticated (Sanctum token) ───────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout',         [AuthController::class, 'logout']);
        Route::get('me',              [AuthController::class, 'me']);

        // Activation OTP (WhatsApp)
        Route::post('verify/send',    [AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
        Route::post('verify',         [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');

        Route::get('following',       [FeedController::class, 'following']);

        Route::get('notifications',          [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::get('profile',         [ProfileController::class, 'me']);

        // Bookmarks
        Route::get('bookmarks',          [BookmarkController::class, 'index']);
        Route::post('bookmarks/toggle',  [BookmarkController::class, 'toggle']);

        // Orders (customer side)
        Route::get('orders',             [OrderController::class, 'mine']);
        Route::get('orders/{id}',        [OrderController::class, 'show'])->whereNumber('id');
        Route::post('orders',            [OrderController::class, 'store']);

        // Bookings (customer side)
        Route::get('bookings',           [BookingController::class, 'mine']);
        Route::post('bookings',          [BookingController::class, 'store']);

        // Reviews
        Route::post('biz/{slug}/reviews', [ReviewController::class, 'store']);
    });
});
