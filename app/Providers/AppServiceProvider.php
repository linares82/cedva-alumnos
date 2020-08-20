<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\PagoObserver;
use App\Pago;
use App\User;
use Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Pago::observe(PagoObserver::class);
    }
}
