@extends('layouts.app')
@section('titulo', 'Configuración')
@section('subtitulo', 'Datos de empresa, moneda, impuestos y opciones del sistema')

@section('contenido')
<form method="POST" action="{{ route('configuracion.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="confTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-empresa" data-target="#tab-empresa" type="button"><i class="fas fa-building"></i> Empresa</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-moneda" data-target="#tab-moneda" type="button"><i class="fas fa-coins"></i> Moneda e impuestos</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-tpv" data-target="#tab-tpv" type="button"><i class="fas fa-cash-register"></i> TPV / Tickets</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-modulos" data-target="#tab-modulos" type="button"><i class="fas fa-puzzle-piece"></i> Módulos</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-apariencia" data-target="#tab-apariencia" type="button"><i class="fas fa-palette"></i> Apariencia</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-fideliza" data-target="#tab-fideliza" type="button"><i class="fas fa-star"></i> Fidelización</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-sunat" data-target="#tab-sunat" type="button" style="color:#c8102e"><i class="fas fa-file-invoice-dollar"></i> SUNAT (Perú)</button></li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- EMPRESA --}}
                <div class="tab-pane show active" id="tab-empresa">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <label class="form-label fw-bold d-block mb-2">Logo de la empresa</label>
                            <div class="border rounded p-3 mb-2" style="background:#f8f9fa">
                                @if($config->logo)
                                    <img src="{{ asset('storage/'.$config->logo) }}" style="max-height:120px;max-width:100%" alt="Logo">
                                @else
                                    <i class="fas fa-image fa-4x text-muted"></i>
                                    <p class="small text-muted mt-2 mb-0">Sin logo</p>
                                @endif
                            </div>
                            <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                            <small class="text-muted">JPG/PNG, máx. 2MB</small>

                            <hr>

                            <label class="form-label fw-bold d-block mb-2 mt-3">Favicon</label>
                            <div class="border rounded p-2 mb-2" style="background:#f8f9fa">
                                @if($config->favicon)
                                    <img src="{{ asset('storage/'.$config->favicon) }}" style="max-height:48px" alt="Favicon">
                                @else
                                    <i class="fas fa-star text-muted"></i>
                                @endif
                            </div>
                            <input type="file" name="favicon" class="form-control form-control-sm" accept="image/*">
                        </div>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Nombre comercial *</label>
                                    <input type="text" name="nombre_empresa" class="form-control" value="{{ old('nombre_empresa', $config->nombre_empresa) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">CIF / NIF</label>
                                    <input type="text" name="cif_nif" class="form-control" value="{{ old('cif_nif', $config->cif_nif) }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Razón social</label>
                                    <input type="text" name="razon_social" class="form-control" value="{{ old('razon_social', $config->razon_social) }}">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Dirección</label>
                                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $config->direccion) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Código postal</label>
                                    <input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal', $config->codigo_postal) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Ciudad</label>
                                    <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', $config->ciudad) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Provincia</label>
                                    <input type="text" name="provincia" class="form-control" value="{{ old('provincia', $config->provincia) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">País</label>
                                    <input type="text" name="pais" class="form-control" value="{{ old('pais', $config->pais) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold"><i class="fas fa-phone"></i> Teléfono</label>
                                    <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $config->telefono) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold"><i class="fas fa-mobile"></i> Móvil</label>
                                    <input type="text" name="movil" class="form-control" value="{{ old('movil', $config->movil) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold"><i class="far fa-envelope"></i> Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $config->email) }}">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold"><i class="fas fa-globe"></i> Sitio web</label>
                                    <input type="url" name="web" class="form-control" value="{{ old('web', $config->web) }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MONEDA E IMPUESTOS --}}
                <div class="tab-pane" id="tab-moneda">
                    <h5><i class="fas fa-coins text-warning"></i> Moneda</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Código ISO</label>
                            <select name="moneda_codigo" class="form-control" id="moneda_codigo">
                                @foreach(['EUR'=>'€ Euro','USD'=>'$ Dólar US','GBP'=>'£ Libra','MXN'=>'$ Peso MX','ARS'=>'$ Peso AR','COP'=>'$ Peso CO','CLP'=>'$ Peso CL','PEN'=>'S/ Sol','BRL'=>'R$ Real','VES'=>'Bs Bolívar','BOB'=>'Bs Boliviano','UYU'=>'$ Peso UY'] as $cod=>$nom)
                                    <option value="{{ $cod }}" @selected($config->moneda_codigo==$cod)>{{ $cod }} - {{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Símbolo</label>
                            <input type="text" name="moneda_simbolo" class="form-control" value="{{ old('moneda_simbolo', $config->moneda_simbolo) }}" maxlength="5">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Posición símbolo</label>
                            <select name="moneda_posicion" class="form-control">
                                <option value="izquierda" @selected($config->moneda_posicion=='izquierda')>Izquierda (€ 100)</option>
                                <option value="derecha" @selected($config->moneda_posicion=='derecha')>Derecha (100 €)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Decimales</label>
                            <input type="number" name="decimales" min="0" max="4" class="form-control" value="{{ old('decimales', $config->decimales) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Separador decimal</label>
                            <select name="separador_decimal" class="form-control">
                                <option value="," @selected($config->separador_decimal==',')>Coma (,)</option>
                                <option value="." @selected($config->separador_decimal=='.')>Punto (.)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Separador miles</label>
                            <select name="separador_miles" class="form-control">
                                <option value="." @selected($config->separador_miles=='.')>Punto (.)</option>
                                <option value="," @selected($config->separador_miles==',')>Coma (,)</option>
                                <option value=" " @selected($config->separador_miles==' ')>Espacio</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Vista previa</label>
                            <div class="form-control bg-light fw-bold text-success" id="preview-moneda" style="font-size:1.2rem">
                                {{ $config->formatearPrecio(1234.56) }}
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-percent text-info"></i> Impuestos (IVA)</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">IVA general (%)</label>
                            <div class="input-group">
                                <input type="number" name="iva_general" step="0.01" class="form-control" value="{{ old('iva_general', $config->iva_general) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Aplicado a productos como bebidas, refrescos, etc.</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">IVA reducido (%)</label>
                            <div class="input-group">
                                <input type="number" name="iva_reducido" step="0.01" class="form-control" value="{{ old('iva_reducido', $config->iva_reducido) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Aplicado a alimentación de primera necesidad</small>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="precios_con_iva" id="precios_con_iva" value="1" @checked($config->precios_con_iva)>
                                <label for="precios_con_iva" class="form-check-label fw-bold">Precios con IVA incluido</label>
                                <small class="d-block text-muted">Si está activo, los precios mostrados ya incluyen impuestos</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TPV / TICKETS --}}
                <div class="tab-pane" id="tab-tpv">
                    <h5><i class="fas fa-hashtag text-primary"></i> Numeración</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Serie tickets</label>
                            <input type="text" name="serie_ticket" class="form-control" value="{{ old('serie_ticket', $config->serie_ticket) }}" maxlength="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Serie facturas</label>
                            <input type="text" name="serie_factura" class="form-control" value="{{ old('serie_factura', $config->serie_factura) }}" maxlength="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Serie pedidos</label>
                            <input type="text" name="serie_pedido" class="form-control" value="{{ old('serie_pedido', $config->serie_pedido) }}" maxlength="10">
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-print text-secondary"></i> Tickets</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Ancho papel</label>
                            <select name="ancho_ticket_mm" class="form-control">
                                <option value="58" @selected($config->ancho_ticket_mm==58)>58 mm</option>
                                <option value="80" @selected($config->ancho_ticket_mm==80)>80 mm</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="imprimir_logo_ticket" id="imp_logo" value="1" @checked($config->imprimir_logo_ticket)>
                                <label for="imp_logo" class="form-check-label">Imprimir logo en ticket</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Texto cabecera del ticket</label>
                            <textarea name="texto_ticket_cabecera" class="form-control" rows="2">{{ old('texto_ticket_cabecera', $config->texto_ticket_cabecera) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Texto pie del ticket</label>
                            <textarea name="texto_ticket_pie" class="form-control" rows="2">{{ old('texto_ticket_pie', $config->texto_ticket_pie) }}</textarea>
                            <small class="text-muted">Ej: "Gracias por su visita - Síguenos en redes sociales"</small>
                        </div>
                    </div>
                </div>

                {{-- MÓDULOS --}}
                <div class="tab-pane" id="tab-modulos">
                    <h5><i class="fas fa-puzzle-piece text-success"></i> Módulos de venta activos</h5>
                    <hr>
                    <div class="row">
                        @foreach([
                            'modulo_mesas'=>['Servicio en mesa','utensils','Permite gestionar mesas y comensales','info'],
                            'modulo_domicilio'=>['Pedidos a domicilio','motorcycle','Reparto a domicilio del cliente','warning'],
                            'modulo_recogida'=>['Recogida en local','shopping-bag','Cliente recoge su pedido','primary'],
                            'modulo_telefono'=>['Pedido telefónico','phone','Toma de pedidos por teléfono','danger'],
                        ] as $key => $info)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 d-flex align-items-center" style="background:#f8f9fa">
                                    <i class="fas fa-{{ $info[1] }} fa-2x text-{{ $info[3] }} mr-3 me-3"></i>
                                    <div class="flex-grow-1">
                                        <strong>{{ $info[0] }}</strong>
                                        <div class="small text-muted">{{ $info[2] }}</div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="{{ $key }}" id="{{ $key }}" value="1" @checked($config->$key)>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <h5 class="mt-4"><i class="fas fa-truck text-warning"></i> Configuración de envío</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Coste de envío por defecto</label>
                            <input type="number" name="coste_envio" step="0.01" class="form-control" value="{{ old('coste_envio', $config->coste_envio) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Pedido mínimo para envío</label>
                            <input type="number" name="pedido_minimo_envio" step="0.01" class="form-control" value="{{ old('pedido_minimo_envio', $config->pedido_minimo_envio) }}">
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="far fa-clock text-info"></i> Horario de atención</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Hora apertura</label>
                            <input type="time" name="hora_apertura" class="form-control" value="{{ $config->hora_apertura?->format('H:i') ?? '09:00' }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Hora cierre</label>
                            <input type="time" name="hora_cierre" class="form-control" value="{{ $config->hora_cierre?->format('H:i') ?? '23:30' }}">
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-bell text-danger"></i> Notificaciones y stock</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="alerta_stock_bajo" id="alerta_stock" value="1" @checked($config->alerta_stock_bajo)>
                                <label for="alerta_stock" class="form-check-label fw-bold">Alertar stock bajo</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Umbral stock bajo</label>
                            <input type="number" name="umbral_stock_bajo" class="form-control" value="{{ old('umbral_stock_bajo', $config->umbral_stock_bajo) }}">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="email_pedidos" id="email_ped" value="1" @checked($config->email_pedidos)>
                                <label for="email_ped" class="form-check-label fw-bold">Email al recibir pedidos</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Email de notificaciones</label>
                            <input type="email" name="email_notificaciones" class="form-control" value="{{ old('email_notificaciones', $config->email_notificaciones) }}">
                        </div>
                    </div>
                </div>

                {{-- APARIENCIA --}}
                <div class="tab-pane" id="tab-apariencia">
                    <h5><i class="fas fa-palette text-info"></i> Personalización visual</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Color primario</label>
                            <div class="input-group">
                                <input type="color" name="color_primario" class="form-control form-control-color" value="{{ $config->color_primario }}">
                                <input type="text" class="form-control" value="{{ $config->color_primario }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Color secundario</label>
                            <div class="input-group">
                                <input type="color" name="color_secundario" class="form-control form-control-color" value="{{ $config->color_secundario }}">
                                <input type="text" class="form-control" value="{{ $config->color_secundario }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tema</label>
                            <select name="tema" class="form-control">
                                <option value="claro" @selected($config->tema=='claro')>Claro</option>
                                <option value="oscuro" @selected($config->tema=='oscuro')>Oscuro</option>
                                <option value="auto" @selected($config->tema=='auto')>Automático (sistema)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SUNAT / FACTURACIÓN ELECTRÓNICA PERÚ --}}
                <div class="tab-pane" id="tab-sunat">
                    <div class="alert alert-info border-0 d-flex gap-3 align-items-start" style="background:linear-gradient(135deg,#fff5f5,#ffebed)">
                        <i class="fas fa-file-invoice-dollar fa-2x" style="color:#c8102e"></i>
                        <div>
                            <h6 class="mb-1" style="color:#c8102e">Facturación electrónica directa a SUNAT</h6>
                            <small>Activa este módulo para emitir Facturas (01) y Boletas (03) electrónicas con envío directo al servicio billService de SUNAT, sin pasar por OSE intermediario.</small>
                        </div>
                    </div>

                    <h5 class="mt-3"><i class="fas fa-power-off"></i> Activación</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="facturacion_electronica_pe" id="fe_pe" value="1" @checked($config->facturacion_electronica_pe ?? false)>
                                <label for="fe_pe" class="form-check-label fw-bold">Activar facturación electrónica Perú</label>
                            </div>
                            <small class="text-muted d-block">Al activar, en el TPV podrás emitir Factura/Boleta tras el cobro.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Modo de envío</label>
                            <select name="sunat_modo" class="form-control">
                                <option value="beta" @selected(($config->sunat_modo ?? 'beta') === 'beta')>🧪 Beta / Pruebas (homologación)</option>
                                <option value="produccion" @selected(($config->sunat_modo ?? '') === 'produccion')>🚀 Producción</option>
                            </select>
                            <small class="text-muted">Empieza siempre por Beta para validar.</small>
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-building"></i> Datos del emisor</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">RUC (11 dígitos)</label>
                            <input type="text" name="ruc" class="form-control" value="{{ old('ruc', $config->ruc ?? '') }}" maxlength="11" pattern="[12]\d{10}" placeholder="20123456789">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Ubigeo (6 dígitos INEI)</label>
                            <input type="text" name="ubigeo" class="form-control" value="{{ old('ubigeo', $config->ubigeo ?? '') }}" maxlength="6" placeholder="150101 (Lima · Lima · Lima)">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Departamento</label>
                            <input type="text" name="departamento" class="form-control" value="{{ old('departamento', $config->departamento ?? '') }}" placeholder="LIMA">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Distrito</label>
                            <input type="text" name="distrito" class="form-control" value="{{ old('distrito', $config->distrito ?? '') }}" placeholder="MIRAFLORES">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Urbanización</label>
                            <input type="text" name="urbanizacion" class="form-control" value="{{ old('urbanizacion', $config->urbanizacion ?? '') }}">
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-key"></i> Credenciales SOL</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Usuario SOL</label>
                            <input type="text" name="usuario_sol" class="form-control" value="{{ old('usuario_sol', $config->usuario_sol ?? '') }}" placeholder="MODDATOS (beta) o tu usuario real">
                            <small class="text-muted">El sistema envía <code>RUC + UsuarioSOL</code> como login SOAP.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Clave SOL</label>
                            <input type="password" name="clave_sol" class="form-control" value="{{ old('clave_sol', $config->clave_sol ?? '') }}" placeholder="moddatos (beta) o tu clave real">
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-certificate"></i> Certificado digital</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Archivo de certificado (.pem o .pfx)</label>
                            <input type="file" name="certificado_archivo" class="form-control" accept=".pem,.pfx,.p12">
                            @if(!empty($config->certificado_path))
                                <small class="text-success d-block mt-1"><i class="fas fa-check-circle"></i> Certificado actual: <code>{{ basename($config->certificado_path) }}</code></small>
                            @else
                                <small class="text-muted d-block mt-1">Sube tu certificado X.509 emitido por entidad acreditada por SUNAT (en pruebas puedes usar el certificado gratuito de prueba).</small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contraseña del certificado</label>
                            <input type="password" name="certificado_password" class="form-control" value="{{ old('certificado_password', $config->certificado_password ?? '') }}">
                        </div>
                    </div>

                    <h5 class="mt-4"><i class="fas fa-hashtag"></i> Numeración y tributos</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Serie Factura</label>
                            <input type="text" name="serie_factura_pe" class="form-control" value="{{ old('serie_factura_pe', $config->serie_factura_pe ?? 'F001') }}" maxlength="4">
                            <small class="text-muted">F + 3 dígitos</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Serie Boleta</label>
                            <input type="text" name="serie_boleta_pe" class="form-control" value="{{ old('serie_boleta_pe', $config->serie_boleta_pe ?? 'B001') }}" maxlength="4">
                            <small class="text-muted">B + 3 dígitos</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Próx. correlativo Factura</label>
                            <input type="number" name="proximo_factura_pe" class="form-control" value="{{ old('proximo_factura_pe', $config->proximo_factura_pe ?? 1) }}" min="1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Próx. correlativo Boleta</label>
                            <input type="number" name="proximo_boleta_pe" class="form-control" value="{{ old('proximo_boleta_pe', $config->proximo_boleta_pe ?? 1) }}" min="1">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">IGV (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="igv_porcentaje" class="form-control" value="{{ old('igv_porcentaje', $config->igv_porcentaje ?? 18.00) }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Vigente: 18%</small>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3 small">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Modo Beta SUNAT:</strong> usa Usuario SOL <code>MODDATOS</code> y clave <code>moddatos</code> con tu RUC de prueba.
                        Endpoint: <code>https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService</code>
                    </div>
                </div>

                {{-- FIDELIZACIÓN --}}
                <div class="tab-pane" id="tab-fideliza">
                    <h5><i class="fas fa-star text-warning"></i> Programa de fidelización</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="programa_puntos" id="prog_puntos" value="1" @checked($config->programa_puntos)>
                                <label for="prog_puntos" class="form-check-label fw-bold">Activar programa de puntos</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Puntos por euro gastado</label>
                            <input type="number" name="puntos_por_euro" step="0.01" class="form-control" value="{{ old('puntos_por_euro', $config->puntos_por_euro) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Valor de cada punto (€)</label>
                            <input type="number" name="valor_punto" step="0.0001" class="form-control" value="{{ old('valor_punto', $config->valor_punto) }}">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer text-right">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar cambios</button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
// Tabs Bootstrap-4 fallback (AdminLTE 3 trae BS4)
document.querySelectorAll('[data-toggle="tab"]').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        const target = btn.getAttribute('data-target') || btn.getAttribute('data-bs-target');
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show','active'));
        document.querySelectorAll('[data-toggle="tab"]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelector(target).classList.add('show','active');
    });
});

// Sincronizar color picker con texto
document.querySelectorAll('input[type=color]').forEach(c=>{
    c.addEventListener('input',e=>{
        const txt = c.parentElement.querySelector('input[type=text]');
        if(txt) txt.value = e.target.value;
    });
});
</script>
@endsection
