<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class BsBaja extends Model
{

    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['cliente_id', 'fecha_baja', 'bnd_baja', 'fecha_reactivar', 'bnd_reactivar', 'usu_alta_id', 'usu_mod_id'];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end


    protected $dates = ['deleted_at'];

    // generated by relation command - Cliente,BsBaja - start
    public function cliente()
    {
        return $this->belongsTo('App\Cliente');
    } // end
}
