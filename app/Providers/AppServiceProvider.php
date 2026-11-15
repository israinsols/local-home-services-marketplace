<?php

namespace App\Providers;

use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Policies\LeadPolicy;
use App\Policies\ProviderProfilePolicy;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(ProviderProfile::class, ProviderProfilePolicy::class);
        View::composer('*', function ($view) {
            $view->with('siteName', SiteSetting::valueFor('site_name', config('marketplace.name')));
            $view->with('contactEmail', SiteSetting::valueFor('contact_email', config('marketplace.contact_email')));
        });
        RateLimiter::for('lead-submit', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('provider-register', fn (Request $request) => Limit::perHour(3)->by($request->ip()));
    }
}
