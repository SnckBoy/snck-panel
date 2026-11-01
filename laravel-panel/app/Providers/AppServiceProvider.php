<?php

namespace App\Providers;

use App\Models\Server;
use App\Policies\ServerPolicy;
use App\Services\ThemeCatalog;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Server::class, ServerPolicy::class);

        RateLimiter::for('login', function (Request $request) {
            $identity = Str::lower(trim((string) $request->input('identity')));
            return Limit::perMinute(5)->by($identity.'|'.$request->ip());
        });
        RateLimiter::for('setup', fn (Request $request) => Limit::perHour(3)->by($request->ip()));
        RateLimiter::for('register', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('google-auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        // Route-level limiting counts malformed payloads and failed bearer auth.
        RateLimiter::for('node-heartbeat', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        View::composer('layouts.app', function ($view): void {
            $view->with('activeTheme', app(ThemeCatalog::class)->activeTheme());
        });
    }
}
