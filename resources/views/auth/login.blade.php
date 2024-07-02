@extends('layouts.masterLogin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-6 col-sm-offset-3">
            <!--
            <div class="card">
            <div class="card-body">
                <div class="widget-box widget-color-red2" id="widget-box-3">
                    <div class="widget-header widget-color-red2 widget-header-small">
                        <h6 class="widget-title" >
                            <i class="ace-icon fa fa-key"></i>
                            MENSAJE URGENTE
                        </h6>
                    </div>
                    <div class="widget-body">
                        <p>
                            Por este medio les informamos que derivado de las nuevas reformas fiscales nos acortan los tiempos para la atención de solicitud de facturas, por lo que es de suma importancia que en el momento que realicen su pago de colegiatura soliciten de forma inmediata su factura y se les pueda atender a tiempo especialmente por ser cierre de año.
                        </p>

                        <p>
                            Les recordamos que el periodo vacacional del plantel es del 22 de diciembre al 02 de enero. Colegiaturas de 2021 ya no se podrán facturar en el 2022.
                        </p>
                    </div>
                </div>
            </div>

            </div>
            -->
            <div class="card">
                <div class="card-body">
                    <div class="widget-box ui-sortable-handle" id="widget-box-3">
                        <div class="widget-header widget-header-small">
                            <h6 class="widget-title">
                                <i class="ace-icon fa fa-key"></i>
                                {{ __('Login') }}
                            </h6>
                        </div>

                        <div class="widget-body" style="">
                            <div class="widget-main padding-16">
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Matricula') }}</label>

                                        <div class="col-md-6">
                                            <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                            @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Contraseña') }}</label>

                                        <div class="col-md-6">
                                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-6 offset-md-4">
                                            <div class="checkbox">
                                                <label for="remember">
                                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    {{ __('Recordarme') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <div class="col-md-8 offset-md-4">
                                            <button type="submit" class="btn btn-primary">
                                                {{ __('Entrar') }}
                                            </button>

                                            @if (Route::has('password.request'))
                                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                                {{ __('¿Olvidaste tu contraseña?') }}
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

    @push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            bootbox.dialog({
                message: "<h1>Aviso Importante</h1><br/><span class='bigger-110'>Estimado alumno te informamos que solo tienes el mes en curso para solicita tu factura, si pagaste en día último del mes en curso solo tienes 24 hrs. para solicitarla. Utiliza nuestro servicio de factura en línea es muy sencillo y rápido para generar tu factura.</span>",
                buttons:
                {
                    "success" :
                        {
                        "label" : "<i class='ace-icon fa fa-check'></i> Aceptar!",
                        "className" : "btn-sm btn-success",
                        "callback": function() {
                            //Example.show("great success");
                        }
                    },
                }
		    });
        });
    </script>
    @endpush
