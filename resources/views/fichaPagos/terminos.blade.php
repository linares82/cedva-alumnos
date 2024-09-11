@inject('cli_funciones','App\Http\Controllers\FichaPagosController')

@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="page-header">
            <h1>
                Terminos y Condiciones
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>

                </small>
            </h1>
        </div>
    </div>
    <div class="col-md-8">

    <h2>TÉRMINOS Y CONDICIONES</h2>
    <p>Términos y Condiciones del pago de la colegiatura </p>

    <h4>OPCIONES Y FORMAS DE PAGO</h4>
    <p>Todos los precios son sujetos a cambio sin previo aviso.</p>

    <h4>Tarjeta de Crédito vía Openpay</h4>
    <p>Aceptamos tarjetas de crédito Visa, Mastercard y American Express por medio de Openpay. Los pagos mediante Tarjeta de Crédito son registrados de forma inmediata.</p>

    <h4>Tarjeta de Débito vía Openpay</h4>
    <p>Aceptamos tarjetas de Débito de las siguientes instituciones bancarias: BANAMEX, HSBC, SCOTIABANK, INBURSA, SANTANDER, IXE Y BANCO AZTECA. Los pagos son procesados por medio del servicio de Openpay. Los pagos mediante Tarjeta de Crédito son registrados de forma inmediata.</p>

    <h4>Depósito Bancario</h4>
    <p>Puede realizar su pago mediante depósito bancario en Bancomer. Los datos para completar su pago son proporcionados al momento de levantar su orden.</p>

    <h4>Transferencia Electrónica</h4>
    <p>Puede realizar su pago mediante transferencia electrónica. Los datos para completar su pago son proporcionados al momento de levantar su orden y enviados por correo electrónico. </p>


    </div>

</div>
@endsection
@push('scripts')
<script type="text/javascript">
$(document).ready(function(){});


</script>
@endpush

