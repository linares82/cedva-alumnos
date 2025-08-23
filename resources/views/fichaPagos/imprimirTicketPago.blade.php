<html>
<head>
    <style>

        body{
            font-family:"Arial";
            font-size:large;
        }

        @media print {
            body{
            font-family:"Arial";
            font-size:large;
            }
        }
    </style>
</head>

<body >
@php
$sucursales=App\Plantel::where('rfc',$cliente->plantel->rfc)->where('st_plantel_id',1)->get();
@endphp

<div id="printeArea">
    <table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
        <tr>
            <td align="center" colspan="2">
                @if(isset($combinacion->especialidad->imagen))
                    <img src="{{env('APP_MAIN_URL').'/storage/especialidads/'.$combinacion->especialidad->imagen }}"
                        alt='img' style='width: 100px;
                        margin: 4px;'>
                @endif

                @php
                $cadena='Id:'.$cliente->id.
                        '; Nombre:'.$cliente->nombre.' '.$cliente->nombre2.' '.$cliente->ape_paterno.' '.$cliente->ape_materno.
                        '; Plantel:'.$cliente->plantel->razon;
                $cadena_pie='cliente:'.$cliente->id.'; Plantel:'.$cliente->plantel->id.";Caja:".$caja->consecutivo;

                    if($caja->cajaLn->cajaConcepto->id==1){
                        $cadena=$cadena.';'.$caja->cajaLn->cajaConcepto->name." (".$caja->cajaLn->adeudo->fecha_pago.")";
                    }else{
                        $cadena=$cadena.';'.$caja->cajaLn->cajaConcepto->name;
                    }


                $cadena=$cadena.'; Total:'.number_format($caja->total, 2).'; '.$impresion_token->toke_unico;
                @endphp


                <img src="data:image/png;base64,
                                    {!! base64_encode(QrCode::format('png')->size(80)->generate($cadena_pie)) !!} ">

            </td>
        </tr>
        <tr><td colspan="2" align="center" >{{$cliente->plantel->nombre_corto}}</td></tr>
        <tr><td colspan="2" align="center">RFC: {{$cliente->plantel->rfc}}</td></tr>
        <tr>
            <td colspan="2" align="center">
                {{ $cliente->plantel->calle }},
                {{ $cliente->plantel->no_int }},
                {{ $cliente->plantel->no_ext }},
                {{ $cliente->plantel->colonia }},
                {{ $cliente->plantel->municipio }},
                {{ $cliente->plantel->estado }},
                MÉXICO

            </td>
        </tr>
      <!--
        <tr>
            <td colspan="2" >

                @if($combinacion)
                Estudios:{{$combinacion->especialidad->name." / ".
                           $combinacion->nivel->name." / ".
                           $combinacion->grado->name}}
                @endif
            </td>
        </tr>
    -->
        <tr>
            <td colspan="2" >
                @if($caja->st_caja_id==1)
                    Ticket {{$caja->consecutivo}} pagado el {{$caja->fecha}}
                @elseif($caja->st_caja_id==2)
                    Ticket {{$caja->consecutivo}} cancelado el {{$caja->fecha}}
                @else
                    Ticket {{$caja->consecutivo}} en espera de su pago
                @endif
            </td>
        </tr>

        <tr>
            <td colspan="2">
                Atendido por: En linea
                Forma Pago: {{ $caja->formaPago->name }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Alumno: {{ $cliente->matricula }} - {{ $cliente->id }} - {{$cliente->nombre." ".$cliente->nombre2." ".$cliente->ape_paterno." ".$cliente->ape_materno}}
            </td>
        </tr>
        <tr></tr>
        <tr>
            <td width="50%">
                Concepto de Pago:
            </td>

            <td>

            </td>

        </tr>
        <?php $total=0; ?>
        <tr>
            <td>
                @php
                $conceptoMensualidad=explode(' ',$caja->cajaLn->cajaConcepto->name);

                @endphp
                <!--
                @if($caja->cajaLn->cajaConcepto->id==1)
                    {{$caja->cajaLn->cajaConcepto->name." (".$caja->cajaLn->adeudo->fecha_pago.")"}}
                @else
                    @if($conceptoMensualidad[0]="Mensualidad")
                        {{ $conceptoMensualidad[1] }}
                    @else
                    {{$caja->cajaLn->cajaConcepto->name}}
                    @endif
                @endif
                -
                {{ number_format($caja->cajaLn->total, 2) }}
                -->
                {{ $caja->cajaLn->cajaConcepto->leyenda_factura }} - {{ number_format($caja->cajaLn->total, 2) }}
            </td>

            <td>


            </td>

        </tr>

        <tr>
            <td>
                Total:{{ number_format($caja->cajaLn->total, 2) }}
                <br/>{{$totalLetra}} {{round($centavos)."/100 M.N."}}
            </td>

            <td align="right">  </td>
        </tr>
        <tr>
            <tr><td colspan="2">Fecha Impresion: {{$fecha}}</td></tr>
        </tr>
        <tr>

            @php


            //dd($fecha);
            $lugarFecha = \Carbon\Carbon::createFromFormat('Y-m-d', $caja->fecha);
            //dd($lugarFecha);
            $mes = App\Mese::find($lugarFecha->month);
            $fechaLetra = $caja->plantel->municipio . ", " .
                $caja->plantel->estado . "; a " .
                $lugarFecha->day . " de " .
                $mes->name . " de " . $lugarFecha->year;
             //dd($fechaLetra);
            @endphp
            <td colspan="2">Fecha Pago:{{$fechaLetra}}

            </td>

        </tr>

        <tr>
            <td colspan=3>
            <table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
                <tr>
                    @if($cliente->plantel->matriz_id>0 and !is_null($cliente->plantel->matriz_id))
                    <td>
                        {{$cliente->plantel->matriz->nombre_corto}}<br/>
                        {{$cliente->plantel->matriz->rfc}}<br/>
                        {{$cliente->plantel->matriz->calle}} {{$cliente->plantel->matriz->no_int}}, {{$cliente->plantel->matriz->colonia}}, <br/>
                        {{$cliente->plantel->matriz->municipio}}, {{$cliente->plantel->matriz->estado}}, C.P. {{$cliente->plantel->matriz->cp}}<br/>
                    </td>
                @endif
                    @foreach($sucursales as $sucursal)
                    @if($sucursal->id<>$cliente->plantel_id)
                    <td>
                        {{$sucursal->nombre_corto}}<br/>
                        {{$sucursal->rfc}}<br/>
                        {{$sucursal->calle}} {{$sucursal->no_int}}, {{$sucursal->colonia}}, <br/>
                        {{$sucursal->municipio}}, {{$sucursal->estado}}, C.P. {{$sucursal->cp}}<br/>
                    </td>
                    @endif
                    @endforeach
                </tr>
            </table>
            <td>
        </tr>

    </table>

<br>

<table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
    <tr>
        <td align="center" colspan="2">
            @if(isset($combinacion->especialidad->imagen))
                <img src="{{env('APP_MAIN_URL').'/storage/especialidads/'.$combinacion->especialidad->imagen }}"
                    alt='img' style='width: 100px;
                    margin: 4px;'>
            @endif

            @php
            $cadena='Id:'.$cliente->id.
                    '; Nombre:'.$cliente->nombre.' '.$cliente->nombre2.' '.$cliente->ape_paterno.' '.$cliente->ape_materno.
                    '; Plantel:'.$cliente->plantel->razon;
            $cadena_pie='cliente:'.$cliente->id.'; Plantel:'.$cliente->plantel->id.";Caja:".$caja->consecutivo;
            if($caja->cajaLn->cajaConcepto->id==1){
                        $cadena=$cadena.';'.$caja->cajaLn->cajaConcepto->name." (".$caja->cajaLn->adeudo->fecha_pago.")";
                    }else{
                        $cadena=$cadena.';'.$caja->cajaLn->cajaConcepto->name;
                    }
            $cadena=$cadena.'; Total:'.number_format($caja->total, 2).'; '.$impresion_token->toke_unico;
            @endphp


            <img src="data:image/png;base64,
                                {!! base64_encode(QrCode::format('png')->size(80)->generate($cadena_pie)) !!} ">

        </td>
    </tr>
    <tr><td colspan="2" align="center" >{{$cliente->plantel->nombre_corto}}</td></tr>
    <tr><td colspan="2" align="center">RFC: {{$cliente->plantel->rfc}}</td></tr>
    <tr>
        <td colspan="2" align="center">
            {{ $cliente->plantel->calle }},
            {{ $cliente->plantel->no_int }},
            {{ $cliente->plantel->no_ext }},
            {{ $cliente->plantel->colonia }},
            {{ $cliente->plantel->municipio }},
            {{ $cliente->plantel->estado }},
            MÉXICO

        </td>
    </tr>
  <!--
    <tr>
        <td colspan="2" >

            @if($combinacion)
            Estudios:{{$combinacion->especialidad->name." / ".
                       $combinacion->nivel->name." / ".
                       $combinacion->grado->name}}
            @endif
        </td>
    </tr>
-->
    <tr>
        <td colspan="2" >
            @if($caja->st_caja_id==1)
                Ticket {{$caja->consecutivo}} pagado el {{$caja->fecha}}
            @elseif($caja->st_caja_id==2)
                Ticket {{$caja->consecutivo}} cancelado el {{$caja->fecha}}
            @else
                Ticket {{$caja->consecutivo}} en espera de su pago
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="2">
            Atendido por: En linea
            Forma Pago: {{ $caja->formaPago->name }}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            Alumno: {{ $cliente->matricula }} - {{ $cliente->id }} - {{$cliente->nombre." ".$cliente->nombre2." ".$cliente->ape_paterno." ".$cliente->ape_materno}}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td width="50%">
            Concepto de Pago:
        </td>

        <td>

        </td>

    </tr>
    <?php $total=0; ?>

    <tr>
        <td>
            @php
            $conceptoMensualidad=explode(' ',$caja->cajaLn->cajaConcepto->name);

            @endphp
            <!--
            @if($caja->cajaLn->cajaConcepto->id==1)
                {{$caja->cajaLn->cajaConcepto->name." (".$caja->cajaLn->adeudo->fecha_pago.")"}}
            @else
                @if($conceptoMensualidad[0]="Mensualidad")
                    {{ $conceptoMensualidad[1] }}
                @else
                {{$caja->cajaLn->cajaConcepto->name}}
                @endif
            @endif
            -
            {{ number_format($caja->cajaLn->total, 2) }}
            -->
            {{ $caja->cajaLn->cajaConcepto->leyenda_factura }} - {{ number_format($caja->cajaLn->total, 2) }}
        </td>

        <td>


        </td>

    </tr>



    <tr>
        <td>
            Total:{{ number_format($caja->cajaLn->total, 2) }}
            <br/>{{$totalLetra}} {{round($centavos)."/100 M.N."}}
        </td>

        <td align="right">  </td>
    </tr>
    <tr>
        <tr><td colspan="2">Fecha Impresion: {{$fecha}}</td></tr>
    </tr>
    <tr>

        @php


        //dd($fecha);
        $lugarFecha = \Carbon\Carbon::createFromFormat('Y-m-d', $caja->fecha);
        //dd($lugarFecha);
        $mes = App\Mese::find($lugarFecha->month);
        $fechaLetra = $caja->plantel->municipio . ", " .
            $caja->plantel->estado . "; a " .
            $lugarFecha->day . " de " .
            $mes->name . " de " . $lugarFecha->year;
         //dd($fechaLetra);
        @endphp
        <td colspan="2">Fecha Pago:{{$fechaLetra}}
        </td>

    </tr>

    <tr>
        <td colspan=3>
        <table style="width:100%;height:auto;border:1px solid #ccc;font-size: 0.70em;">
            <tr>
                @if($cliente->plantel->matriz_id>0 and !is_null($cliente->plantel->matriz_id))
                    <td>
                        {{$cliente->plantel->matriz->nombre_corto}}<br/>
                        {{$cliente->plantel->matriz->rfc}}<br/>
                        {{$cliente->plantel->matriz->calle}} {{$cliente->plantel->matriz->no_int}}, {{$cliente->plantel->matriz->colonia}}, <br/>
                        {{$cliente->plantel->matriz->municipio}}, {{$cliente->plantel->matriz->estado}}, C.P. {{$cliente->plantel->matriz->cp}}<br/>
                    </td>
                @endif
                @foreach($sucursales as $sucursal)
                @if($sucursal->id<>$cliente->plantel_id)
                <td>
                    {{$sucursal->nombre_corto}}<br/>
                    {{$sucursal->rfc}}<br/>
                    {{$sucursal->calle}} {{$sucursal->no_int}}, {{$sucursal->colonia}}, <br/>
                    {{$sucursal->municipio}}, {{$sucursal->estado}}, C.P. {{$sucursal->cp}}<br/>
                </td>
                @endif
                @endforeach
            </tr>
        </table>
        <td>
    </tr>

</table>

</div>

<script type="text/php">
    if (isset($pdf)){
        $font = $fontMetrics->getFont("Arial", "bold");
        $pdf->page_text(700, 590, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 10, array(0, 0, 0));
    }
</script>




</body>
</html>
