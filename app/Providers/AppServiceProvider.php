<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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

        // Store short, stable names in polymorphic columns (audit_logs.subject_type)
        // instead of PHP class names, so renaming a class never orphans history.
        Relation::enforceMorphMap([
            'user' => User::class,
            'product' => Product::class,
            'batch' => Batch::class,
            'organization' => Organization::class,
            'location' => Location::class,
            'shipment' => Shipment::class,
        ]);

        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        // Themed pagination: compact Previous/Next on phones, numbered pages above.
        Paginator::defaultView('pagination.productsphere');

        Password::defaults(fn () => Password::min(10)->letters()->numbers());
    }
}
