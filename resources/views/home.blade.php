@extends('layouts.master1')

@section('content')
<div class="page-content">
    <div class="main-content-inner">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Tablero Principal</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        Bienvenido, te sugerimos encarecidamente que actualices tus
                        <a href="{{ route('fichaAdeudos.datosFiscales') }}">datos fiscales </a>
                        para facilitar tramites futuros. Gracias.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
