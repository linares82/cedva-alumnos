<html>
    <head>
        <style>

              h1, h3, h5, th { text-align: center; }
        table, #chart_div { margin: auto; font-family: Segoe UI; box-shadow: 10px 10px 5px #888; border: thin ridge gray; }
        th { font-size: 11px; background: #0046c3; color: #fff; max-width: 400px; padding: 2px 5px; }
        td { font-size: 10px; padding: 2px 10px; color: #000; }
        tr { background: #b8d1f3; }
        tr:nth-child(even) { background: #dae5f4; }
        tr:nth-child(odd) { background: #fff; }

        </style>

    </head>
    <body>
        <div id="printeArea">
            <h3>Lista de Asistencia del {{$asignacion->fec_inicio}} al {{$asignacion->fec_fin}} </h3>
            <table>
                <?php
                $grupo0="";
                $asistencias_acumuladas=0;
                $total_registros=0;
                ?>
                @foreach($registros as $r)

                @if($grupo0<>$r->grupo)

                    <!--<div style="page-break-after:always;"></div>-->
                        <tr>
                            <td colspan="5">
                                {{$r->denominacion }} <br/>
                                {{"Grupo: ".$r->grupo}}<br/>
                                {{"Periodo Lectivo: ".$r->lectivo}}<br/>
                                {{"Profesor: ".$r->maestro}}<br/>
                                {{"Grado: ".$r->grado}}<br/>
                                {{"Materia: ".$r->materia}}<br/>
                            </td>
                            <td colspan="{{$contador-1}}">
                                <img src="data:image/png;base64,
                                {!! base64_encode(QrCode::format('png')->size(100)->generate('Asignacion:'.$asignacion->id.
                                ', Alumnos Inscritos:'.$total_alumnos.', rango:'.$data['fecha_f'].':'.$data['fecha_t'].', token:'.$token)) !!} ">
                                <img src="{{ asset('/storage/especialidads/'.$r->logo) }}" alt="Sin logo" height="80px" ></img>
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                        <th class="altura"><strong>NO.</strong></th>
                        <th class="altura"><strong>Nombre(s)</strong></th>
                        <th class="altura"><strong>A. Paterno</strong></th>
                        <th class="altura"><strong>A. Materno</strong></th>

                        @foreach($fechas_enc as $fecha_enc)

                            <th class=""><strong >{{$fecha_enc}}</strong></th>

                        @endforeach
                        <th>Asistencias - % </th>
                        <th>Ultimo Pago Colegiatura</th>
                        </tr>
                        <?php
                        $grupo0=$r->grupo;
                        $asistencias=0;
                        $contador_linea=1;
                        ?>

                @endif
                            <tr>
                                <td>{{$contador_linea++}}</td>
                                <td>{{ $r->cliente }}</td>
                                <td>{{$r->nombre." ".$r->nombre2}}</td><td>{{$r->ape_paterno}}</td><td>{{$r->ape_materno}}</td>
                                <?php
                                    $fechas=\App\AsistenciaR::where('asignacion_academica_id',$r->asignacion)
                                            ->where('cliente_id',$r->cliente)
                                            ->whereNotIn('cliente_id',[0,2])
                                            ->get();

                                ?>
                                @foreach($fechas_enc as $fecha_enc)
                                    <?php $marcador=0; ?>

                                        @foreach($fechas as $fecha)
                                            @if($fecha_enc==$fecha->fecha)
                                            <?php
                                            $st_asistencia= \App\EstAsistencium::find($fecha->est_asistencia_id);
                                            if($st_asistencia->id<>2){
                                                $asistencias++;
                                            }

                                            $marcador=1;
                                            ?>
                                            <td class="centrar_texto"> {{$st_asistencia->name}} </td>
                                            @endif

                                        @endforeach


                                    <?php $i=1; ?>
                                    @if($marcador==0)
                                        <td></td>
                                    @endif
                                @endforeach
                                <?php
                                if($total_asistencias==0){
                                    $porcentaje=0;
                                }else{
                                    $porcentaje = round((($asistencias*100)/$total_asistencias),2);
                                }
                                $asistencias_acumuladas=$asistencias+$asistencias_acumuladas;
                                $total_registros++;
                                ?>
                                <td>{{$asistencias." - ".$porcentaje}}</td>
                                <?php
                                $caja=\App\Caja::where('cliente_id',$r->id)->latest()->first();
                                ?>
                                @if(is_object($caja))
                                <td>{{$caja->id."-".$caja->stCaja->name." - ".$caja->total." - ".$caja->fecha}}</td>
                                @endif
                                <?php
                                $asistencias=0;
                                ?>
                            </tr>


                @endforeach
                <tr>
                    <td colspan='{{4+$total_asistencias}}'>Promedio de asistencias</td>
                    <td>{{$asistencias_acumuladas/$total_registros}}</td>
                </tr>
            </table>
        </div>

        <script type="text/php">
            /*if (isset($pdf))
            {
            $font = Font_Metrics::get_font("Arial", "bold");
            $pdf->page_text(670, 580, "Pagina {PAGE_NUM} de {PAGE_COUNT}", $font, 9, array(0, 0, 0));
            }*/
        </script>

    </body>
</html>

