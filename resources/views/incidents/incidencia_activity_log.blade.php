@extends('layouts.app')

@section('content')
@if (auth()->user()->is_admin)
<div class="panel panel-primary">
    <div class="panel-heading">Incidentes Log</div>

    <div class="panel-body">
        @if (session('notification'))
            <div class="alert alert-success">
                {{ session('notification') }}
            </div>
        @endif

        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
&nbsp;
        <div class="panel panel-success">
            <form action="{{route('activity/log/incidencia')}}" method="GET" class="navbar-form" role="search">
                <div class="form-group">
                    <input type="text" name= "name" id="name" class="form-control" placeholder="Buscador" required/>
                </div>
                <button type="submit" name="Search" class="btn-sm btn-primary">BUSCAR</button>
                <a href="/activity/log/incidencia" class="btn btn-sm btn-success">ACTUALIZAR</a>
            </form>
			<div class="panel-heading">
            
				<h3 class="panel-title">Tabla de Logs - Incidencias</h3>
			</div>
			<div class="panel-body" style="overflow-x:auto;">
				<table class="table table-bordered" style="overflow-x:auto;">
					<thead>
						        <tr>
                                    <th>Id</th>
                                    <th bgcolor="#5D7B9D"><font color="#fff">Responsable</th>
                                    <th>Title</th>
                                    <th bgcolor="#5D7B9D"><font color="#fff">Accion Realizada</th>
                                    <th>Severidad</th>
                                    <th>Cliente</th>
                                    <th>Soporte</th>
                                    <th>Producto</th>
                                    <th bgcolor="#5D7B9D"><font color="#fff">Fecha</th>
                                </tr>
            </thead>
            <tbody>
                @foreach ($activityLog as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->responsable }}</td>
                                    <td>{{ $log->title }}</td>
                                    <td>{{ $log->modyfy_user}}</td>
                                    <td>{{ $log->severity }}</td>
                                    <td>{{ $log->client_id}}</td>
                                    <td>{{ $log->support_id}}</td>
                                    <td>{{ $log->producto_id}}</td>
                                    <td>{{ $log->date_time}}</td>
         						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
@endif
@endsection