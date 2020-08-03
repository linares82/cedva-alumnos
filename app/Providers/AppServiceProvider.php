<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\PagoObserver;
use App\Pago;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Pago::observe(PagoObserver::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
