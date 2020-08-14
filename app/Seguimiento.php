<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Seguimiento extends Model
{

    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['cliente_id', 'st_seguimiento_id', 'mes', 'usu_alta_id', 'usu_mod_id'];

    public function usu_alta()
    {
        return $this->hasOne('App\User', 'id', 'usu_alta_id');
    } // end

    public function usu_mod()
    {
        return $this->hasOne('App\User', 'id', 'usu_mod_id');
    } // end

    public function cliente()
    {
        return $this->belongsTo('App\Cliente');
    } // end
    public function stSeguimiento()
    {
        return $this->belongsTo('App\StSeguimiento');
    } // end

    protected $dates = ['deleted_at'];
}
