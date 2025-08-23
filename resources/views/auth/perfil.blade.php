@extends('layouts.master1')

@section('content')
@include('error')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Personalizar su contraseña por favor</div>
                <div class="panel-body">
                    <form action="{{route('users.updatePerfil', $user->id)}}" id="frm" method="POST">
                        @csrf
                        <div class="form-group col-md-6 @if($errors->has('email')) has-error @endif">
                            <label for="email">Mail</label>
                            <input type="text" value="{{ $user->email }}" name="email" id="email" class="form-control input-sm">
                            <input type="hidden" value="{{ $user->id }}" name="id" id="id" class="form-control input-sm">
                            @if($errors->has("email"))
                                <span class="help-block">{{ $errors->first("email") }}</span>
                            @endif
                        </div>
                        <div class="form-group col-md-6 @if($errors->has('password')) has-error @endif">
                            <label for="password1">Password</label>
                            <input type="text" value="{{ $user->password1 }}" name="password1" id="password1" class="form-control input-sm">
                            @if($errors->has("password"))
                                <span class="help-block">{{ $errors->first("password") }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" id="btnGuardar" class="btn btn-primary">
                                    Guardar
                                </button>
                                <a class="btn btn-danger" href="/">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
$(document).ready(function(){
    $("#btnGuardar").on('click', function(e) {
    $(this).prop('disabled', true);
    $('#frm').submit();
    });

});

function formatoFecha(texto){
  return texto.replace(/^(\d{4})-(\d{2})-(\d{2})$/g,'$1/$2/$3');
}

</script>
@endpush
