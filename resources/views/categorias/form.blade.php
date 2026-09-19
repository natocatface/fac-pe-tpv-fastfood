@extends('layouts.app')
@section('titulo', $categoria->exists ? 'Editar categoría' : 'Nueva categoría')

@section('contenido')
<form method="POST" action="{{ $categoria->exists ? route('categorias.update', $categoria) : route('categorias.store') }}" enctype="multipart/form-data">
    @csrf @if($categoria->exists) @method('PUT') @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-info-circle"></i> Datos de la categoría</div>
                <div class="card-body">
                    @if($errors->any())<div class="alert alert-danger">{!! implode('<br>', $errors->all()) !!}</div>@endif
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $categoria->nombre) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Icono FontAwesome</label>
                            <input type="text" name="icono" class="form-control" value="{{ old('icono', $categoria->icono) }}" placeholder="pizza-slice, hamburger, etc.">
                            <small class="text-muted">Sin "fa-" delante</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Color</label>
                            <input type="color" name="color" class="form-control form-control-color" value="{{ old('color', $categoria->color ?: '#28a745') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Orden</label>
                            <input type="number" name="orden" class="form-control" value="{{ old('orden', $categoria->orden ?? 0) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Imagen de categoría</label>
                        @if($categoria->imagen)<div class="mb-2"><img src="{{ asset('storage/'.$categoria->imagen) }}" style="max-height:100px"></div>@endif
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-toggle-on"></i> Visibilidad</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-2"><input type="checkbox" class="form-check-input" name="activa" id="activa" value="1" {{ old('activa', $categoria->activa ?? true) ? 'checked' : '' }}><label for="activa" class="form-check-label">Activa</label></div>
                    <div class="form-check form-switch"><input type="checkbox" class="form-check-input" name="mostrar_tpv" id="mostrar_tpv" value="1" {{ old('mostrar_tpv', $categoria->mostrar_tpv ?? true) ? 'checked' : '' }}><label for="mostrar_tpv" class="form-check-label">Mostrar en TPV</label></div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('categorias.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i></a>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
