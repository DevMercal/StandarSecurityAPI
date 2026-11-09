<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\GenericObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        User::observe(UserObserver::class);

        Role::observe(GenericObserver::class);
        Permission::observe(GenericObserver::class);
    }
}
