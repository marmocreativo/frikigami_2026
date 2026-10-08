<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Season;
use Illuminate\Support\Facades\Route;

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
        //
        Route::bind('temporada', fn (string $value) => Season::fromSlug($value) ?? abort(404));
    }
}
