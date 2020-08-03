<?php

namespace App;

use App\Traits\GetAllDataTrait;
use App\Traits\RelationManagerTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPago extends Model
{
    use RelationManagerTrait, GetAllDataTrait;
    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['name', 'activo', 'usu_alta_id', 'usu_mod_id'];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end

    // generated by relation command - CuentaContable,PlanPagoLn - start
    public function lineas()
    {
        return $this->hasMany('App\PlanPagoLn');
    } // end

    protected $dates = ['deleted_at'];

    public function turnos()
    {
        return $this->belongsToMany('App\Turno', 'plan_pago_turno', 'plan_pago_id', 'turno_id');
    } // end
}
