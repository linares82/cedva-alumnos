<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\AdeudoPagoOnLine;
use Illuminate\Http\Request;
use Auth;
use App\Http\Requests\updateAdeudoPagoOnLine;
use App\Http\Requests\createAdeudoPagoOnLine;

class AdeudoPagoOnLinesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$adeudoPagoOnLines = AdeudoPagoOnLine::getAllData($request);

		return view('adeudoPagoOnLines.index', compact('adeudoPagoOnLines'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return view('adeudoPagoOnLines.create')
			->with( 'list', AdeudoPagoOnLine::getListFromAllRelationApps() );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function store(createAdeudoPagoOnLine $request)
	{

		$input = $request->all();
		$input['usu_alta_id']=Auth::user()->id;
		$input['usu_mod_id']=Auth::user()->id;

		//create data
		AdeudoPagoOnLine::create( $input );

		return redirect()->route('adeudoPagoOnLines.index')->with('message', 'Registro Creado.');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id, AdeudoPagoOnLine $adeudoPagoOnLine)
	{
		$adeudoPagoOnLine=$adeudoPagoOnLine->find($id);
		return view('adeudoPagoOnLines.show', compact('adeudoPagoOnLine'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id, AdeudoPagoOnLine $adeudoPagoOnLine)
	{
		$adeudoPagoOnLine=$adeudoPagoOnLine->find($id);
		return view('adeudoPagoOnLines.edit', compact('adeudoPagoOnLine'))
			->with( 'list', AdeudoPagoOnLine::getListFromAllRelationApps() );
	}

	/**
	 * Show the form for duplicatting the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function duplicate($id, AdeudoPagoOnLine $adeudoPagoOnLine)
	{
		$adeudoPagoOnLine=$adeudoPagoOnLine->find($id);
		return view('adeudoPagoOnLines.duplicate', compact('adeudoPagoOnLine'))
			->with( 'list', AdeudoPagoOnLine::getListFromAllRelationApps() );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @param Request $request
	 * @return Response
	 */
	public function update($id, AdeudoPagoOnLine $adeudoPagoOnLine, updateAdeudoPagoOnLine $request)
	{
		$input = $request->all();
		$input['usu_mod_id']=Auth::user()->id;
		//update data
		$adeudoPagoOnLine=$adeudoPagoOnLine->find($id);
		$adeudoPagoOnLine->update( $input );

		return redirect()->route('adeudoPagoOnLines.index')->with('message', 'Registro Actualizado.');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id,AdeudoPagoOnLine $adeudoPagoOnLine)
	{
		$adeudoPagoOnLine=$adeudoPagoOnLine->find($id);
		$adeudoPagoOnLine->delete();

		return redirect()->route('adeudoPagoOnLines.index')->with('message', 'Registro Borrado.');
	}

}
