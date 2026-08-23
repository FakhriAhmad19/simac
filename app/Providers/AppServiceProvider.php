<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        // App uses Bootstrap 5, so render paginator links with Bootstrap markup
        // instead of the default Tailwind view (which shows oversized SVG arrows).
        Paginator::useBootstrapFive();
    }
}
