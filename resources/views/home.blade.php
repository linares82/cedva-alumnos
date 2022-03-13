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
            </div>

        </div>
    </div>

</div>
@endsection
