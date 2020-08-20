@extends('layouts.master1')

@section('content')
@include('error')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Personalizar su contraseña por favor</div>
                <div class="panel-body">
                    {!! Form::model($user, array('route' => array('users.updatePerfil'),'method' => 'post')) !!}
                        {{ csrf_field() }}
                        <div class="form-group col-md-6 @if($errors->has('email')) has-error @endif">
                            <label for="email-field">Mail</label>
                            {!! Form::text("email", null, array("class" => "form-control input-sm", "id" => "email-field")) !!}
                            {!! Form::hidden("id", null, array("class" => "form-control input-sm", "id" => "id-field")) !!}
                            @if($errors->has("email"))
                                <span class="help-block">{{ $errors->first("email") }}</span>
                            @endif
                        </div>
                        <div class="form-group col-md-6 @if($errors->has('password')) has-error @endif">
                            <label for="password1-field">Password</label>
                            {!! Form::text("password1", null, array("class" => "form-control input-sm", "id" => "password1-field")) !!}
                            @if($errors->has("password"))
                                <span class="help-block">{{ $errors->first("password") }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Guardar
                                </button>
                                <a class="btn btn-danger" href="{{ route('home') }}">Cancelar</a>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
