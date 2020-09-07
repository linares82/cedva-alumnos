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

        //Permite descargar un archivo sin crearlo, solo con una cadena
        \Response::macro('attachment', function ($content) {

            $headers = [
                'Content-type'        => 'text/xml',
                'Content-Disposition' => 'attachment; filename="xmlFile.xml"',
            ];

            return \Response::make($content, 200, $headers);
        });
    }
}
