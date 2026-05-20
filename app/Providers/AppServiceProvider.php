<?php

namespace App\Providers;

use App\Models\Product;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            \App\Contracts\Repositories\API\AuthRepositoryInterface::class,
            \App\Repositories\API\AuthRepository::class
        );
        $this->app->bind(
            \App\Contracts\Repositories\API\ProductRepositoryInterface::class,
            \App\Repositories\API\ProductRepository::class
        );

        // Services
        $this->app->bind(
            \App\Contracts\Services\API\AuthServiceInterface::class,
            \App\Services\API\AuthService::class
        );
        $this->app->bind(
            \App\Contracts\Services\API\ProductServiceInterface::class,
            \App\Services\API\ProductService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
    }
}
