<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultaCalificacion extends Model
{
	use SoftDeletes;

	public function __construct(array $attributes = array())
	{
		parent::__construct($attributes);
	}

	//Mass Assignment
	protected $fillable = ['matricula', 'materia', 'codigo', 'creditos', 'lectivo', 'calificacion', 'tipo_calificacion', 'usu_alta_id', 'usu_mod_id', 'grupo'];

	public function usu_alta()
	{
		return $this->hasOne('App\User', 'id', 'usu_alta_id');
	} // end

	public function usu_mod()
	{
		return $this->hasOne('App\User', 'id', 'usu_mod_id');
	} // end


	protected $dates = ['deleted_at'];
}
