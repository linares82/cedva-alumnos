<?php

namespace App\Providers;

use Auth;
use App\Pago;
use App\User;
use App\PeticionMultipago;
use App\Observers\PagoObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\PeticionMultipagoObserver;

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
        PeticionMultipago::observe(PeticionMultipagoObserver::class);

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
