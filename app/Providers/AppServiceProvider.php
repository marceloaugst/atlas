<?php

namespace App\Providers;

use App\Services\GoogleBooks\GoogleBooksClient;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
