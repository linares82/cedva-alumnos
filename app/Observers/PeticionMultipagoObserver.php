<?php

namespace App\Observers;

use App\HPeticion;
use App\PeticionMultipago;
use Illuminate\Support\Facades\Auth;

class PeticionMultipagoObserver
{
    protected $peticionMultipagoN;

    public function updating(PeticionMultipago $peticionMultipago)
    {
        $this->peticionMultipagoN = $peticionMultipago;
        $peticionMultipagoA = PeticionMultipago::find($peticionMultipago->id);
        foreach($peticionMultipagoA->toArray() as $llave=>$valor){
            if($valor<>$this->peticionMultipagoN->$llave){
                //echo $llave." - ".$valor;
                $input['peticion_multipagos_id']=$this->peticionMultipagoN->id;
                $input['campo']=$llave;
                $input['valor_nuevo']=$this->peticionMultipagoN->$llave;
                $input['valor_anterior']=(is_null($valor) ? 'nulo' : $valor);
                $input['usu_alta_id']=isset(Auth::user()->name) ? Auth::user()->name : 1;
                $input['usu_mod_id']=isset(Auth::user()->name) ? Auth::user()->name : 1;
                HPeticion::create($input);
            }

        }
        //dd($this->cliente->nombre."-".$vcliente->nombre);
    }
}
