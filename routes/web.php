<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AccountDataController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProviderAuthController;
use App\Http\Controllers\ProviderPortalController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;

Route::get('/request-quote', [LeadController::class, 'create'])->name('leads.create');
Route::post('/leads', [LeadController::class, 'store'])->middleware('throttle:lead-submit')->name('leads.store');
Route::get('/leads/received', [LeadController::class, 'thankYou'])->name('leads.thank-you');
Route::post('/stripe/webhook', [CreditController::class, 'webhook'])->name('stripe.webhook');
Route::post('/cookie-consent', [CookieConsentController::class, 'store'])->name('cookie-consent.store');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-home.xml', [SitemapController::class, 'home'])->name('sitemap.home');
Route::get('/sitemaps/{section}.xml', [SitemapController::class, 'section'])
    ->where('section', '[a-z0-9-]+')
    ->name('sitemap.section');

Route::get('/provider/register', [ProviderAuthController::class, 'create'])->name('provider.register');
Route::post('/provider/register', [ProviderAuthController::class, 'store'])->middleware('throttle:provider-register')->name('provider.register.store');
Route::get('/provider/login', [ProviderAuthController::class, 'login'])->name('provider.login');
Route::post('/provider/login', [ProviderAuthController::class, 'authenticate'])->middleware('throttle:6,1')->name('provider.login.store');
Route::post('/provider/logout', [ProviderAuthController::class, 'logout'])->middleware('auth')->name('provider.logout');
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->middleware(['auth', 'provider.account'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'provider.account', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'provider.account', 'throttle:6,1'])
    ->name('verification.send');

Route::prefix('provider')->name('provider.')->middleware(['auth', 'verified', 'provider.account'])->group(function () {
    Route::get('/', [ProviderPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/leads/{leadId}', [ProviderPortalController::class, 'showLead'])->whereNumber('leadId')->name('leads.show');
    Route::post('/leads/{leadId}/unlock', [ProviderPortalController::class, 'unlockLead'])->whereNumber('leadId')->middleware('throttle:20,1')->name('leads.unlock');
    Route::get('/profile', [ProviderPortalController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [ProviderPortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/account/export', [AccountDataController::class, 'export'])->name('data.export');
    Route::delete('/account', [AccountDataController::class, 'erase'])->middleware('throttle:3,1')->name('account.erase');
    Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
    Route::post('/credits/checkout', [CreditController::class, 'checkout'])->name('credits.checkout');
    Route::get('/credits/success', [CreditController::class, 'success'])->name('credits.success');
});

Route::get('/', HomeController::class)->name('home');
Route::get('/find', SearchController::class)->name('search');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/pros/{provider:slug}', [ProviderController::class, 'show'])->name('providers.show');
Route::get('/{service}/{city}', [LandingPageController::class, 'serviceAndCity'])
    ->where(['service' => '[a-z0-9-]+', 'city' => '[a-z0-9-]+'])
    ->name('landing.service-city');
Route::get('/{slug}', [LandingPageController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('landing.page');
