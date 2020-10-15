@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="page-header">
            <h1>
                Historial Academico
                <small>
                    <i class="ace-icon fa fa-angle-double-right"></i>

                </small>
            </h1>
        </div>
    </div>
    <div class="col-md-8">
        <div class="profile-user-info profile-user-info-striped">
            <div class="profile-info-row">
                <div class="profile-info-name"> Escuela </div>
                <div class="profile-info-value">
                     {{ $cliente->plantel->nombre_corto }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Alumno </div>
                <div class="profile-info-value">
                     {{ $cliente->nombre }} {{ $cliente->nombre2 }} {{ $cliente->ape_paterno }} {{ $cliente->ape_materno }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Matricula </div>
                <div class="profile-info-value">
                     {{ Auth::user()->name }}
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> Email </div>
                <div class="profile-info-value">
                     {{ $cliente->mail }}
                </div>
            </div>
        </div>

    </div>
    <div class="col-md-12">
        <h3>Materias en Curso</h3>
        <table id="simple-table" class="table table-striped table-bordered table-hover">

            <tbody>
                @foreach($materias_actuales as $a)
                <tr>
                    <td colspan='3'> {{$a->materia->name}}</td>

                    <td colspan="5">
                        <table id="simple-table" class="table table-striped  table-bordered table-hover">

                            <tbody>
                                @php
                                    $cantidad_materias=0;
                                    //$a->load('calificaciones');
                                    $calificaciones=\App\Calificacion::where('hacademica_id',$a->id)->get();
                                @endphp

                                @foreach($a->calificaciones as $cali)
                                <tr>

                                    <tr>
                                    @php
                                    //$cali->load('tpoExamen');
                                    //$cali->load('calificacionPonderacions');
                                    @endphp
                                        <strong>Tipo de examen:{{$cali->tpoExamen->name}} - </strong>
                                        @foreach($cali->calificacionPonderacions as $calificacionPonderacion)
                                            <th class="centrar_texto">{{$calificacionPonderacion->cargaPonderacion->name}}</th>
                                        @endforeach
                                        <th>Promedio</th>
                                    </tr>
                                    <tr>
                                        <strong>Calificacion: {{$cali->calificacion}}</strong>
                                        @php
                                            $cantidad_materias_validas=0;
                                            $sumatoria_calificacions_validas=0;
                                        @endphp
                                        @foreach($cali->calificacionPonderacions as $calificacionPonderacion)
                                            @if(is_null($calificacionPonderacion->deleted_at))
                                            <td class="centrar_texto">{{round($calificacionPonderacion->calificacion_parcial,2)}}</td>
                                            @php
                                             if($calificacionPonderacion->calificacion_parcial>0){
                                                $cantidad_materias_validas++;
                                                $sumatoria_calificacions_validas=$sumatoria_calificacions_validas+$calificacionPonderacion->calificacion_parcial;
                                            }
                                            @endphp
                                            @endif
                                        @endforeach
                                        @if($cantidad_materias_validas>0)
                                        @if(($sumatoria_calificacions_validas/$cantidad_materias_validas)>=6)
                                        <td>{{ round($sumatoria_calificacions_validas/$cantidad_materias_validas) }}</td>
                                        @else
                                        <td> {{ intdiv(($sumatoria_calificacions_validas/$cantidad_materias_validas),1) }}</td>
                                        @endif
                                        @else
                                            <td>0</td>
                                        @endif
                                    </tr>
                                <tr>
                                @php
                                    $sumatoria_calificaciones=$cali->calificacion;
                                    $cantidad_materias=$cantidad_materias+1;
                                @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </tbody>
            </tr>

            </tbody>
        </table>
        <h3>Materias Terminadas</h3>
        <table id="simple-table" class="table  table-bordered table-hover">
            <thead>
                <th>Matricula</th><th>Materia</th><th>Clave</th><th>Créditos</th><th>Periodo</th><th>Calificacion</th><th>Tipo Evaluacion</th>
            </thead>
            <tbody>
                @foreach($materias_terminadas as $terminada)
                <tr>
                    <td>{{ $terminada->matricula }}</td><td>{{ $terminada->materia }}</td><td>{{ $terminada->clave }}</td><td>{{ $terminada->creditos }}</td><td>{{ $terminada->periodo }}</td><td>{{ $terminada->calificacion }}</td><td>{{ $terminada->tipo_examen }}</td>
                </tr>
                @endforeach
                @if(isset($consulta_calificaciones) and count($consulta_calificaciones)>0)
                    @foreach($consulta_calificaciones as $registro)
                        <tr>
                            <td>{{$cliente->matricula}}</td>
                            <td>{{$registro->materia}}</td>
                            <td>{{$registro->codigo}}</td>
                            <td>{{$registro->creditos}}</td>
                            <td>{{$registro->lectivo}}</td>
                            <td>{{$registro->calificacion}}</td>
                            <td>{{$registro->tipo_examen}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

</div>
@endsection
@push('scripts')
<script type="text/javascript">


</script>
@endpush

