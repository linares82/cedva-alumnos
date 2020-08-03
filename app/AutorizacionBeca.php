<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutorizacionBeca extends Model
{
	
	use SoftDeletes;

	public function __construct(array $attributes = array())
	{
		parent::__construct($attributes);

	}

	//Mass Assignment
	protected $fillable = ['solicitud', 'cliente_id', 'monto_inscripcion', 'monto_mensualidad', 'st_beca_id', 'usu_alta_id', 'usu_mod_id', 'file', 'lectivo_id', 'tipo_beca_id', 'motivo_beca_id', 'mensualidad_sep'];

	public function usu_alta()
	{
		return $this->hasOne('App\User', 'id', 'usu_alta_id');
	} // end

	public function usu_mod()
	{
		return $this->hasOne('App\User', 'id', 'usu_mod_id');
	} // end


	protected $dates = ['deleted_at'];

	// generated by relation command - Cliente,AutorizacionBeca - start
	public function cliente()
	{
		return $this->belongsTo('App\Cliente');
	} // end

	// generated by relation command - StBeca,AutorizacionBeca - start
	public function stBeca()
	{
		return $this->belongsTo('App\StBeca');
	} // end

	public function autCajaPlantel()
	{
		return $this->belongsTo('App\StBeca', 'aut_caja_plantel');
	}

	public function autDirPlantel()
	{
		return $this->belongsTo('App\StBeca', 'aut_dir_plantel');
	}

	public function autCajaCorp()
	{
		return $this->belongsTo('App\StBeca', 'aut_caja_corp');
	}

	public function autSerEsc()
	{
		return $this->belongsTo('App\StBeca', 'aut_ser_esc');
	}

	public function autDueno()
	{
		return $this->belongsTo('App\StBeca', 'aut_dueno');
	}

	public function lectivo()
	{
		return $this->belongsTo('App\Lectivo', 'lectivo_id');
	}

	// generated by relation command - TipoBeca,AutorizacionBeca - start
	public function tipoBeca()
	{
		return $this->belongsTo('App\TipoBeca');
	} // end

	// generated by relation command - MotivoBeca,AutorizacionBeca - start
	public function motivoBeca()
	{
		return $this->belongsTo('App\MotivoBeca');
	} // end
}
