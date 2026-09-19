<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('titulo', 'Dashboard') | {{ $config->nombre_empresa ?? config('app.name') }}</title>

@if($config && $config->favicon)
    <link rel="icon" href="{{ asset('storage/'.$config->favicon) }}">
@endif

{{-- Bootstrap 5 + AdminLTE 3 vía CDN --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

<style>
:root{
    --color-primario: {{ $config->color_primario ?? '#28a745' }};
    --color-secundario: {{ $config->color_secundario ?? '#343a40' }};
}
body, .main-sidebar, .nav-sidebar, .form-control, .btn, .card-title { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
.brand-link { background: linear-gradient(135deg, var(--color-primario), color-mix(in srgb, var(--color-primario) 70%, black)) !important; color:#fff !important; }
.brand-link .brand-text { color:#fff !important; font-weight:600; }
.main-sidebar { background:#1a2332 !important; }
.nav-sidebar > .nav-item > .nav-link.active { background: var(--color-primario) !important; color:#fff; box-shadow:0 4px 12px rgba(0,0,0,.15); }
.nav-sidebar .nav-link { border-radius:6px; margin:2px 8px; }
.nav-sidebar .nav-link:hover:not(.active) { background:rgba(255,255,255,.06); }
.content-wrapper { background:#f4f6f9; }
.kpi-card { border:none; border-radius:14px; overflow:hidden; transition:transform .2s, box-shadow .2s; box-shadow:0 2px 12px rgba(0,0,0,.05); }
.kpi-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.1); }
.kpi-card .icon { font-size:2.5rem; opacity:.25; position:absolute; right:1rem; top:1rem; }
.kpi-card h2 { font-weight:700; margin:0; }
.bg-grad-success { background:linear-gradient(135deg,#28a745,#1e7e34); color:#fff; }
.bg-grad-info { background:linear-gradient(135deg,#17a2b8,#117a8b); color:#fff; }
.bg-grad-warning { background:linear-gradient(135deg,#ffc107,#d39e00); color:#fff; }
.bg-grad-danger { background:linear-gradient(135deg,#dc3545,#bd2130); color:#fff; }
.bg-grad-primary { background:linear-gradient(135deg,#007bff,#0056b3); color:#fff; }
.bg-grad-dark { background:linear-gradient(135deg,#343a40,#1d2124); color:#fff; }
.card { border:none; border-radius:12px; box-shadow:0 1px 6px rgba(0,0,0,.04); }
.card-header { border-bottom:1px solid #eef0f3; background:#fff; border-radius:12px 12px 0 0 !important; font-weight:600; }
.btn-primary { background:var(--color-primario); border-color:var(--color-primario); }
.btn-primary:hover, .btn-primary:focus { background:color-mix(in srgb, var(--color-primario) 85%, black); border-color:color-mix(in srgb, var(--color-primario) 85%, black); }
.text-primario { color:var(--color-primario); }
.bg-primario { background:var(--color-primario); }
.badge-soft { background:rgba(40,167,69,.12); color:var(--color-primario); font-weight:500; padding:.4em .7em; }
.tabla-bonita th { background:#f8f9fa; font-weight:600; font-size:.85rem; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; }
.alert { border:none; border-radius:10px; }
.main-header { box-shadow:0 1px 3px rgba(0,0,0,.04); border:0; }
@yield('estilos')
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- NAVBAR --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('tpv.index') }}" class="nav-link"><i class="fas fa-cash-register text-success"></i> Abrir TPV</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link"><i class="far fa-clock"></i> <span id="reloj"></span></span>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user-circle"></i> {{ auth()->user()->name ?? 'Usuario' }}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item"><i class="fas fa-user"></i> Mi perfil</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    {{-- SIDEBAR --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            @if($config && $config->logo)
                <img src="{{ asset('storage/'.$config->logo) }}" alt="logo" class="brand-image" style="opacity:.95">
            @else
                <i class="fas fa-utensils brand-image" style="margin-left:.8rem;font-size:1.5rem"></i>
            @endif
            <span class="brand-text">{{ $config->nombre_empresa ?? 'TPV FastFood' }}</span>
        </a>
        <div class="sidebar">
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-header text-uppercase">Principal</li>
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tpv.index') }}" class="nav-link {{ request()->routeIs('tpv.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cash-register"></i><p>TPV / Punto de Venta</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pedidos.index') }}" class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-receipt"></i><p>Pedidos</p>
                        </a>
                    </li>

                    <li class="nav-header text-uppercase">Catálogo</li>
                    <li class="nav-item">
                        <a href="{{ route('categorias.index') }}" class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tags"></i><p>Categorías</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('productos.index') }}" class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-pizza-slice"></i><p>Productos</p>
                        </a>
                    </li>

                    <li class="nav-header text-uppercase">CRM</li>
                    <li class="nav-item">
                        <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i><p>Clientes</p>
                        </a>
                    </li>

                    @if(($config->facturacion_electronica_pe ?? false))
                    <li class="nav-header text-uppercase">SUNAT</li>
                    <li class="nav-item">
                        <a href="{{ route('comprobantes.index') }}" class="nav-link {{ request()->routeIs('comprobantes.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar" style="color:#ee2737"></i><p>Comprobantes Electrónicos</p>
                        </a>
                    </li>
                    @endif

                    <li class="nav-header text-uppercase">Sistema</li>
                    <li class="nav-item">
                        <a href="{{ route('configuracion.edit') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i><p>Configuración</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('backup.index') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shield-alt"></i><p>Copias de seguridad</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- CONTENIDO --}}
    <div class="content-wrapper">
        <section class="content-header pt-3">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1 class="m-0 h3">@yield('titulo', 'Dashboard')</h1>
                        @hasSection('subtitulo')<p class="text-muted mb-0 small">@yield('subtitulo')</p>@endif
                    </div>
                    <div class="col-sm-6 text-right">
                        @yield('botones')
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @yield('contenido')
            </div>
        </section>
    </div>

    {{-- FOOTER --}}
    <footer class="main-footer text-sm">
        <strong>&copy; {{ date('Y') }} {{ $config->nombre_empresa ?? config('app.name') }}.</strong> Todos los derechos reservados.
        <div class="float-right d-none d-sm-inline-block"><b>Versión</b> 1.0.0</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function tickReloj(){
    const f = new Date();
    const dias=['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
    const txt = dias[f.getDay()]+' '+f.toLocaleDateString('es-ES')+' '+f.toLocaleTimeString('es-ES');
    const el = document.getElementById('reloj'); if(el) el.textContent = txt;
}
setInterval(tickReloj,1000); tickReloj();

// Helper formato moneda
window.formatearMoneda = function(num){
    const dec = {{ $config->decimales ?? 2 }};
    const sd = "{{ $config->separador_decimal ?? ',' }}";
    const sm = "{{ $config->separador_miles ?? '.' }}";
    const sim = "{{ $config->moneda_simbolo ?? '€' }}";
    const pos = "{{ $config->moneda_posicion ?? 'derecha' }}";
    let n = (Number(num)||0).toFixed(dec);
    let [ent, decp] = n.split('.');
    ent = ent.replace(/\B(?=(\d{3})+(?!\d))/g, sm);
    const txt = decp ? ent+sd+decp : ent;
    return pos === 'izquierda' ? sim+' '+txt : txt+' '+sim;
};
</script>
@yield('scripts')
</body>
</html>
