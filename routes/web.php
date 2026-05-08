<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileSettingsController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('feed')
        : view('welcome');
})->name('home');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/push/vapid', [PushController::class, 'vapidKey'])->name('push.vapid');

// Guest auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/signup',  [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [AuthController::class, 'signup'])->name('signup.attempt');

    // Forgot password (OTP via WhatsApp)
    Route::get('/forgot',         [OtpController::class, 'showForgot'])->name('forgot');
    Route::post('/forgot',        [OtpController::class, 'sendForgot'])->name('forgot.send');
    Route::get('/forgot/verify',  [OtpController::class, 'showForgotVerify'])->name('forgot.verify');
    Route::post('/forgot/verify', [OtpController::class, 'verifyForgot'])->name('forgot.reset');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated app routes
Route::middleware('auth')->group(function () {
    // Activation (after signup)
    Route::get('/verify',       [OtpController::class, 'showActivate'])->name('verify.show');
    Route::post('/verify/send', [OtpController::class, 'sendActivate'])->name('verify.send');
    Route::post('/verify',      [OtpController::class, 'verifyActivate'])->name('verify.attempt');

    Route::get('/feed',     [FeedController::class, 'index'])->name('feed');
    Route::get('/discover', [BrowseController::class, 'discover'])->name('discover');
    Route::get('/zones',    [BrowseController::class, 'zones'])->name('zones');

    Route::get('/posts/new',  [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts',     [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/posts/{post}/vote',    [PostController::class, 'vote'])->name('posts.vote');
    Route::post('/posts/{post}/comment', [PostController::class, 'comment'])->name('posts.comment');
    Route::post('/posts/{post}/report',  [PostController::class, 'report'])->name('posts.report');

    Route::get('/me',                 [ProfileController::class, 'show'])->name('profile.me');
    Route::post('/me/profile',        [ProfileSettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/me/password',       [ProfileSettingsController::class, 'changePassword'])->name('profile.password');
    Route::get('/u/{username}',       [ProfileController::class, 'show'])->name('profile.show');

    // Prices
    Route::get('/prices',                  [PriceController::class, 'index'])->name('prices.index');
    Route::get('/prices/new',              [PriceController::class, 'create'])->name('prices.create');
    Route::post('/prices',                 [PriceController::class, 'store'])->name('prices.store');
    Route::get('/prices/{product}',        [PriceController::class, 'show'])->name('prices.show');

    // Push notifications
    Route::post('/push/subscribe',   [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');

    // Alerts
    Route::get('/alerts',                  [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/new',              [AlertController::class, 'create'])->name('alerts.create');
    Route::post('/alerts',                 [AlertController::class, 'store'])->name('alerts.store');
    Route::post('/alerts/{alert}/confirm', [AlertController::class, 'confirm'])->name('alerts.confirm');
    Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
});
