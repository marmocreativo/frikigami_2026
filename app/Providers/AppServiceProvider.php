<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Season;
use Illuminate\Support\Facades\Route;
use App\Contracts\Translator;
use App\Services\AnthropicTranslator;
use App\Services\GeminiTranslator;
use App\Contracts\TextGenerator;
use App\Services\AnthropicTextGenerator;
use App\Services\GeminiTextGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TextGenerator::class, fn () => match (config('services.ai_driver')) {
            'gemini' => app(GeminiTextGenerator::class),
            default => app(AnthropicTextGenerator::class),
        });
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
