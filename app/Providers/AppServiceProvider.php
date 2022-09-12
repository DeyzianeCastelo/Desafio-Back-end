<?php

namespace App\Providers;

use App\Models\Shopkeeper;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Dusterio\LumenPassport\LumenPassport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        User::observe(UserObserver::class);
        Shopkeeper::observe(Shopkeeper::class);
        Passport::ignoreMigrations();
        LumenPassport::routes($this->app);
    }
}
