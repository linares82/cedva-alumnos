<?php

namespace App\Console\Commands;

use App\Pago;
use App\PeticionMultipago;
use Illuminate\Console\Command;

class Prb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ian:prb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'pruebas de lo que sea';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
        $pago = Pago::find(2495);
        $pago->bnd_pagado = 1;
        $pago->save();
        */
        $peticion=PeticionMultipago::find(1);
        $peticion->mp_amount=$peticion->mp_amount+1;
        $peticion->save();
    }
}
