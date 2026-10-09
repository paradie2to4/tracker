<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

/**
 * Fortify provides the login/logout/registration backend (credential check,
 * session regeneration, CSRF-protected routes). This provider supplies the
 * Blade views and the sign-up action.
 *
 * Brute-force protection uses Fortify's built-in failed-attempt limiter
 * (see 'limiters' in config/fortify.php).
 */
class FortifyServiceProvider extends ServiceProvider
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
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::createUsersUsing(CreateNewUser::class);
    }
}
