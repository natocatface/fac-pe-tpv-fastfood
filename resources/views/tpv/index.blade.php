@extends('layouts.app')
@section('titulo', 'TPV - Punto de Venta')
@section('subtitulo', 'Toma de pedidos rápida')

@section('estilos')
:root{
    --tpv-radius: 14px;
    --tpv-shadow: 0 2px 12px rgba(0,0,0,.06);
    --tpv-shadow-hover: 0 12px 28px rgba(0,0,0,.14);
}

.content-wrapper{ background:#eef1f6 !important; }

/* ========= LAYOUT GENERAL ========= */
.tpv-app{
    display:grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 1rem;
    height: calc(100vh - 170px);
    min-height: 620px;
    width: 100%;
    max-width: 100%;
    overflow: hidden;          /* evita scroll horizontal en el contenedor */
}
/* Los hijos deben respetar el ancho de su columna del grid;
   sin esto, el grid de productos puede forzar el ensanchamiento
   y sacar el panel de ticket fuera de la vista. */
.tpv-app > *{ min-width:0; min-height:0; }

@media (max-width: 1399px){
    .tpv-app{ grid-template-columns: minmax(0, 1fr) 340px; }
}
@media (max-width: 1199px){
    .tpv-app{ grid-template-columns: minmax(0, 1fr) 320px; }
}
@media (max-width: 991px){
    .tpv-app{ grid-template-columns: 1fr; height:auto; overflow:visible; }
}

/* ========= COLUMNA IZQUIERDA ========= */
.tpv-left{
    display:flex; flex-direction:column; gap:.75rem;
    min-width:0;   /* clave: permite que el grid hijo se ajuste */
    min-height:0;
}

.tpv-search{
    background:#fff; border-radius:var(--tpv-radius);
    padding:.6rem .85rem; display:flex; gap:.5rem; align-items:center;
    box-shadow:var(--tpv-shadow);
}
.tpv-search input{
    border:0; outline:0; flex:1; font-size:.95rem; padding:.4rem .25rem;
    background:transparent;
}
.tpv-search .clear-btn{
    background:#f1f3f5; border:0; width:30px; height:30px; border-radius:8px;
    display:flex; align-items:center; justify-content:center; color:#6c757d;
}
.tpv-search .clear-btn:hover{background:#e9ecef}

.tpv-cats{
    display:flex; gap:.5rem; overflow-x:auto; padding:.25rem 0 .35rem;
    scrollbar-width: thin;
}
.tpv-cats::-webkit-scrollbar{height:6px}
.tpv-cats::-webkit-scrollbar-thumb{background:#cbd2da;border-radius:3px}
.cat-btn{
    flex:0 0 auto; border:0; border-radius:12px;
    padding:.7rem 1.2rem; font-weight:600; color:#fff; font-size:.85rem;
    box-shadow:0 3px 8px rgba(0,0,0,.12);
    transition: transform .15s, box-shadow .15s, filter .15s;
    display:flex; align-items:center; gap:.4rem;
    white-space:nowrap; cursor:pointer;
}
.cat-btn:hover{ transform:translateY(-2px); box-shadow:0 6px 14px rgba(0,0,0,.18); }
.cat-btn.activo{ outline:3px solid rgba(255,255,255,.7); outline-offset:-1px; transform:translateY(-2px); }
.cat-btn .cnt{
    background:rgba(255,255,255,.25); padding:.05rem .45rem; border-radius:6px;
    font-size:.7rem;
}

/* ========= GRID PRODUCTOS ========= */
.tpv-products{
    flex:1;
    min-width:0;                /* respeta el ancho de la columna padre */
    overflow-y:auto;
    overflow-x:hidden;          /* nunca scroll horizontal en productos */
    padding:.25rem .35rem .5rem 0;
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    grid-auto-rows: max-content;/* cada fila usa exactamente la altura del contenido */
    gap:.75rem;
    align-content:flex-start;
    align-items:start;          /* las tarjetas no se estiran a la altura de la fila */
}
@media (max-width: 1399px){
    .tpv-products{ grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); }
}
@media (max-width: 1199px){
    .tpv-products{ grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)); gap:.55rem; }
}
.tpv-products::-webkit-scrollbar{width:8px}
.tpv-products::-webkit-scrollbar-thumb{background:#cbd2da;border-radius:4px}

.prod-card{
    background:#fff; border-radius:var(--tpv-radius);
    padding:.6rem .55rem .7rem;
    text-align:center; cursor:pointer; position:relative;
    transition: transform .15s, box-shadow .15s, border-color .15s;
    border:2px solid transparent;
    box-shadow:var(--tpv-shadow);
    overflow:hidden;
    user-select:none;
    /* Layout vertical interno: imagen arriba, nombre en medio, precio abajo */
    display:flex; flex-direction:column;
    min-height: 195px;          /* altura mínima para que siempre quepa todo */
}
.prod-card:hover{ transform:translateY(-3px); box-shadow:var(--tpv-shadow-hover); border-color:var(--color-primario); }
.prod-card:active{ transform:translateY(0) scale(.98); }
.prod-card .img-wrap{
    border-radius:10px; overflow:hidden;
    height: 100px;              /* altura fija, no aspect-ratio (más estable) */
    margin-bottom:.5rem;
    background: linear-gradient(135deg,#f8f9fa,#e9ecef);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.prod-card .img-wrap img{ width:100%; height:100%; object-fit:cover; }
.prod-card .img-wrap i{ font-size:2.4rem; }
.prod-card .nombre{
    font-weight:600; font-size:.82rem; line-height:1.25;
    min-height:2.1em;
    max-height:2.5em;
    color:#1a2332;
    overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
    flex:1 1 auto;
}
.prod-card .precio{
    color:var(--color-primario); font-weight:700; font-size:1.02rem;
    margin-top:.35rem;
    flex-shrink:0;
}
@media (max-width: 1399px){
    .prod-card{ min-height: 185px; }
    .prod-card .img-wrap{ height: 92px; }
    .prod-card .img-wrap i{ font-size:2.1rem; }
}
@media (max-width: 1199px){
    .prod-card{ min-height: 175px; padding:.5rem .45rem .6rem; }
    .prod-card .img-wrap{ height: 82px; }
    .prod-card .img-wrap i{ font-size:1.9rem; }
    .prod-card .nombre{ font-size:.78rem; }
    .prod-card .precio{ font-size:.95rem; }
}
.prod-card .badge-oferta{
    position:absolute; top:8px; left:8px;
    background:#dc3545; color:#fff; font-size:.65rem; font-weight:700;
    padding:.15rem .45rem; border-radius:5px; letter-spacing:.5px;
    box-shadow:0 2px 4px rgba(220,53,69,.4);
}
.prod-card .tags{ position:absolute; top:6px; right:6px; display:flex; flex-direction:column; gap:2px; }
.prod-card .tags span{
    font-size:.55rem; padding:.15rem .35rem; border-radius:4px; color:#fff; font-weight:600;
}
.tag-veg{background:#28a745}
.tag-vegan{background:#1e7e34}
.tag-sg{background:#ffc107;color:#212529 !important}
.tag-pic{background:#dc3545}

/* ========= COLUMNA DERECHA (TICKET) ========= */
.tpv-ticket{
    background:#fff; border-radius:var(--tpv-radius);
    display:flex; flex-direction:column;
    box-shadow: 0 4px 18px rgba(0,0,0,.08);
    overflow:hidden;
    min-width:0;    /* se ajusta a la columna del grid */
    min-height:0;
    width:100%;
}
.ticket-head{
    padding:1rem 1.2rem;
    background:linear-gradient(135deg,var(--color-primario), color-mix(in srgb, var(--color-primario) 70%, black));
    color:#fff;
}
.ticket-head h5{margin:0; font-weight:700}
.ticket-head .small-info{font-size:.78rem; opacity:.85}
.btn-clear-ticket{
    background:rgba(255,255,255,.18); color:#fff; border:0;
    width:34px; height:34px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    transition:background .15s;
}
.btn-clear-ticket:hover{background:rgba(255,255,255,.32)}

/* Selector tipo de pedido */
.tipos-row{
    display:grid; grid-template-columns:repeat(auto-fit, minmax(70px,1fr)); gap:.4rem;
    margin-top:.6rem;
}
.tipo-btn{
    padding:.5rem .3rem; border:1px solid rgba(255,255,255,.4);
    background:rgba(255,255,255,.12); color:#fff; border-radius:8px;
    font-size:.7rem; font-weight:600; cursor:pointer;
    display:flex; flex-direction:column; align-items:center; gap:.2rem;
    transition: all .15s;
}
.tipo-btn:hover{background:rgba(255,255,255,.22)}
.tipo-btn.activo{background:#fff; color:var(--color-primario); border-color:#fff;}
.tipo-btn i{font-size:1rem}

/* Cliente / mesa zone */
.zona-cliente, .zona-mesa{
    padding:.6rem .9rem; background:#f8f9fa; border-bottom:1px solid #eef0f3;
    font-size:.85rem;
}
.cliente-buscar{position:relative}
.cliente-input{
    border:1px solid #ddd; border-radius:8px; padding:.45rem .6rem .45rem 2rem;
    width:100%; font-size:.85rem;
}
.cliente-input:focus{outline:0;border-color:var(--color-primario);box-shadow:0 0 0 3px rgba(40,167,69,.12)}
.cliente-buscar .ic{position:absolute; left:.65rem; top:50%; transform:translateY(-50%); color:#adb5bd}
.cliente-resultados{
    position:absolute; left:0; right:0; top:calc(100% + 4px);
    background:#fff; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.15);
    max-height:230px; overflow-y:auto; z-index:50;
}
.cliente-resultados .item{padding:.55rem .75rem; cursor:pointer; border-bottom:1px solid #f1f3f5; font-size:.83rem}
.cliente-resultados .item:hover{background:#f8f9fa}
.cliente-sel{
    background:#e6f4ea; border:1px solid #28a745; border-radius:8px;
    padding:.5rem .65rem; margin-top:.5rem; font-size:.82rem; color:#155724;
    display:flex; gap:.5rem; align-items:start;
}
.cliente-sel .quitar{margin-left:auto; cursor:pointer; background:transparent; border:0; color:#155724}

/* Items del ticket */
.ticket-body{
    flex:1; overflow-y:auto; padding:.4rem 0;
    background:#fafbfc;
}
.ticket-body::-webkit-scrollbar{width:8px}
.ticket-body::-webkit-scrollbar-thumb{background:#cbd2da;border-radius:4px}
.ticket-empty{
    text-align:center; padding:3rem 1rem; color:#9ca3af;
}
.ticket-empty i{font-size:3rem; opacity:.4; margin-bottom:.6rem; display:block}

.ticket-item{
    display:grid;
    grid-template-columns: auto 1fr auto auto;
    gap:.6rem; align-items:center;
    padding:.6rem .85rem;
    border-bottom:1px dashed #e9ecef;
    background:#fff;
    margin:0 .6rem .35rem;
    border-radius:10px;
    box-shadow:0 1px 3px rgba(0,0,0,.04);
    animation: slideIn .25s ease;
}
@keyframes slideIn{ from{opacity:0; transform:translateX(20px)} to{opacity:1; transform:none} }
.ticket-item .qty-controls{display:flex; align-items:center; gap:.2rem}
.qty-controls button{
    border:0; width:28px; height:28px; border-radius:6px;
    background:#f1f3f5; color:#495057; font-weight:700;
    transition:all .12s;
}
.qty-controls button:hover{background:var(--color-primario); color:#fff}
.qty-controls .qty-num{
    min-width:26px; text-align:center; font-weight:700; font-size:.95rem;
    color:#1a2332;
}
.ticket-item .info .nombre{font-weight:600; font-size:.85rem; line-height:1.2; color:#1a2332}
.ticket-item .info .precio-unit{font-size:.7rem; color:#6c757d}
.ticket-item .total{font-weight:700; font-size:.95rem; color:#1a2332; min-width:70px; text-align:right}
.ticket-item .delete{
    background:transparent; border:0; color:#dc3545; padding:.2rem .35rem;
    border-radius:6px; transition:background .12s;
}
.ticket-item .delete:hover{background:#fde2e4}

/* Footer del ticket */
.ticket-foot{
    padding:.85rem 1rem 1rem;
    border-top:2px solid #f1f3f5;
    background:#fff;
}
.ticket-line{display:flex; justify-content:space-between; font-size:.85rem; color:#6c757d; margin-bottom:.2rem}
.ticket-line input{
    border:1px solid #ced4da; border-radius:5px; padding:0 .35rem; width:75px;
    text-align:right; font-size:.8rem; height:24px;
}
.ticket-total-big{
    font-size:2rem; font-weight:800; color:var(--color-primario);
    text-align:right; margin:.4rem 0 .6rem;
    letter-spacing:-1px;
}
.btns-tpv{display:grid; grid-template-columns:1fr 1.4fr; gap:.5rem}
.btn-tpv-grande{ padding:.85rem; font-weight:700; font-size:.95rem; border:0; border-radius:10px; transition:all .15s }
.btn-tpv-grande:hover{transform:translateY(-1px); box-shadow:0 6px 14px rgba(0,0,0,.15)}
.btn-tpv-warn{background:#ffc107; color:#212529}
.btn-tpv-success{background:linear-gradient(135deg,#28a745,#1e7e34); color:#fff}

/* ========= MODAL COBRO ========= */
.modal-cobro .modal-content{border:0; border-radius:18px; overflow:hidden}
.modal-cobro .modal-header{
    background:linear-gradient(135deg,#28a745,#1e7e34); color:#fff; border:0; padding:1rem 1.4rem;
}
.cobro-total{
    text-align:center; padding:1.5rem 1rem; background:#f8f9fa;
    margin:-1rem -1rem 1rem; border-bottom:1px solid #eef0f3;
}
.cobro-total .lbl{font-size:.85rem; color:#6c757d; text-transform:uppercase; letter-spacing:1px}
.cobro-total .num{font-size:2.8rem; font-weight:800; color:#28a745; margin:.25rem 0 0}

.metodo-grid{display:grid; grid-template-columns:repeat(2, 1fr); gap:.55rem}
.metodo-btn{
    border:2px solid #e9ecef; background:#fff; border-radius:12px;
    padding:.85rem; text-align:center; cursor:pointer; font-weight:600;
    transition:all .15s;
}
.metodo-btn:hover{border-color:var(--color-primario)}
.metodo-btn.activo{
    border-color:var(--color-primario);
    background:linear-gradient(180deg, rgba(40,167,69,.08), rgba(40,167,69,.02));
    color:var(--color-primario);
}
.metodo-btn .ic{font-size:1.5rem; display:block; margin-bottom:.3rem}

.quick-amounts{display:grid; grid-template-columns:repeat(4,1fr); gap:.4rem; margin-top:.5rem}
.quick-amounts button{
    border:1px solid #ced4da; background:#fff; padding:.5rem; border-radius:8px;
    font-weight:600; transition:all .15s; cursor:pointer;
}
.quick-amounts button:hover{background:var(--color-primario); color:#fff; border-color:var(--color-primario)}

.cambio-box{
    background:linear-gradient(135deg,#e6f4ea,#c3e6cb); border-radius:12px; padding:1rem;
    text-align:center; margin-top:.85rem;
}
.cambio-box .lbl{font-size:.78rem; color:#155724; text-transform:uppercase; letter-spacing:.5px}
.cambio-box .num{font-size:2rem; font-weight:800; color:#155724; margin:.2rem 0 0}
.cambio-box.negativo{background:linear-gradient(135deg,#fde2e4,#f5c6cb)}
.cambio-box.negativo .lbl, .cambio-box.negativo .num{color:#721c24}
@endsection

@section('contenido')
<div class="tpv-app">

    {{-- ========== IZQUIERDA: catálogo ========== --}}
    <div class="tpv-left">
        <div class="tpv-search">
            <i class="fas fa-search text-muted"></i>
            <input id="buscador-prod" type="text" placeholder="Buscar producto por nombre o código..." autocomplete="off" oninput="filtrarProductos()">
            <button type="button" class="clear-btn" title="Limpiar" onclick="document.getElementById('buscador-prod').value='';filtrarProductos()"><i class="fas fa-times"></i></button>
        </div>

        <div class="tpv-cats">
            <button class="cat-btn activo" data-cat="todas" style="background:linear-gradient(135deg,#343a40,#1a2332)">
                <i class="fas fa-th"></i> Todas
                <span class="cnt">{{ $productos->count() }}</span>
            </button>
            @foreach($categorias as $cat)
                @php $cnt = $productos->where('categoria_id',$cat->id)->count(); @endphp
                <button class="cat-btn" data-cat="{{ $cat->id }}"
                        style="background:linear-gradient(135deg,{{ $cat->color }}, color-mix(in srgb, {{ $cat->color }} 65%, black))">
                    @if($cat->icono)<i class="fas fa-{{ $cat->icono }}"></i>@endif
                    {{ $cat->nombre }}
                    <span class="cnt">{{ $cnt }}</span>
                </button>
            @endforeach
        </div>

        <div class="tpv-products" id="lista-productos">
            @foreach($productos as $p)
                <div class="prod-card js-prod"
                     data-cat="{{ $p->categoria_id }}"
                     data-nombre="{{ strtolower($p->nombre) }}"
                     data-codigo="{{ $p->codigo }}"
                     data-id="{{ $p->id }}"
                     data-precio="{{ $p->precioActual() }}"
                     data-titulo="{{ $p->nombre }}"
                     data-imagen="{{ $p->imagenUrl() }}">

                    @if($p->precio_oferta)<span class="badge-oferta">-{{ number_format(100-($p->precio_oferta/$p->precio*100),0) }}%</span>@endif

                    <div class="tags">
                        @if($p->es_vegetariano)<span class="tag-veg" title="Vegetariano">VEG</span>@endif
                        @if($p->es_vegano)<span class="tag-vegan" title="Vegano">V+</span>@endif
                        @if($p->es_sin_gluten)<span class="tag-sg" title="Sin gluten">SG</span>@endif
                        @if($p->picante)<span class="tag-pic" title="Picante">🌶</span>@endif
                    </div>

                    <div class="img-wrap">
                        @if($p->imagen)
                            <img src="{{ $p->imagenUrl() }}" alt="{{ $p->nombre }}" loading="lazy">
                        @else
                            <i class="fas fa-{{ $p->categoria->icono ?? 'utensils' }} fa-3x" style="color:{{ $p->categoria->color }};opacity:.6"></i>
                        @endif
                    </div>
                    <div class="nombre">{{ $p->nombre }}</div>
                    <div class="precio">{{ $config->formatearPrecio($p->precioActual()) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========== DERECHA: ticket ========== --}}
    <div class="tpv-ticket">
        <div class="ticket-head">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h5><i class="fas fa-receipt"></i> Pedido actual</h5>
                    <div class="small-info" id="ticket-info">Sin productos</div>
                </div>
                <button class="btn-clear-ticket" onclick="limpiarTicket()" title="Vaciar"><i class="fas fa-trash"></i></button>
            </div>

            <div class="tipos-row">
                <button type="button" class="tipo-btn activo" data-tipo="mostrador" onclick="setTipo('mostrador')"><i class="fas fa-store"></i> Mostrador</button>
                @if($config->modulo_mesas)<button type="button" class="tipo-btn" data-tipo="mesa" onclick="setTipo('mesa')"><i class="fas fa-utensils"></i> Mesa</button>@endif
                @if($config->modulo_domicilio)<button type="button" class="tipo-btn" data-tipo="domicilio" onclick="setTipo('domicilio')"><i class="fas fa-motorcycle"></i> Domicilio</button>@endif
                @if($config->modulo_recogida)<button type="button" class="tipo-btn" data-tipo="recogida" onclick="setTipo('recogida')"><i class="fas fa-shopping-bag"></i> Recogida</button>@endif
                @if($config->modulo_telefono)<button type="button" class="tipo-btn" data-tipo="telefono" onclick="setTipo('telefono')"><i class="fas fa-phone"></i> Teléfono</button>@endif
            </div>
        </div>

        <div class="zona-cliente" id="zona-cliente" style="display:none">
            <div class="cliente-buscar">
                <i class="fas fa-user-circle ic"></i>
                <input type="text" id="busca-cliente" class="cliente-input" placeholder="Buscar cliente por nombre o teléfono..." autocomplete="off">
                <div class="cliente-resultados" id="resultados-cliente" style="display:none"></div>
            </div>
            <div id="cliente-seleccionado" style="display:none"></div>
        </div>

        <div class="zona-mesa" id="zona-mesa" style="display:none">
            <label class="fw-bold small d-block mb-1"><i class="fas fa-utensils"></i> Mesa asignada</label>
            <select id="select-mesa" class="form-control form-control-sm">
                <option value="">— Selecciona mesa —</option>
                @foreach($mesas as $m)
                    <option value="{{ $m->id }}">{{ $m->zona->nombre ?? 'Sin zona' }} · Mesa {{ $m->numero }}</option>
                @endforeach
            </select>
        </div>

        <div class="ticket-body" id="ticket-body">
            <div class="ticket-empty">
                <i class="fas fa-shopping-basket"></i>
                <p class="mb-0"><strong>Carrito vacío</strong></p>
                <small>Toca un producto para añadirlo al ticket</small>
            </div>
        </div>

        <div class="ticket-foot">
            <div class="ticket-line"><span>Subtotal</span><span id="t-subtotal">{{ $config->formatearPrecio(0) }}</span></div>
            <div class="ticket-line">
                <span>Descuento</span>
                <span><input type="number" id="t-desc" value="0" step="0.01" min="0" oninput="renderTicket()"> {{ $config->moneda_simbolo }}</span>
            </div>
            <div class="ticket-line" id="row-envio" style="display:none"><span>Envío</span><span id="t-envio">{{ $config->formatearPrecio($config->coste_envio) }}</span></div>
            <div class="ticket-total-big" id="t-total">{{ $config->formatearPrecio(0) }}</div>
            <div class="btns-tpv">
                <button type="button" class="btn-tpv-grande btn-tpv-warn" onclick="guardarPedido(false)"><i class="fas fa-save"></i> Guardar</button>
                <button type="button" class="btn-tpv-grande btn-tpv-success" onclick="abrirCobro()"><i class="fas fa-euro-sign"></i> Cobrar</button>
            </div>
        </div>
    </div>
</div>

{{-- ========== MODAL COBRO ========== --}}
<div class="modal fade modal-cobro" id="modalCobro" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-cash-register"></i> Cobrar pedido</h5>
        <button type="button" class="btn-close btn-close-white close text-white" data-bs-dismiss="modal" data-dismiss="modal" style="opacity:1">×</button>
      </div>
      <div class="modal-body">
        <div class="cobro-total">
            <div class="lbl">Total a cobrar</div>
            <div class="num" id="cobro-total">{{ $config->formatearPrecio(0) }}</div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="fw-bold mb-2"><i class="fas fa-credit-card"></i> Método de pago</label>
                <div class="metodo-grid">
                    <button type="button" class="metodo-btn activo" data-metodo="efectivo" onclick="setMetodo('efectivo')">
                        <span class="ic">💵</span>Efectivo
                    </button>
                    <button type="button" class="metodo-btn" data-metodo="tarjeta" onclick="setMetodo('tarjeta')">
                        <span class="ic">💳</span>Tarjeta
                    </button>
                    <button type="button" class="metodo-btn" data-metodo="bizum" onclick="setMetodo('bizum')">
                        <span class="ic">📱</span>Bizum
                    </button>
                    <button type="button" class="metodo-btn" data-metodo="transferencia" onclick="setMetodo('transferencia')">
                        <span class="ic">🏦</span>Transferencia
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <label class="fw-bold mb-2"><i class="fas fa-money-bill-wave"></i> Importe entregado</label>
                <input type="number" step="0.01" id="cobro-entregado" class="form-control form-control-lg text-right" placeholder="0,00" oninput="calcCambio()" style="font-size:1.5rem;font-weight:700;text-align:right">
                <div class="quick-amounts">
                    <button type="button" onclick="setEntregado('exacto')">Exacto</button>
                    <button type="button" onclick="setEntregado(20)">20€</button>
                    <button type="button" onclick="setEntregado(50)">50€</button>
                    <button type="button" onclick="setEntregado(100)">100€</button>
                </div>
                <div class="cambio-box" id="cambio-box">
                    <div class="lbl">Cambio a devolver</div>
                    <div class="num" id="cobro-cambio">{{ $config->formatearPrecio(0) }}</div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
        <button type="button" class="btn btn-success btn-lg" onclick="guardarPedido(true)"><i class="fas fa-check"></i> Confirmar cobro</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
// ============== ESTADO GLOBAL ==============
let ticket = []; // [{id, titulo, precio, cantidad}]
let tipoPedido = 'mostrador';
let metodoPago = 'efectivo';
let clienteSel = null;
const costeEnvio = {{ (float) ($config->coste_envio ?? 0) }};

// ============== CATÁLOGO ==============
// Click en producto vía delegación (más fiable que onclick inline)
document.getElementById('lista-productos').addEventListener('click', function(e){
    const card = e.target.closest('.js-prod');
    if (!card) return;
    agregarProducto({
        id:     parseInt(card.dataset.id, 10),
        titulo: card.dataset.titulo,
        precio: parseFloat(card.dataset.precio),
        imagen: card.dataset.imagen,
    });
});

// Filtro de categorías
document.querySelectorAll('.cat-btn').forEach(b => {
    b.addEventListener('click', () => {
        document.querySelectorAll('.cat-btn').forEach(x => x.classList.remove('activo'));
        b.classList.add('activo');
        const cat = b.dataset.cat;
        document.querySelectorAll('.js-prod').forEach(p => {
            p.style.display = (cat === 'todas' || p.dataset.cat === cat) ? '' : 'none';
        });
    });
});

function filtrarProductos() {
    const q = (document.getElementById('buscador-prod').value || '').toLowerCase().trim();
    document.querySelectorAll('.js-prod').forEach(p => {
        const m = !q || p.dataset.nombre.includes(q) || (p.dataset.codigo || '').toLowerCase().includes(q);
        p.style.display = m ? '' : 'none';
    });
}

// ============== TICKET ==============
function agregarProducto(p) {
    const ex = ticket.find(i => i.id === p.id);
    if (ex) ex.cantidad += 1;
    else ticket.push({ id: p.id, titulo: p.titulo, precio: p.precio, cantidad: 1 });
    renderTicket();
}

function modificarCantidad(id, delta) {
    const it = ticket.find(i => i.id === id);
    if (!it) return;
    it.cantidad += delta;
    if (it.cantidad <= 0) ticket = ticket.filter(i => i.id !== id);
    renderTicket();
}

function eliminarItem(id) {
    ticket = ticket.filter(i => i.id !== id);
    renderTicket();
}

function limpiarTicket() {
    if (ticket.length > 0 && !confirm('¿Vaciar el ticket actual?')) return;
    ticket = [];
    clienteSel = null;
    document.getElementById('t-desc').value = 0;
    document.getElementById('cliente-seleccionado').style.display = 'none';
    renderTicket();
}

function renderTicket() {
    const cont = document.getElementById('ticket-body');
    if (ticket.length === 0) {
        cont.innerHTML = `
            <div class="ticket-empty">
                <i class="fas fa-shopping-basket"></i>
                <p class="mb-0"><strong>Carrito vacío</strong></p>
                <small>Toca un producto para añadirlo al ticket</small>
            </div>`;
    } else {
        cont.innerHTML = ticket.map(i => `
            <div class="ticket-item">
                <div class="qty-controls">
                    <button type="button" onclick="modificarCantidad(${i.id},-1)">−</button>
                    <span class="qty-num">${i.cantidad}</span>
                    <button type="button" onclick="modificarCantidad(${i.id},1)">+</button>
                </div>
                <div class="info">
                    <div class="nombre">${escapeHtml(i.titulo)}</div>
                    <div class="precio-unit">${formatearMoneda(i.precio)} c/u</div>
                </div>
                <div class="total">${formatearMoneda(i.cantidad * i.precio)}</div>
                <button type="button" class="delete" onclick="eliminarItem(${i.id})" title="Quitar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }
    actualizarTotales();
    actualizarInfo();
}

function escapeHtml(t){
    return String(t).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function actualizarTotales() {
    const sub = ticket.reduce((s, i) => s + i.cantidad * i.precio, 0);
    const desc = parseFloat(document.getElementById('t-desc').value) || 0;
    const env = (tipoPedido === 'domicilio') ? costeEnvio : 0;
    const total = Math.max(0, sub - desc + env);

    document.getElementById('t-subtotal').textContent = formatearMoneda(sub);
    document.getElementById('t-envio').textContent = formatearMoneda(env);
    document.getElementById('t-total').textContent = formatearMoneda(total);
    document.getElementById('cobro-total').textContent = formatearMoneda(total);
    document.getElementById('row-envio').style.display = (tipoPedido === 'domicilio') ? 'flex' : 'none';
}

function actualizarInfo(){
    const el = document.getElementById('ticket-info');
    const n = ticket.reduce((s,i)=>s+i.cantidad,0);
    el.textContent = (n === 0) ? 'Sin productos' : `${n} producto${n!==1?'s':''} · ${ticket.length} líneas`;
}

// ============== TIPO DE PEDIDO ==============
function setTipo(t) {
    tipoPedido = t;
    document.querySelectorAll('.tipo-btn').forEach(b => b.classList.toggle('activo', b.dataset.tipo === t));
    document.getElementById('zona-cliente').style.display = (t === 'domicilio' || t === 'recogida' || t === 'telefono') ? 'block' : 'none';
    document.getElementById('zona-mesa').style.display = (t === 'mesa') ? 'block' : 'none';
    actualizarTotales();
}

// ============== CLIENTE ==============
let busqTimer;
const inputBusca = document.getElementById('busca-cliente');
if (inputBusca) {
    inputBusca.addEventListener('input', e => {
        clearTimeout(busqTimer);
        const q = e.target.value;
        if (q.length < 2) {
            document.getElementById('resultados-cliente').style.display = 'none';
            return;
        }
        busqTimer = setTimeout(() => {
            fetch('{{ route("tpv.buscar-cliente") }}?q=' + encodeURIComponent(q), {headers:{'Accept':'application/json'}})
                .then(r => r.json())
                .then(data => {
                    const cont = document.getElementById('resultados-cliente');
                    if (!Array.isArray(data) || data.length === 0) {
                        cont.innerHTML = '<div class="item text-muted">Sin resultados</div>';
                    } else {
                        cont.innerHTML = data.map(c => `
                            <div class="item" data-cli='${JSON.stringify(c).replace(/'/g,"&#39;")}'>
                                <strong>${escapeHtml(c.nombre)} ${escapeHtml(c.apellidos || '')}</strong>
                                ${c.telefono ? `<br><small><i class="fas fa-phone"></i> ${escapeHtml(c.telefono)}</small>` : ''}
                                ${c.direccion ? `<br><small class="text-muted">${escapeHtml(c.direccion)}, ${escapeHtml(c.ciudad || '')}</small>` : ''}
                            </div>`).join('');
                        cont.querySelectorAll('.item').forEach(el => {
                            el.addEventListener('click', () => {
                                seleccionarCliente(JSON.parse(el.dataset.cli));
                            });
                        });
                    }
                    cont.style.display = 'block';
                });
        }, 280);
    });
}

function seleccionarCliente(c) {
    clienteSel = c;
    const html = `
        <div class="cliente-sel">
            <i class="fas fa-user-check mt-1"></i>
            <div class="flex-grow-1">
                <strong>${escapeHtml(c.nombre)} ${escapeHtml(c.apellidos || '')}</strong>
                ${c.telefono ? `<br><i class="fas fa-phone"></i> ${escapeHtml(c.telefono)}` : ''}
                ${c.direccion ? `<br><i class="fas fa-map-marker-alt"></i> ${escapeHtml(c.direccion)}, ${escapeHtml(c.ciudad || '')}` : ''}
            </div>
            <button type="button" class="quitar" onclick="quitarCliente()"><i class="fas fa-times"></i></button>
        </div>`;
    const wrap = document.getElementById('cliente-seleccionado');
    wrap.innerHTML = html;
    wrap.style.display = 'block';
    document.getElementById('busca-cliente').value = '';
    document.getElementById('resultados-cliente').style.display = 'none';
}

function quitarCliente() {
    clienteSel = null;
    document.getElementById('cliente-seleccionado').style.display = 'none';
}

// ============== COBRO ==============
function setMetodo(m) {
    metodoPago = m;
    document.querySelectorAll('.metodo-btn').forEach(b => b.classList.toggle('activo', b.dataset.metodo === m));
}

function calcCambio() {
    const ent = parseFloat(document.getElementById('cobro-entregado').value) || 0;
    const totalActual = ticket.reduce((s, i) => s + i.cantidad * i.precio, 0)
        - (parseFloat(document.getElementById('t-desc').value) || 0)
        + (tipoPedido === 'domicilio' ? costeEnvio : 0);
    const cam = ent - totalActual;
    const box = document.getElementById('cambio-box');
    if (cam < 0) {
        box.classList.add('negativo');
        box.querySelector('.lbl').textContent = 'Falta por cobrar';
        document.getElementById('cobro-cambio').textContent = formatearMoneda(Math.abs(cam));
    } else {
        box.classList.remove('negativo');
        box.querySelector('.lbl').textContent = 'Cambio a devolver';
        document.getElementById('cobro-cambio').textContent = formatearMoneda(cam);
    }
}

function setEntregado(amount) {
    const total = ticket.reduce((s, i) => s + i.cantidad * i.precio, 0)
        - (parseFloat(document.getElementById('t-desc').value) || 0)
        + (tipoPedido === 'domicilio' ? costeEnvio : 0);
    document.getElementById('cobro-entregado').value = (amount === 'exacto') ? total.toFixed(2) : amount;
    calcCambio();
}

// Helper: muestra/oculta modal con BS5 o BS4 (jQuery)
function mostrarModal(selector){
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const el = document.querySelector(selector);
            const inst = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            inst.show();
            return;
        }
    } catch(e){ console.warn('BS5 modal falló, usando jQuery:', e); }
    if (window.jQuery && jQuery.fn.modal) {
        jQuery(selector).modal('show');
    } else {
        // Fallback manual si ninguna lib está disponible
        const el = document.querySelector(selector);
        if (el) {
            el.classList.add('show');
            el.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }
}
function ocultarModal(selector){
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const inst = bootstrap.Modal.getInstance(document.querySelector(selector));
            if (inst) { inst.hide(); return; }
        }
    } catch(e){ console.warn('BS5 modal hide falló:', e); }
    if (window.jQuery && jQuery.fn.modal) {
        jQuery(selector).modal('hide');
    } else {
        const el = document.querySelector(selector);
        if (el) {
            el.classList.remove('show');
            el.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }
}

function abrirCobro() {
    try {
        if (ticket.length === 0) { alert('Añade productos primero'); return; }
        setMetodo('efectivo');
        setEntregado('exacto');
        mostrarModal('#modalCobro');
    } catch(e) {
        console.error('Error abrirCobro:', e);
        alert('Error al abrir el cobro: ' + e.message);
    }
}

function guardarPedido(cobrar) {
    try {
        if (ticket.length === 0) { alert('Añade productos primero'); return; }

        const selMesa = document.getElementById('select-mesa');
        if (tipoPedido === 'mesa' && (!selMesa || !selMesa.value)) {
            alert('Selecciona una mesa antes de guardar');
            return;
        }

        const desc = parseFloat(document.getElementById('t-desc').value) || 0;
        const env  = (tipoPedido === 'domicilio') ? costeEnvio : 0;
        const total = ticket.reduce((s, i) => s + i.cantidad * i.precio, 0) - desc + env;

        const data = {
            tipo:         tipoPedido,
            cliente_id:   clienteSel ? clienteSel.id : null,
            mesa_id:      tipoPedido === 'mesa' ? selMesa.value : null,
            descuento:    desc,
            coste_envio:  env,
            productos:    ticket.map(i => ({ id: i.id, cantidad: i.cantidad, precio: i.precio })),
            pagos:        cobrar ? [{ metodo: metodoPago, importe: total }] : null,
        };

        const csrfMeta = document.querySelector('meta[name=csrf-token]');
        if (!csrfMeta) { alert('Falta el token CSRF en la página. Recarga el TPV (Ctrl+F5).'); return; }

        // Bloqueamos los botones para evitar doble envío
        document.querySelectorAll('.btn-tpv-grande, #modalCobro .btn-success').forEach(b => b.disabled = true);

        fetch('{{ route("tpv.pedido.guardar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':  csrfMeta.content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(async r => {
            const text = await r.text();
            let json = null;
            try { json = JSON.parse(text); } catch(_) {}
            if (!r.ok) {
                // Mostrar el primer mensaje de validación si lo hay
                let msg = 'Error ' + r.status;
                if (json && json.errors) {
                    msg += ': ' + Object.values(json.errors).flat().join(' · ');
                } else if (json && json.message) {
                    msg += ': ' + json.message;
                } else if (text) {
                    msg += ': ' + text.substring(0, 200);
                }
                throw new Error(msg);
            }
            return json;
        })
        .then(res => {
            if (res && res.ok) {
                alert(res.mensaje || 'Pedido guardado correctamente');
                ocultarModal('#modalCobro');
                limpiarTicket();
                if (cobrar && res.pedido && res.pedido.id) {
                    window.open('{{ url("/tpv/ticket") }}/' + res.pedido.id, '_blank', 'width=400,height=620');
                }
            } else {
                alert('Respuesta inesperada del servidor');
                console.error('Respuesta:', res);
            }
        })
        .catch(e => {
            console.error('Error guardarPedido:', e);
            alert(e.message || 'Error desconocido al guardar el pedido');
        })
        .finally(() => {
            document.querySelectorAll('.btn-tpv-grande, #modalCobro .btn-success').forEach(b => b.disabled = false);
        });
    } catch(e) {
        console.error('Error guardarPedido (sync):', e);
        alert('Error: ' + e.message);
    }
}

// Inicialización
renderTicket();
</script>
@endsection
