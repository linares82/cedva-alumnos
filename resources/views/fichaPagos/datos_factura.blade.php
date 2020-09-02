@extends('layouts.master1')

@section('content')
<div class="row">
    {!! Form::model($cliente, array('route' => array('fichaAdeudos.confirmarFactura', $adeudo_pago_on_line),'method' => 'post')) !!}
            <div class="form-group col-md-4 @if($errors->has('tipo_persona_id')) has-error @endif">
                <label for="tipo_persona_id-field">Tipo Persona</label>
                {!! Form::select("tipo_persona_id", $tipoPersonas, null, array("class" => "form-control select_seguridad", "id" => "tipo_persona_id-field", 'style'=>'width:100%')) !!}
                @if($errors->has("tipo_persona_id"))
                <span class="help-block">{{ $errors->first("tipo_persona_id") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('frazon')) has-error @endif">
                <label for="frazon-field">Nombre o Razon Social</label>
                {!! Form::text("frazon", null, array("class" => "form-control input-sm", "id" => "frazon-field")) !!}
                @if($errors->has("frazon"))
                <span class="help-block">{{ $errors->first("frazon") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('frfc')) has-error @endif">
                <label for="frfc-field">RFC</label>
                {!! Form::text("frfc", null, array("class" => "form-control input-sm", "id" => "frfc-field")) !!}
                @if($errors->has("frfc"))
                <span class="help-block">{{ $errors->first("frfc") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcalle')) has-error @endif" style="clear:left;">
                <label for="fcalle-field">Calle</label>
                {!! Form::text("fcalle", null, array("class" => "form-control input-sm", "id" => "fcalle-field")) !!}
                @if($errors->has("fcalle"))
                <span class="help-block">{{ $errors->first("fcalle") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fno_exterior')) has-error @endif">
                <label for="fno_exterior-field">No. Exterior</label>
                {!! Form::text("fno_exterior", null, array("class" => "form-control input-sm", "id" => "fno_exterior-field")) !!}
                @if($errors->has("fno_exterior"))
                <span class="help-block">{{ $errors->first("fno_exterior") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fno_interior')) has-error @endif">
                <label for="fno_interior-field">No. Interior</label>
                {!! Form::text("fno_interior", null, array("class" => "form-control input-sm", "id" => "fno_interior-field")) !!}
                @if($errors->has("fno_interior"))
                <span class="help-block">{{ $errors->first("fno_interior") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcolonia')) has-error @endif">
                <label for="fcolonia-field">Colonia</label>
                {!! Form::text("fcolonia", null, array("class" => "form-control input-sm", "id" => "fcolonia-field")) !!}
                @if($errors->has("fcolonia"))
                <span class="help-block">{{ $errors->first("fcolonia") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fciudad')) has-error @endif">
                <label for="fciudad-field">Ciudad</label>
                {!! Form::text("fciudad", null, array("class" => "form-control input-sm", "id" => "fciudad-field")) !!}
                @if($errors->has("fciudad"))
                <span class="help-block">{{ $errors->first("fciudad") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('festado')) has-error @endif">
                <label for="festado-field">Estado</label>
                {!! Form::text("festado", null, array("class" => "form-control input-sm", "id" => "festado-field")) !!}
                @if($errors->has("festado"))
                <span class="help-block">{{ $errors->first("festado") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fpais')) has-error @endif">
                <label for="fpais-field">Pais</label>
                {!! Form::text("fpais", null, array("class" => "form-control input-sm", "id" => "fpais-field")) !!}
                @if($errors->has("fpais"))
                <span class="help-block">{{ $errors->first("fpais") }}</span>
                @endif
            </div>
            <div class="form-group col-md-4 @if($errors->has('fcp')) has-error @endif">
                <label for="fcp-field">C.P.</label>
                {!! Form::text("fcp", null, array("class" => "form-control input-sm", "id" => "fcp-field")) !!}
                @if($errors->has("fcp"))
                <span class="help-block">{{ $errors->first("fcp") }}</span>
                @endif
            </div>

            <div class="row">
            </div>

            <div class="well well-sm">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a class="btn btn-link pull-right" href="{{ route('fichaAdeudos.index') }}"><i class="glyphicon glyphicon-backward"></i>  Regresar</a>
            </div>

            {!! Form::close() !!}
</div>
@endsection
@push('scripts')
<script type="text/javascript">
$(document).ready(function(){


});



</script>
@endpush
