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

Route::get('/', fn () => redirect()->route('feed'))->name('home');
Route::view('/welcome', 'welcome')->name('welcome');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt',  [SeoController::class, 'robots'])->name('robots');

// ─── Local-SEO money pages ──────────────────────────────────────
// Explicit slug whitelist (defined in LocalSeoController::LANDINGS) — anything
// not in the whitelist 404s, so this won't shadow legit routes.
Route::get('/{slug}', [\App\Http\Controllers\LocalSeoController::class, 'landing'])
    ->where('slug', 'best-restaurants-in-banha|cafes-in-banha|doctors-in-banha|pharmacies-in-banha|places-to-go-in-banha|restaurants-in-banha|24-hour-pharmacies-in-banha')
    ->name('seo.landing');

// Programmatic pattern: /{cat}-in-{area-slug}-banha
// e.g. /cafes-in-el-felal-banha, /dentists-in-shareh-farid-nada-banha
Route::get('/{slug}-in-{area}-banha', [\App\Http\Controllers\LocalSeoController::class, 'programmatic'])
    ->where('slug', 'cafes|doctors|pharmacies|restaurants|dentists|places-to-go')
    ->where('area', '[a-z0-9-]+')
    ->name('seo.programmatic');
Route::view('/offline',    'offline')->name('offline');
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
// Brand-friendly canonical URL: /biz/{slug} — matches city-app's URL pattern
// for direct brand queries ("ارمادا بنها" → /biz/armada).
Route::get('/biz/{business:slug}',           [DirectoryController::class, 'show'])->name('directory.show.slug');
// Legacy numeric URL — still works (no 301 to avoid breaking external backlinks)
// but the canonical <link> on the rendered page points to /biz/{slug}.
Route::get('/directory/business/{business}', [DirectoryController::class, 'show'])->name('directory.show');

// ── Public booking (guests allowed) ───────────────────────────
Route::get('/directory/business/{business}/book',  [\App\Http\Controllers\BookingController::class, 'show'])->name('booking.show');
Route::post('/directory/business/{business}/book', [\App\Http\Controllers\BookingController::class, 'store'])->name('booking.store');
Route::get('/directory/c/{category}',        [DirectoryController::class, 'category'])->name('directory.category');
Route::get('/directory/business/{business}/click', [DirectoryController::class, 'trackClick'])->name('directory.track');

// Public map (everything in Banha on one map)
Route::get('/map',                  [DirectoryController::class, 'map'])->name('directory.map');
Route::get('/map.json',             [DirectoryController::class, 'mapData'])->name('directory.map.data');

// Public QR menu (the SEO money page)
Route::get('/m/{business}', [\App\Http\Controllers\MenuController::class, 'publicMenu'])->name('menu.public');

// Public — place an order from the menu page (guests allowed; sends to restaurant via WAAPI)
Route::post('/m/{business}/order', [\App\Http\Controllers\OrderController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('order.store');

// Public marketplace + search + hashtag pages
Route::get('/market',                  [\App\Http\Controllers\ListingController::class, 'index'])->name('marketplace.index');
Route::get('/market/{listing}',        [\App\Http\Controllers\ListingController::class, 'show'])->name('marketplace.show')->whereNumber('listing');
Route::get('/search',                  [\App\Http\Controllers\SearchController::class, 'index'])->name('search');
Route::get('/search/suggest',          [\App\Http\Controllers\SearchController::class, 'suggest'])->name('search.suggest');
Route::get('/tag/{tag}',               [\App\Http\Controllers\HashtagController::class, 'show'])->name('hashtag.show');
Route::get('/tags',                    [\App\Http\Controllers\HashtagController::class, 'trending'])->name('hashtag.trending');

// Public: users discovery + nearby + events + stories
Route::get('/users',                   [BrowseController::class, 'users'])->name('users.index');
Route::get('/nearby',                  [DirectoryController::class, 'nearby'])->name('directory.nearby');
Route::get('/events',                  [\App\Http\Controllers\EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}',          [\App\Http\Controllers\EventController::class, 'show'])->name('events.show')->whereNumber('event');
Route::get('/stories',                 [\App\Http\Controllers\StoryController::class, 'index'])->name('stories.index');
Route::get('/stories/{story}',         [\App\Http\Controllers\StoryController::class, 'show'])->name('stories.show')->whereNumber('story');

// Public guest browsing — feed, discover, zones, posts & user profiles
Route::get('/feed',                    [FeedController::class, 'index'])->name('feed');
Route::get('/discover',                [BrowseController::class, 'discover'])->name('discover');
Route::get('/zones',                   [BrowseController::class, 'zones'])->name('zones');
Route::get('/zone/{slug}',             [BrowseController::class, 'zoneShow'])->name('zone.show');
Route::get('/posts/{post}',            [PostController::class, 'show'])->name('posts.show');
Route::get('/u/{username}',            [ProfileController::class, 'show'])->name('profile.show');

// Support (live chat for users; fallback page for guests)
Route::get('/support', [\App\Http\Controllers\SupportController::class, 'open'])->name('support');

// ─── Public utility hubs (no auth) ─────────────────────────────
Route::get('/offers',           [\App\Http\Controllers\OffersController::class,     'index'])->name('offers.index');
Route::get('/open-now',         [\App\Http\Controllers\OpenNowController::class,    'index'])->name('open-now.index');
Route::get('/emergency',        [\App\Http\Controllers\EmergencyController::class,  'index'])->name('emergency.index');
Route::get('/benha-university', [\App\Http\Controllers\UniversityController::class, 'index'])->name('university.index');
Route::get('/banha-trains',     [\App\Http\Controllers\TrainsController::class,     'index'])->name('trains.index');

// ─── Marketing landings (own-business pitch + QR menu pitch) ───
Route::get('/own-business',     [\App\Http\Controllers\MarketingController::class,  'claim'])->name('marketing.claim');
Route::get('/qr-menu',          [\App\Http\Controllers\MarketingController::class,  'qrMenu'])->name('marketing.qr-menu');

// ─── Areas lookup (public — used by cart geolocation auto-pick) ───
Route::get('/areas/nearest',    [\App\Http\Controllers\AreaController::class, 'nearest'])
    ->middleware('throttle:30,1')
    ->name('areas.nearest');

// Authenticated app routes
Route::middleware('auth')->group(function () {
    // Activation (after signup)
    Route::get('/verify',       [OtpController::class, 'showActivate'])->name('verify.show');
    Route::post('/verify/send', [OtpController::class, 'sendActivate'])->name('verify.send');
    Route::post('/verify',      [OtpController::class, 'verifyActivate'])->name('verify.attempt');

    // Directory — owner CRUD (auth-only)
    Route::get('/directory/new',                            [DirectoryController::class, 'create'])->name('directory.create');
    Route::post('/directory',                               [DirectoryController::class, 'store'])->name('directory.store');
    Route::get('/directory/mine',                           [DirectoryController::class, 'myListings'])->name('directory.mine');
    Route::get('/directory/business/{business}/manage',     [DirectoryController::class, 'manage'])->name('directory.manage');
    Route::get('/directory/business/{business}/edit',       [DirectoryController::class, 'edit'])->name('directory.edit');
    Route::get('/directory/business/{business}/stats',      [DirectoryController::class, 'stats'])->name('directory.stats');
    Route::get('/directory/business/{business}/bookings',   [\App\Http\Controllers\BookingController::class, 'ownerIndex'])->name('booking.owner.index');
    Route::patch('/booking/{booking}/status',               [\App\Http\Controllers\BookingController::class, 'updateStatus'])->name('booking.status.update');

    // Owner — incoming orders
    Route::get('/directory/business/{business}/orders',     [\App\Http\Controllers\OrderController::class, 'ownerIndex'])->name('order.owner.index');
    Route::patch('/order/{order}/status',                   [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('order.status.update');

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
    Route::get('/market/{listing}/edit',   [\App\Http\Controllers\ListingController::class, 'edit'])->name('marketplace.edit')->whereNumber('listing');
    Route::patch('/market/{listing}',      [\App\Http\Controllers\ListingController::class, 'update'])->name('marketplace.update')->whereNumber('listing');
    Route::delete('/market/{listing}',     [\App\Http\Controllers\ListingController::class, 'destroy'])->name('marketplace.destroy');
    Route::post('/market/{listing}/sold',  [\App\Http\Controllers\ListingController::class, 'markSold'])->name('marketplace.sold');

    // Bookmarks + Notifications inbox
    Route::post('/bookmark',           [\App\Http\Controllers\BookmarkController::class, 'toggle'])->name('bookmark.toggle');
    Route::get('/saved',               [\App\Http\Controllers\BookmarkController::class, 'index'])->name('bookmark.index');
    Route::get('/notifications',                   [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count',             [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Polls
    Route::post('/polls/{poll}/vote', [\App\Http\Controllers\PollController::class, 'vote'])->name('polls.vote');

    // Follow + personalized feed
    Route::post('/users/{user}/follow', [\App\Http\Controllers\FollowController::class, 'toggle'])->name('users.follow');
    Route::get('/following',            [\App\Http\Controllers\FollowController::class, 'followingFeed'])->name('feed.following');

    // Business photo gallery (owner CRUD)
    Route::post('/directory/business/{business}/photo',  [\App\Http\Controllers\BusinessPhotoController::class, 'store'])->name('business.photo.store');
    Route::delete('/business-photos/{photo}',            [\App\Http\Controllers\BusinessPhotoController::class, 'destroy'])->name('business.photo.destroy');

    // Business reviews (logged-in users)
    Route::post('/directory/business/{business}/review',   [\App\Http\Controllers\BusinessReviewController::class, 'store'])->name('business.review.store');
    Route::delete('/directory/business/{business}/review', [\App\Http\Controllers\BusinessReviewController::class, 'destroy'])->name('business.review.destroy');

    // Claim ownership of an unowned (OSM) business via WhatsApp OTP
    Route::get('/directory/business/{business}/claim',          [\App\Http\Controllers\BusinessClaimController::class, 'show'])->name('directory.claim.show');
    Route::post('/directory/business/{business}/claim/request', [\App\Http\Controllers\BusinessClaimController::class, 'requestOtp'])->name('directory.claim.request');
    Route::post('/directory/business/{business}/claim/verify',  [\App\Http\Controllers\BusinessClaimController::class, 'verify'])->name('directory.claim.verify');

    // Events (owner CRUD)
    Route::get('/events/new',           [\App\Http\Controllers\EventController::class, 'create'])->name('events.create');
    Route::post('/events',              [\App\Http\Controllers\EventController::class, 'store'])->name('events.store');
    Route::post('/events/{event}/attend',   [\App\Http\Controllers\EventController::class, 'attend'])->name('events.attend');
    Route::post('/events/{event}/unattend', [\App\Http\Controllers\EventController::class, 'unattend'])->name('events.unattend');
    Route::delete('/events/{event}',    [\App\Http\Controllers\EventController::class, 'destroy'])->name('events.destroy');

    // Stories (owner CRUD)
    Route::get('/stories/new',          [\App\Http\Controllers\StoryController::class, 'create'])->name('stories.create');
    Route::post('/stories',             [\App\Http\Controllers\StoryController::class, 'store'])->name('stories.store');
    Route::get('/stories/{story}/viewers', [\App\Http\Controllers\StoryController::class, 'viewers'])->name('stories.viewers')->whereNumber('story');
    Route::delete('/stories/{story}',   [\App\Http\Controllers\StoryController::class, 'destroy'])->name('stories.destroy');

    // DMs
    Route::get('/chat',                  [\App\Http\Controllers\ChatController::class, 'inbox'])->name('chat.inbox');
    Route::get('/chat/with/{user}',      [\App\Http\Controllers\ChatController::class, 'open'])->name('chat.open');
    Route::get('/chat/{thread}',         [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show')->whereNumber('thread');
    Route::post('/chat/{thread}',        [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send')->whereNumber('thread');
    Route::get('/chat/{thread}/poll',    [\App\Http\Controllers\ChatController::class, 'poll'])->name('chat.poll')->whereNumber('thread');
    Route::post('/chat/{thread}/report', [\App\Http\Controllers\ChatController::class, 'report'])->name('chat.report')->whereNumber('thread');

    // Customer order tracking
    Route::get('/my-orders',                        [\App\Http\Controllers\MyOrdersController::class, 'index'])->name('my-orders.index');
    Route::post('/my-orders/{order}/reorder',       [\App\Http\Controllers\MyOrdersController::class, 'reorder'])->name('my-orders.reorder');

    Route::get('/me',                 [ProfileController::class, 'show'])->name('profile.me');
    Route::get('/wallet',             [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet');

    // Withdrawals (user actions)
    Route::post('/withdrawals',                        [\App\Http\Controllers\WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::post('/withdrawals/{withdrawal}/cancel',    [\App\Http\Controllers\WithdrawalController::class, 'cancel'])->name('withdrawals.cancel');
    Route::post('/me/area',           [\App\Http\Controllers\AreaController::class, 'setDefault'])->name('profile.area.set');
    Route::post('/me/profile',        [ProfileSettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/me/password',       [ProfileSettingsController::class, 'changePassword'])->name('profile.password');
    Route::post('/me/prayer-notify',  [ProfileSettingsController::class, 'togglePrayerNotify'])->name('profile.prayer.notify');
    Route::post('/me/avatar',         [ProfileSettingsController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::delete('/me/avatar',       [ProfileSettingsController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // ─── Admin ────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                              [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/users',                         [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/ban',             [AdminController::class, 'userBan'])->name('users.ban');
        Route::post('/users/{user}/tier',            [AdminController::class, 'userTier'])->name('users.tier');
        Route::post('/users/{user}/admin',           [AdminController::class, 'userAdmin'])->name('users.admin');
        Route::get('/users/{user}/points',           [AdminController::class, 'userPoints'])->name('users.points');
        Route::post('/users/{user}/points',          [AdminController::class, 'userPointsAward'])->name('users.points.award');
        Route::post('/point-tx/{tx}/revoke',         [AdminController::class, 'userPointsRevoke'])->name('users.points.revoke');

        // Withdrawals queue
        Route::get('/withdrawals',                       [AdminController::class, 'withdrawals'])->name('withdrawals');
        Route::post('/withdrawals/{withdrawal}/approve', [AdminController::class, 'withdrawalApprove'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/paid',    [AdminController::class, 'withdrawalMarkPaid'])->name('withdrawals.paid');
        Route::post('/withdrawals/{withdrawal}/reject',  [AdminController::class, 'withdrawalReject'])->name('withdrawals.reject');

        Route::get('/posts',                         [AdminController::class, 'posts'])->name('posts');
        Route::post('/posts/{post}/remove',          [AdminController::class, 'postRemove'])->name('posts.remove');
        Route::post('/posts/{post}/restore',         [AdminController::class, 'postRestore'])->name('posts.restore');

        Route::get('/reports',                       [AdminController::class, 'reports'])->name('reports');
        Route::post('/reports/{report}/resolve',     [AdminController::class, 'reportResolve'])->name('reports.resolve');

        Route::get('/businesses',                    [AdminController::class, 'businesses'])->name('businesses');
        Route::post('/businesses/{business}/photo',   [AdminController::class, 'businessSetPhoto'])->name('businesses.photo');
        Route::post('/businesses/{business}/verify',  [AdminController::class, 'businessVerify'])->name('businesses.verify');
        Route::post('/businesses/{business}/toggle',  [AdminController::class, 'businessToggleActive'])->name('businesses.toggle');
        Route::post('/businesses/{business}/promote', [AdminController::class, 'businessPromote'])->name('businesses.promote');
        // Claim-invite via WAAPI — preview (GET, JSON) + send (POST).
        Route::get('/businesses/{business}/invite',      [AdminController::class, 'businessInvitePreview'])->name('businesses.invite.preview');
        Route::post('/businesses/{business}/invite',     [AdminController::class, 'businessInviteSend'])->name('businesses.invite.send');
        Route::post('/listings/{listing}/feature',    [AdminController::class, 'listingFeature'])->name('listings.feature');

        Route::get('/broadcast',                     [AdminController::class, 'broadcastForm'])->name('broadcast');
        Route::post('/broadcast',                    [AdminController::class, 'broadcastSend'])->name('broadcast.send');

        Route::get('/outages',                       [AdminController::class, 'outageForm'])->name('outages');
        Route::post('/outages',                      [AdminController::class, 'outageStore'])->name('outages.store');
        Route::post('/outages/{alert}/resolve',      [AdminController::class, 'outageResolve'])->name('outages.resolve');

        // Promo banners (homepage slider)
        Route::get('/promo-banners',                       [AdminController::class, 'promoBanners'])->name('promo.banners');
        Route::post('/promo-banners',                      [AdminController::class, 'promoBannerStore'])->name('promo.banners.store');
        Route::post('/promo-banners/{banner}',             [AdminController::class, 'promoBannerUpdate'])->name('promo.banners.update');
        Route::post('/promo-banners/{banner}/toggle',      [AdminController::class, 'promoBannerToggle'])->name('promo.banners.toggle');
        Route::delete('/promo-banners/{banner}',           [AdminController::class, 'promoBannerDestroy'])->name('promo.banners.destroy');

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
