@extends('layouts.app')
@section('titulo', 'Dashboard')
@section('subtitulo', 'Resumen general del negocio - '.now()->isoFormat('dddd, D [de] MMMM [de] YYYY'))

@section('contenido')

@php
    // Color primario resuelto a hex (Chart.js NO soporta var(--xxx))
    $colorPrim = $config->color_primario ?? '#28a745';
    $colorSec  = $config->color_secundario ?? '#343a40';
    $sinDatos  = ($pedidosHoy == 0 && $ventasMes == 0 && array_sum($data7Total) == 0);
@endphp

@if($sinDatos)
    <div class="alert alert-info shadow-sm">
        <h5 class="mb-2"><i class="fas fa-info-circle"></i> No hay datos de pedidos todavía</h5>
        <p class="mb-2">El dashboard se llena con datos reales de pedidos. Para ver los gráficos con información demo, ejecuta los seeders:</p>
        <pre class="bg-dark text-white p-2 rounded mb-2"><code>php artisan migrate:fresh --seed</code></pre>
        <small class="text-muted">Esto generará ~900 pedidos en los últimos 60 días, 12 clientes, 11 cajas y todos los datos para que el dashboard cobre vida.</small>
    </div>
@endif

{{-- KPIs --}}
<div class="row">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="kpi-card bg-grad-success p-4 position-relative h-100">
            <i class="fas fa-euro-sign icon"></i>
            <div class="text-uppercase small fw-bold opacity-75">Ventas hoy</div>
            <h2>{{ $config->formatearPrecio($ventasHoy) }}</h2>
            <div class="small mt-2">
                @if($variacionDia >= 0)
                    <i class="fas fa-arrow-up"></i> +{{ number_format($variacionDia,1) }}%
                @else
                    <i class="fas fa-arrow-down"></i> {{ number_format($variacionDia,1) }}%
                @endif
                vs ayer
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="kpi-card bg-grad-info p-4 position-relative h-100">
            <i class="fas fa-receipt icon"></i>
            <div class="text-uppercase small fw-bold opacity-75">Pedidos hoy</div>
            <h2>{{ $pedidosHoy }}</h2>
            <div class="small mt-2"><i class="fas fa-ticket-alt"></i> Ticket medio: {{ $config->formatearPrecio($tickedMedio) }}</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="kpi-card bg-grad-primary p-4 position-relative h-100">
            <i class="fas fa-chart-line icon"></i>
            <div class="text-uppercase small fw-bold opacity-75">Ventas del mes</div>
            <h2>{{ $config->formatearPrecio($ventasMes) }}</h2>
            <div class="small mt-2">
                @if($variacionMes >= 0)
                    <i class="fas fa-arrow-up"></i> +{{ number_format($variacionMes,1) }}%
                @else
                    <i class="fas fa-arrow-down"></i> {{ number_format($variacionMes,1) }}%
                @endif
                vs mes anterior
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="kpi-card bg-grad-warning p-4 position-relative h-100">
            <i class="fas fa-users icon"></i>
            <div class="text-uppercase small fw-bold opacity-75">Clientes</div>
            <h2>{{ $totalClientes }}</h2>
            <div class="small mt-2"><i class="fas fa-user-plus"></i> +{{ $clientesNuevosMes }} nuevos este mes</div>
        </div>
    </div>
</div>

{{-- Alertas --}}
@if($stockBajo > 0)
<div class="alert alert-warning shadow-sm">
    <i class="fas fa-exclamation-triangle"></i>
    Hay <strong>{{ $stockBajo }}</strong> producto(s) con stock bajo.
    <a href="{{ route('productos.index', ['activo'=>1]) }}" class="alert-link">Ver productos &rarr;</a>
</div>
@endif

<div class="row">
    {{-- Gráfico ventas 7 días --}}
    <div class="col-xl-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-area text-success"></i> Ventas últimos 7 días</span>
                <div class="badge badge-soft">Total: {{ $config->formatearPrecio(array_sum($data7Total)) }}</div>
            </div>
            <div class="card-body">
                <div style="position:relative;height:300px"><canvas id="chart7dias"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Donut tipos pedido --}}
    <div class="col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-pie text-success"></i> Ventas por tipo (mes)</div>
            <div class="card-body">
                @if(count($ventasPorTipo) === 0)
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-pie fa-3x opacity-25 mb-2"></i>
                        <p class="mb-0">Sin pedidos este mes</p>
                    </div>
                @else
                    <div style="position:relative;height:280px"><canvas id="chartTipos"></canvas></div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Ventas por hora --}}
    <div class="col-xl-7 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-clock text-success"></i> Ventas por hora (hoy)</div>
            <div class="card-body">
                <div style="position:relative;height:250px"><canvas id="chartHoras"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Top productos --}}
    <div class="col-xl-5 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <span><i class="fas fa-trophy text-warning"></i> Top productos del mes</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover tabla-bonita mb-0">
                    <thead><tr><th>#</th><th>Producto</th><th class="text-center">Uds.</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @forelse($topProductos as $i => $p)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $i+1 }}</span></td>
                                <td>{{ $p->nombre_producto }}</td>
                                <td class="text-center">{{ number_format($p->cantidad,0) }}</td>
                                <td class="text-right fw-bold">{{ $config->formatearPrecio($p->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Sin datos aún</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Pedidos en curso --}}
    <div class="col-xl-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <span><i class="fas fa-fire text-danger"></i> Pedidos en curso</span>
                <a href="{{ route('pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover tabla-bonita mb-0">
                    <thead><tr><th>Nº</th><th>Tipo</th><th>Cliente / Mesa</th><th>Estado</th><th class="text-right">Total</th><th></th></tr></thead>
                    <tbody>
                        @forelse($pedidosEnCurso as $p)
                            <tr>
                                <td class="fw-bold">{{ $p->numero }}</td>
                                <td><i class="fas fa-{{ $p->tipo == 'mesa' ? 'utensils' : ($p->tipo == 'domicilio' ? 'motorcycle' : ($p->tipo == 'recogida' ? 'shopping-bag' : 'phone')) }}"></i> {{ \App\Models\Pedido::TIPOS[$p->tipo] ?? $p->tipo }}</td>
                                <td>{{ $p->mesa ? 'Mesa '.$p->mesa->numero : ($p->cliente_nombre ?: 'Sin cliente') }}</td>
                                <td><span class="badge bg-{{ $p->colorEstado() }}">{{ \App\Models\Pedido::ESTADOS[$p->estado] ?? $p->estado }}</span></td>
                                <td class="text-right fw-bold">{{ $config->formatearPrecio($p->total) }}</td>
                                <td><a href="{{ route('pedidos.show', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted"><i class="far fa-smile-beam"></i> Sin pedidos pendientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Categorías --}}
    <div class="col-xl-5 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-layer-group text-success"></i> Ventas por categoría</div>
            <div class="card-body">
                @forelse($ventasCategorias as $c)
                    @php $maxc = $ventasCategorias->max('total') ?: 1; $pct = ($c->total/$maxc)*100; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="fw-bold">{{ $c->nombre }}</span>
                            <span class="text-muted">{{ $config->formatearPrecio($c->total) }}</span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:6px">
                            <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $c->color }};"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">Sin datos aún</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ============== CONFIGURACIÓN GLOBAL CHART.JS ==============
const COLOR_PRIM = '{{ $colorPrim }}';
const COLOR_SEC  = '{{ $colorSec }}';
const fmt = (v) => window.formatearMoneda(v);

// Helper: convertir hex a rgba
function hexToRgba(hex, alpha){
    hex = hex.replace('#','');
    const r = parseInt(hex.substring(0,2),16);
    const g = parseInt(hex.substring(2,4),16);
    const b = parseInt(hex.substring(4,6),16);
    return `rgba(${r},${g},${b},${alpha})`;
}

// Datos del backend
const labels7    = @json($labels7);
const data7Total = @json(array_map('floatval', $data7Total));
const data7Num   = @json(array_map('intval', $data7Num));
const horasLabels= @json($horasLabels);
const horasData  = @json(array_map('floatval', $horasData));
const tiposData  = {!! json_encode($ventasPorTipo, JSON_FORCE_OBJECT) !!};
const tipoNombres= @json(\App\Models\Pedido::TIPOS);

// ============== GRÁFICO 7 DÍAS (línea + nº pedidos) ==============
const ctx7 = document.getElementById('chart7dias');
if (ctx7) {
    new Chart(ctx7, {
        type: 'line',
        data: {
            labels: labels7,
            datasets: [{
                label: 'Ventas (€)',
                data: data7Total,
                borderColor: COLOR_PRIM,
                backgroundColor: hexToRgba(COLOR_PRIM, 0.15),
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 5,
                pointBackgroundColor: '#fff',
                pointBorderColor: COLOR_PRIM,
                pointBorderWidth: 2,
                yAxisID: 'y'
            },{
                label: 'Nº pedidos',
                data: data7Num,
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23,162,184,0.1)',
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#17a2b8',
                pointBorderWidth: 2,
                yAxisID: 'y2'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, padding: 15 } },
                tooltip: {
                    callbacks: {
                        label: function(c) {
                            const v = c.dataset.label.includes('€') ? fmt(c.raw) : c.raw;
                            return c.dataset.label + ': ' + v;
                        }
                    }
                }
            },
            scales: {
                y:  { beginAtZero: true, position: 'left',  ticks: { callback: v => fmt(v) }, grid: { color: '#eef0f3' } },
                y2: { beginAtZero: true, position: 'right', ticks: { stepSize: 1 }, grid: { drawOnChartArea: false } },
                x:  { grid: { display: false } }
            }
        }
    });
}

// ============== DONUT TIPOS DE PEDIDO ==============
const ctxT = document.getElementById('chartTipos');
if (ctxT) {
    const keys = Object.keys(tiposData);
    const vals = Object.values(tiposData).map(Number);
    new Chart(ctxT, {
        type: 'doughnut',
        data: {
            labels: keys.map(k => tipoNombres[k] || k),
            datasets: [{
                data: vals,
                backgroundColor: ['#28a745','#17a2b8','#ffc107','#dc3545','#6f42c1','#fd7e14'],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, boxWidth: 10 } },
                tooltip: {
                    callbacks: {
                        label: c => c.label + ': ' + fmt(c.raw)
                    }
                }
            }
        }
    });
}

// ============== HISTOGRAMA POR HORA ==============
const ctxH = document.getElementById('chartHoras');
if (ctxH) {
    new Chart(ctxH, {
        type: 'bar',
        data: {
            labels: horasLabels,
            datasets: [{
                label: 'Ventas',
                data: horasData,
                backgroundColor: function(ctx){
                    const chart = ctx.chart;
                    const {ctx: c, chartArea} = chart;
                    if (!chartArea) return COLOR_PRIM;
                    const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    g.addColorStop(0, COLOR_PRIM);
                    g.addColorStop(1, hexToRgba(COLOR_PRIM, 0.3));
                    return g;
                },
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 18
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => fmt(c.raw) } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => fmt(v) }, grid: { color: '#eef0f3' } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endsection
