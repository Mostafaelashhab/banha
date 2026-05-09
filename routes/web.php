<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\DirectoryController;
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
Route::get('/robots.txt',  [SeoController::class, 'robots'])->name('robots');
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

// Public directory (browseable without login)
Route::get('/directory',                     [DirectoryController::class, 'index'])->name('directory.index');
Route::get('/directory/business/{business}', [DirectoryController::class, 'show'])->name('directory.show');
Route::get('/directory/c/{category}',        [DirectoryController::class, 'category'])->name('directory.category');
Route::get('/directory/business/{business}/click', [DirectoryController::class, 'trackClick'])->name('directory.track');

// Public QR menu (the SEO money page)
Route::get('/m/{business}', [\App\Http\Controllers\MenuController::class, 'publicMenu'])->name('menu.public');

// Public marketplace + search + hashtag pages
Route::get('/market',                  [\App\Http\Controllers\ListingController::class, 'index'])->name('marketplace.index');
Route::get('/market/{listing}',        [\App\Http\Controllers\ListingController::class, 'show'])->name('marketplace.show')->whereNumber('listing');
Route::get('/search',                  [\App\Http\Controllers\SearchController::class, 'index'])->name('search');
Route::get('/tag/{tag}',               [\App\Http\Controllers\HashtagController::class, 'show'])->name('hashtag.show');
Route::get('/tags',                    [\App\Http\Controllers\HashtagController::class, 'trending'])->name('hashtag.trending');

// Public: users discovery + nearby + events + stories
Route::get('/users',                   [BrowseController::class, 'users'])->name('users.index');
Route::get('/nearby',                  [DirectoryController::class, 'nearby'])->name('directory.nearby');
Route::get('/events',                  [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}',          [\App\Http\Controllers\EventController::class, 'show'])->name('events.show')->whereNumber('event');
Route::get('/stories',                 [\App\Http\Controllers\StoryController::class, 'index'])->name('stories.index');
Route::get('/stories/{story}',         [\App\Http\Controllers\StoryController::class, 'show'])->name('stories.show')->whereNumber('story');

// Authenticated app routes
Route::middleware('auth')->group(function () {
    // Activation (after signup)
    Route::get('/verify',       [OtpController::class, 'showActivate'])->name('verify.show');
    Route::post('/verify/send', [OtpController::class, 'sendActivate'])->name('verify.send');
    Route::post('/verify',      [OtpController::class, 'verifyActivate'])->name('verify.attempt');

    Route::get('/feed',     [FeedController::class, 'index'])->name('feed');
    Route::get('/discover', [BrowseController::class, 'discover'])->name('discover');
    Route::get('/zones',    [BrowseController::class, 'zones'])->name('zones');

    // Directory — owner CRUD (auth-only)
    Route::get('/directory/new',                            [DirectoryController::class, 'create'])->name('directory.create');
    Route::post('/directory',                               [DirectoryController::class, 'store'])->name('directory.store');
    Route::get('/directory/mine',                           [DirectoryController::class, 'myListings'])->name('directory.mine');
    Route::get('/directory/business/{business}/edit',       [DirectoryController::class, 'edit'])->name('directory.edit');
    Route::get('/directory/business/{business}/stats',      [DirectoryController::class, 'stats'])->name('directory.stats');

    // Menu management (owner)
    Route::get('/directory/business/{business}/menu',       [\App\Http\Controllers\MenuController::class, 'manage'])->name('menu.manage');
    Route::post('/directory/business/{business}/menu/category', [\App\Http\Controllers\MenuController::class, 'storeCategory'])->name('menu.category.store');
    Route::delete('/menu/category/{category}',              [\App\Http\Controllers\MenuController::class, 'destroyCategory'])->name('menu.category.destroy');
    Route::post('/directory/business/{business}/menu/item', [\App\Http\Controllers\MenuController::class, 'storeItem'])->name('menu.item.store');
    Route::patch('/menu/item/{item}',                       [\App\Http\Controllers\MenuController::class, 'updateItem'])->name('menu.item.update');
    Route::post('/menu/item/{item}/toggle',                 [\App\Http\Controllers\MenuController::class, 'toggleItem'])->name('menu.item.toggle');
    Route::delete('/menu/item/{item}',                      [\App\Http\Controllers\MenuController::class, 'destroyItem'])->name('menu.item.destroy');
    Route::patch('/directory/business/{business}',          [DirectoryController::class, 'update'])->name('directory.update');
    Route::delete('/directory/business/{business}',         [DirectoryController::class, 'destroy'])->name('directory.destroy');

    Route::get('/posts/new',  [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts',     [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/posts/{post}/vote',    [PostController::class, 'vote'])->name('posts.vote');
    Route::post('/posts/{post}/comment', [PostController::class, 'comment'])->name('posts.comment');
    Route::post('/posts/{post}/report',  [PostController::class, 'report'])->name('posts.report');

    Route::post('/comments/{comment}/like',   [\App\Http\Controllers\CommentController::class, 'like'])->name('comments.like');
    Route::post('/comments/{comment}/reply',  [\App\Http\Controllers\CommentController::class, 'reply'])->name('comments.reply');
    Route::post('/comments/{comment}/report', [\App\Http\Controllers\CommentController::class, 'report'])->name('comments.report');
    Route::delete('/comments/{comment}',      [\App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');

    // Marketplace owner actions
    Route::get('/market/new',              [\App\Http\Controllers\ListingController::class, 'create'])->name('marketplace.create');
    Route::post('/market',                 [\App\Http\Controllers\ListingController::class, 'store'])->name('marketplace.store');
    Route::delete('/market/{listing}',     [\App\Http\Controllers\ListingController::class, 'destroy'])->name('marketplace.destroy');
    Route::post('/market/{listing}/sold',  [\App\Http\Controllers\ListingController::class, 'markSold'])->name('marketplace.sold');

    // Bookmarks + Notifications inbox
    Route::post('/bookmark',           [\App\Http\Controllers\BookmarkController::class, 'toggle'])->name('bookmark.toggle');
    Route::get('/saved',               [\App\Http\Controllers\BookmarkController::class, 'index'])->name('bookmark.index');
    Route::get('/notifications',       [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.count');

    // Polls
    Route::post('/polls/{poll}/vote', [\App\Http\Controllers\PollController::class, 'vote'])->name('polls.vote');

    // Follow + personalized feed
    Route::post('/users/{user}/follow', [\App\Http\Controllers\FollowController::class, 'toggle'])->name('users.follow');
    Route::get('/following',            [\App\Http\Controllers\FollowController::class, 'followingFeed'])->name('feed.following');

    // Business photo gallery (owner CRUD)
    Route::post('/directory/business/{business}/photo',  [\App\Http\Controllers\BusinessPhotoController::class, 'store'])->name('business.photo.store');
    Route::delete('/business-photos/{photo}',            [\App\Http\Controllers\BusinessPhotoController::class, 'destroy'])->name('business.photo.destroy');

    // Events (owner CRUD)
    Route::get('/events/new',           [\App\Http\Controllers\EventController::class, 'create'])->name('events.create');
    Route::post('/events',              [\App\Http\Controllers\EventController::class, 'store'])->name('events.store');
    Route::post('/events/{event}/attend',   [\App\Http\Controllers\EventController::class, 'attend'])->name('events.attend');
    Route::post('/events/{event}/unattend', [\App\Http\Controllers\EventController::class, 'unattend'])->name('events.unattend');
    Route::delete('/events/{event}',    [\App\Http\Controllers\EventController::class, 'destroy'])->name('events.destroy');

    // Stories (owner CRUD)
    Route::get('/stories/new',          [\App\Http\Controllers\StoryController::class, 'create'])->name('stories.create');
    Route::post('/stories',             [\App\Http\Controllers\StoryController::class, 'store'])->name('stories.store');
    Route::delete('/stories/{story}',   [\App\Http\Controllers\StoryController::class, 'destroy'])->name('stories.destroy');

    // DMs
    Route::get('/chat',                  [\App\Http\Controllers\ChatController::class, 'inbox'])->name('chat.inbox');
    Route::get('/chat/with/{user}',      [\App\Http\Controllers\ChatController::class, 'open'])->name('chat.open');
    Route::get('/chat/{thread}',         [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show')->whereNumber('thread');
    Route::post('/chat/{thread}',        [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send')->whereNumber('thread');
    Route::get('/chat/{thread}/poll',    [\App\Http\Controllers\ChatController::class, 'poll'])->name('chat.poll')->whereNumber('thread');
    Route::post('/chat/{thread}/report', [\App\Http\Controllers\ChatController::class, 'report'])->name('chat.report')->whereNumber('thread');

    Route::get('/me',                 [ProfileController::class, 'show'])->name('profile.me');
    Route::post('/me/profile',        [ProfileSettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/me/password',       [ProfileSettingsController::class, 'changePassword'])->name('profile.password');
    Route::post('/me/avatar',         [ProfileSettingsController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::delete('/me/avatar',       [ProfileSettingsController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::get('/u/{username}',       [ProfileController::class, 'show'])->name('profile.show');

    // ─── Admin ────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                              [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/users',                         [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/ban',             [AdminController::class, 'userBan'])->name('users.ban');
        Route::post('/users/{user}/tier',            [AdminController::class, 'userTier'])->name('users.tier');
        Route::post('/users/{user}/admin',           [AdminController::class, 'userAdmin'])->name('users.admin');

        Route::get('/posts',                         [AdminController::class, 'posts'])->name('posts');
        Route::post('/posts/{post}/remove',          [AdminController::class, 'postRemove'])->name('posts.remove');
        Route::post('/posts/{post}/restore',         [AdminController::class, 'postRestore'])->name('posts.restore');

        Route::get('/reports',                       [AdminController::class, 'reports'])->name('reports');
        Route::post('/reports/{report}/resolve',     [AdminController::class, 'reportResolve'])->name('reports.resolve');

        Route::get('/businesses',                    [AdminController::class, 'businesses'])->name('businesses');
        Route::post('/businesses/{business}/verify',  [AdminController::class, 'businessVerify'])->name('businesses.verify');
        Route::post('/businesses/{business}/toggle',  [AdminController::class, 'businessToggleActive'])->name('businesses.toggle');
        Route::post('/businesses/{business}/promote', [AdminController::class, 'businessPromote'])->name('businesses.promote');
        Route::post('/listings/{listing}/feature',    [AdminController::class, 'listingFeature'])->name('listings.feature');

        Route::get('/broadcast',                     [AdminController::class, 'broadcastForm'])->name('broadcast');
        Route::post('/broadcast',                    [AdminController::class, 'broadcastSend'])->name('broadcast.send');

        Route::post('/recheck-tiers',                [AdminController::class, 'recheckTiers'])->name('recheck.tiers');
    });

    // Prices
    Route::get('/prices',                  [PriceController::class, 'index'])->name('prices.index');
    Route::get('/prices/new',              [PriceController::class, 'create'])->name('prices.create');
    Route::post('/prices',                 [PriceController::class, 'store'])->name('prices.store');
    Route::get('/prices/{product}',        [PriceController::class, 'show'])->name('prices.show');

    // Push notifications
    Route::post('/push/subscribe',   [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::post('/push/test',        [PushController::class, 'sendTest'])->name('push.test');

    // Alerts
    Route::get('/alerts',                  [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/new',              [AlertController::class, 'create'])->name('alerts.create');
    Route::post('/alerts',                 [AlertController::class, 'store'])->name('alerts.store');
    Route::get('/alerts/{alert}',          [AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{alert}/confirm', [AlertController::class, 'confirm'])->name('alerts.confirm');
    Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
});
