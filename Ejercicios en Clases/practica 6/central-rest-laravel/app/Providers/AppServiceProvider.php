<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (request()->header('x-forwarded-proto') === 'https' || str_contains((string) request()->getHost(), 'lhr.life')) {
            URL::forceScheme('https');
        }
    }
}
