<?php

namespace App\Providers;

use App\Services\GoogleBooks\GoogleBooksClient;
use App\Services\Rag\AnthropicClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleBooksClient::class, fn () => new GoogleBooksClient(
            baseUrl: config('services.google_books.url'),
            apiKey: config('services.google_books.key'),
        ));

        $this->app->singleton(AnthropicClient::class, fn () => new AnthropicClient(
            baseUrl: config('services.anthropic.url'),
            apiKey: config('services.anthropic.key'),
            model: config('services.anthropic.model'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
