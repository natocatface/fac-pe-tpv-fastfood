@extends('layouts.app')
@section('titulo', $producto->exists ? 'Editar producto' : 'Nuevo producto')

@section('contenido')
<form method="POST" action="{{ $producto->exists ? route('productos.update', $producto) : route('productos.store') }}" enctype="multipart/form-data">
    @csrf @if($producto->exists) @method('PUT') @endif

    @if($errors->any())<div class="alert alert-danger">{!! implode('<br>', $errors->all()) !!}</div>@endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-info-circle"></i> Información básica</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $producto->nombre) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Código</label>
                            <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $producto->codigo) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categoría *</label>
                            <select name="categoria_id" class="form-control" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($categorias as $c)
                                    <option value="{{ $c->id }}" @selected(old('categoria_id', $producto->categoria_id)==$c->id)>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tipo</label>
                            <select name="tipo" class="form-control">
                                @foreach(['simple'=>'Simple','menu'=>'Menú','combo'=>'Combo','compuesto'=>'Compuesto'] as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('tipo', $producto->tipo ?? 'simple')==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Ingredientes</label>
                            <textarea name="ingredientes" class="form-control" rows="2" placeholder="Tomate, mozzarella, jamón...">{{ old('ingredientes', $producto->ingredientes) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Alérgenos</label>
                            <input type="text" name="alergenos" class="form-control" value="{{ old('alergenos', $producto->alergenos) }}" placeholder="Gluten, lactosa, frutos secos...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="fas fa-euro-sign"></i> Precios e impuestos</div>
                <div class="card-body row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Precio venta *</label>
                        <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio', $producto->precio) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Precio costo</label>
                        <input type="number" step="0.01" name="precio_costo" class="form-control" value="{{ old('precio_costo', $producto->precio_costo ?? 0) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Precio oferta</label>
                        <input type="number" step="0.01" name="precio_oferta" class="form-control" value="{{ old('precio_oferta', $producto->precio_oferta) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">IVA (%)</label>
                        <input type="number" step="0.01" name="iva" class="form-control" value="{{ old('iva', $producto->iva ?? $config->iva_general) }}">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="fas fa-boxes"></i> Stock y operativa</div>
                <div class="card-body row">
                    <div class="col-md-3 mb-3">
                        <div class="form-check form-switch mt-4"><input type="checkbox" class="form-check-input" name="controla_stock" id="cs" value="1" {{ old('controla_stock', $producto->controla_stock ?? false) ? 'checked' : '' }}><label for="cs" class="form-check-label">Controlar stock</label></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Stock actual</label>
                        <input type="number" step="0.01" name="stock" class="form-control" value="{{ old('stock', $producto->stock ?? 0) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Stock mínimo</label>
                        <input type="number" step="0.01" name="stock_minimo" class="form-control" value="{{ old('stock_minimo', $producto->stock_minimo ?? 0) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Tiempo prep (min)</label>
                        <input type="number" name="tiempo_preparacion" class="form-control" value="{{ old('tiempo_preparacion', $producto->tiempo_preparacion ?? 0) }}">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="fas fa-tags"></i> Etiquetas dietéticas</div>
                <div class="card-body row">
                    @foreach(['es_vegetariano'=>['Vegetariano','leaf','success'],'es_vegano'=>['Vegano','seedling','success'],'es_sin_gluten'=>['Sin gluten','bread-slice','warning'],'picante'=>['Picante','pepper-hot','danger']] as $k=>$v)
                    <div class="col-md-3 mb-2">
                        <div class="form-check form-switch"><input type="checkbox" class="form-check-input" name="{{ $k }}" id="{{ $k }}" value="1" {{ old($k, $producto->$k ?? false) ? 'checked' : '' }}><label for="{{ $k }}" class="form-check-label"><i class="fas fa-{{ $v[1] }} text-{{ $v[2] }}"></i> {{ $v[0] }}</label></div>
                    </div>
                    @endforeach
                    <div class="col-md-6 mb-2">
                        <label class="form-label small fw-bold">Calorías (kcal)</label>
                        <input type="number" name="calorias" class="form-control form-control-sm" value="{{ old('calorias', $producto->calorias) }}">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label small fw-bold">Nivel de picante (0-5)</label>
                        <input type="number" min="0" max="5" name="nivel_picante" class="form-control form-control-sm" value="{{ old('nivel_picante', $producto->nivel_picante ?? 0) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-image"></i> Imagen</div>
                <div class="card-body text-center">
                    @if($producto->imagen)<img src="{{ $producto->imagenUrl() }}" class="img-fluid mb-2" style="max-height:160px;border-radius:8px">@endif
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header"><i class="fas fa-toggle-on"></i> Visibilidad</div>
                <div class="card-body">
                    @foreach(['activo'=>['Activo',true],'destacado'=>['Destacado',false],'disponible_local'=>['Disponible en local',true],'disponible_domicilio'=>['Disponible domicilio',true],'disponible_recogida'=>['Disponible recogida',true]] as $k=>$v)
                    <div class="form-check form-switch mb-2"><input type="checkbox" class="form-check-input" name="{{ $k }}" id="{{ $k }}" value="1" {{ old($k, $producto->$k ?? $v[1]) ? 'checked' : '' }}><label for="{{ $k }}" class="form-check-label">{{ $v[0] }}</label></div>
                    @endforeach
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('productos.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i></a>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
