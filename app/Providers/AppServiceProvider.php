<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // Outside production, fail loudly on N+1 lazy loading, on attributes
        // that are not fillable, and on reading columns that were not selected.
        Model::shouldBeStrict(! $this->app->isProduction());

        Password::defaults(fn () => Password::min(10)->letters()->numbers());
    }
}
