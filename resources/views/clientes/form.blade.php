@extends('layouts.app')
@section('titulo', $cliente->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('contenido')
<form method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}">
    @csrf @if($cliente->exists) @method('PUT') @endif

    @if($errors->any())<div class="alert alert-danger">{!! implode('<br>', $errors->all()) !!}</div>@endif

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><i class="fas fa-id-card"></i> Datos personales</div>
                <div class="card-body row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cliente->nombre) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos', $cliente->apellidos) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">NIF/CIF</label>
                        <input type="text" name="nif_cif" class="form-control" value="{{ old('nif_cif', $cliente->nif_cif) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tipo</label>
                        <select name="tipo" class="form-control">
                            <option value="particular" @selected(old('tipo', $cliente->tipo)=='particular')>Particular</option>
                            <option value="empresa" @selected(old('tipo', $cliente->tipo)=='empresa')>Empresa</option>
                            <option value="vip" @selected(old('tipo', $cliente->tipo)=='vip')>VIP</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Fecha nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $cliente->telefono) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Móvil</label>
                        <input type="text" name="movil" class="form-control" value="{{ old('movil', $cliente->movil) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="fas fa-map-marker-alt"></i> Dirección de entrega principal</div>
                <div class="card-body row">
                    <div class="col-md-8 mb-3"><label class="form-label fw-bold">Dirección</label><input type="text" name="direccion" class="form-control" value="{{ old('direccion', $cliente->direccion) }}"></div>
                    <div class="col-md-2 mb-3"><label class="form-label fw-bold">Nº</label><input type="text" name="numero" class="form-control" value="{{ old('numero', $cliente->numero) }}"></div>
                    <div class="col-md-2 mb-3"><label class="form-label fw-bold">Piso</label><input type="text" name="piso" class="form-control" value="{{ old('piso', $cliente->piso) }}"></div>
                    <div class="col-md-3 mb-3"><label class="form-label fw-bold">CP</label><input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal', $cliente->codigo_postal) }}"></div>
                    <div class="col-md-5 mb-3"><label class="form-label fw-bold">Ciudad</label><input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', $cliente->ciudad) }}"></div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-bold">Provincia</label><input type="text" name="provincia" class="form-control" value="{{ old('provincia', $cliente->provincia) }}"></div>
                    <div class="col-md-12 mb-3"><label class="form-label fw-bold">Referencia para repartidor</label><textarea name="referencia_direccion" class="form-control" rows="2">{{ old('referencia_direccion', $cliente->referencia_direccion) }}</textarea></div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><i class="fas fa-cogs"></i> Configuración CRM</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descuento fijo (%)</label>
                        <input type="number" step="0.01" name="descuento_fijo" class="form-control" value="{{ old('descuento_fijo', $cliente->descuento_fijo ?? 0) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notas internas</label>
                        <textarea name="notas" class="form-control" rows="3">{{ old('notas', $cliente->notas) }}</textarea>
                    </div>
                    <div class="form-check form-switch mb-2"><input type="checkbox" class="form-check-input" name="acepta_marketing" id="am" value="1" {{ old('acepta_marketing', $cliente->acepta_marketing ?? false) ? 'checked' : '' }}><label for="am" class="form-check-label">Acepta comunicaciones de marketing</label></div>
                    @if($cliente->exists)<div class="form-check form-switch"><input type="checkbox" class="form-check-input" name="activo" id="ac" value="1" {{ old('activo', $cliente->activo) ? 'checked' : '' }}><label for="ac" class="form-check-label">Cliente activo</label></div>@endif
                </div>
                @if($cliente->exists)
                <div class="card-body bg-light">
                    <div class="row text-center">
                        <div class="col"><div class="text-muted small">Pedidos</div><h4>{{ $cliente->total_pedidos }}</h4></div>
                        <div class="col"><div class="text-muted small">Gastado</div><h4 class="text-success">{{ $config->formatearPrecio($cliente->total_gastado) }}</h4></div>
                        <div class="col"><div class="text-muted small">Puntos</div><h4 class="text-warning">{{ $cliente->puntos_fidelidad }}</h4></div>
                    </div>
                </div>
                @endif
                <div class="card-footer text-right">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i></a>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
