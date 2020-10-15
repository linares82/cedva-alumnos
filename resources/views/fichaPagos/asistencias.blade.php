@extends('layouts.master1')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="page-header">
            <h1>
                Asistencias
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
        <table id="simple-table" class="table table-striped table-bordered table-hover">
            <thead>
                <th>Materia</th><th>Ver Asistencias</th>
            </thead>
            <tbody>
                @foreach($materias_actuales as $a)
                <tr>
                    <td> {{$a->materia->name}}</td>
                    <td> <a href="
                        {{ route('inscripcions.listar',
                           array('plantel_f'=>$a->plantel_id, 'lectivo_f'=>$a->lectivo_id, 'grupo_f'=>$a->grupo_id,
                                 'materia_f'=>$a->materium_id, 'cliente_f'=>$a->cliente_id)
                                 ) }}" target="_blank" class="btn btn-success btn-xs"><i class="glyphicon glyphicon-eye-open"></i>Ver</a> </td>
                </tr>

                @endforeach
            </tbody>
        </table>
    </div>


</div>
@endsection
@push('scripts')
<script type="text/javascript">


</script>
@endpush

