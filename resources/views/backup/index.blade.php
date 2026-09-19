@extends('layouts.app')
@section('titulo', 'Sistema y Copias de seguridad')
@section('subtitulo', 'Protege tu información y gestiona el ciclo de vida del sistema')

@section('estilos')
<style>
.hero-backup{
    background:linear-gradient(135deg,#1a2332 0%,#28a745 120%);
    border-radius:18px; padding:2rem; color:#fff; position:relative; overflow:hidden;
    box-shadow:0 12px 30px rgba(40,167,69,.25); margin-bottom:1.5rem;
}
.hero-backup::after{
    content:""; position:absolute; right:-60px; bottom:-60px;
    width:240px; height:240px; border-radius:50%;
    background:radial-gradient(circle, rgba(255,255,255,.18) 0%, transparent 70%);
}
.hero-backup .icon-shield{
    width:90px; height:90px; border-radius:24px;
    background:rgba(255,255,255,.18); backdrop-filter:blur(8px);
    display:flex; align-items:center; justify-content:center;
    font-size:2.6rem; box-shadow:0 8px 20px rgba(0,0,0,.18);
}
.stat-mini{
    background:rgba(255,255,255,.12); border-radius:12px;
    padding:.85rem 1.1rem; backdrop-filter:blur(6px);
}
.stat-mini .num{font-size:1.6rem; font-weight:700; line-height:1.1; margin:0}
.stat-mini .lbl{font-size:.78rem; opacity:.85; text-transform:uppercase; letter-spacing:.5px}

.action-card{
    border-radius:16px; padding:1.6rem; height:100%; position:relative; overflow:hidden;
    transition:transform .25s, box-shadow .25s; border:none;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
}
.action-card:hover{transform:translateY(-4px); box-shadow:0 16px 36px rgba(0,0,0,.12);}
.action-card .av-icon{
    width:64px; height:64px; border-radius:18px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.8rem; margin-bottom:1rem; color:#fff;
    box-shadow:0 6px 16px rgba(0,0,0,.15);
}
.action-card.crear .av-icon{background:linear-gradient(135deg,#28a745,#1e7e34)}
.action-card.restaurar .av-icon{background:linear-gradient(135deg,#fd7e14,#c46410)}
.action-card.reset .av-icon{background:linear-gradient(135deg,#dc3545,#a71d2a)}
.action-card .av-titulo{font-size:1.15rem; font-weight:700; margin-bottom:.4rem; color:#1a2332}
.action-card .av-desc{font-size:.9rem; color:#6c757d; margin-bottom:1rem; min-height:42px}
.action-card.zona-peligro{
    background:linear-gradient(180deg,#fff 0%,#fff5f5 100%);
    border:2px solid #ffe1e1;
}
.action-card.zona-peligro::before{
    content:"PELIGROSO"; position:absolute; top:14px; right:-30px;
    background:#dc3545; color:#fff; font-size:.65rem; font-weight:700;
    padding:.2rem 2rem; transform:rotate(35deg); letter-spacing:1px;
}

.tabla-backup th{background:#f8f9fa; font-weight:600; font-size:.82rem; text-transform:uppercase; letter-spacing:.5px; color:#6c757d}
.tabla-backup tr:hover{background:#fafafa}
.archivo-backup{display:flex; align-items:center; gap:.6rem}
.archivo-backup .ic{
    width:36px; height:36px; border-radius:8px;
    background:linear-gradient(135deg,#e6f4ea,#c3e6cb);
    display:flex; align-items:center; justify-content:center; color:#155724; font-size:1rem;
}

.modal-peligro .modal-header{
    background:linear-gradient(135deg,#dc3545,#a71d2a); color:#fff; border:0;
}
.modal-restaurar .modal-header{
    background:linear-gradient(135deg,#fd7e14,#c46410); color:#fff; border:0;
}
.modal-content{border:0; border-radius:16px; overflow:hidden}
.input-confirma{
    border:2px dashed #dc3545; border-radius:10px; padding:.75rem 1rem;
    text-transform:uppercase; letter-spacing:3px; font-weight:700; text-align:center;
    font-size:1.1rem;
}
.input-confirma:focus{outline:0; box-shadow:0 0 0 3px rgba(220,53,69,.18); border-color:#dc3545}

.opcion-mantener{
    background:#f8f9fa; border-radius:10px; padding:.75rem 1rem; margin-bottom:.5rem;
    display:flex; align-items:center; gap:.7rem; cursor:pointer;
    transition:background .15s, border .15s; border:1px solid transparent;
}
.opcion-mantener:hover{background:#eef0f3}
.opcion-mantener input[type=checkbox]{transform:scale(1.25); margin:0}
.opcion-mantener.checked{background:#e6f4ea; border-color:#28a745}
.opcion-mantener .it-ico{
    width:34px; height:34px; border-radius:8px; background:#fff;
    display:flex; align-items:center; justify-content:center; color:#28a745;
}

.upload-zone{
    border:2px dashed #fd7e14; border-radius:14px; padding:1.5rem;
    text-align:center; background:#fff8f0; transition:background .2s;
    cursor:pointer;
}
.upload-zone:hover{background:#ffefd9}
.upload-zone.dragging{background:#ffe1bd}
.upload-zone i.fa-cloud-upload-alt{font-size:2.2rem; color:#fd7e14; margin-bottom:.5rem}

.bg-grad-info-soft{background:linear-gradient(135deg,#e3f2fd,#bbdefb); color:#0d47a1}
</style>
@endsection

@section('contenido')

{{-- HERO con stats --}}
<div class="hero-backup">
    <div class="d-flex align-items-center" style="position:relative; z-index:2">
        <div class="icon-shield me-3 mr-3"><i class="fas fa-shield-alt"></i></div>
        <div class="flex-grow-1">
            <h3 class="mb-1" style="font-weight:700">Seguridad y Sistema</h3>
            <p class="mb-0 opacity-75">Crea copias periódicas, restaura desde un archivo o resetea el sistema cuando empieces con una empresa nueva.</p>
        </div>
    </div>
    <div class="row mt-3" style="position:relative; z-index:2">
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-mini">
                <div class="num">{{ number_format($stats['tablas']) }}</div>
                <div class="lbl"><i class="fas fa-table"></i> Tablas</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-mini">
                <div class="num">{{ number_format($stats['pedidos']) }}</div>
                <div class="lbl"><i class="fas fa-receipt"></i> Pedidos</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-mini">
                <div class="num">{{ number_format($stats['productos']) }}</div>
                <div class="lbl"><i class="fas fa-pizza-slice"></i> Productos</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-mini">
                <div class="num">{{ $service->formatearTamano($stats['tamano_bd']) }}</div>
                <div class="lbl"><i class="fas fa-database"></i> Tamaño BD</div>
            </div>
        </div>
    </div>
</div>

{{-- 3 ACCIONES PRINCIPALES --}}
<div class="row">
    {{-- CREAR COPIA --}}
    <div class="col-lg-4 mb-4">
        <div class="card action-card crear">
            <div class="av-icon"><i class="fas fa-cloud-download-alt"></i></div>
            <div class="av-titulo">Crear copia de seguridad</div>
            <p class="av-desc">Genera un archivo .sql con la estructura y datos completos de tu sistema.</p>
            <form method="POST" action="{{ route('backup.crear') }}">
                @csrf
                <button type="submit" class="btn btn-success w-100"><i class="fas fa-save"></i> Crear copia ahora</button>
            </form>
            <a href="{{ route('backup.descargar-directo') }}" class="btn btn-outline-success w-100 mt-2">
                <i class="fas fa-download"></i> Descargar sin guardar
            </a>
            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Recomendado: crear copias antes de cambios importantes o al cierre del día.</small>
        </div>
    </div>

    {{-- RESTAURAR --}}
    <div class="col-lg-4 mb-4">
        <div class="card action-card restaurar">
            <div class="av-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="av-titulo">Restaurar copia</div>
            <p class="av-desc">Carga un archivo .sql de copia previa para restaurar el sistema completo.</p>
            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#modalRestaurar" data-target="#modalRestaurar">
                <i class="fas fa-upload"></i> Subir y restaurar
            </button>
            <small class="text-muted d-block mt-2"><i class="fas fa-exclamation-triangle text-warning"></i> Sobrescribe los datos actuales. Crea antes una copia.</small>
        </div>
    </div>

    {{-- RESET --}}
    <div class="col-lg-4 mb-4">
        <div class="card action-card reset zona-peligro">
            <div class="av-icon"><i class="fas fa-power-off"></i></div>
            <div class="av-titulo">Empresa nueva (Reset)</div>
            <p class="av-desc">Borra los datos del sistema para empezar desde cero con una nueva empresa.</p>
            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#modalReset" data-target="#modalReset">
                <i class="fas fa-undo-alt"></i> Resetear sistema
            </button>
            <small class="text-muted d-block mt-2"><i class="fas fa-skull-crossbones text-danger"></i> Acción IRREVERSIBLE. Crea una copia primero.</small>
        </div>
    </div>
</div>

{{-- LISTADO DE COPIAS GUARDADAS --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-archive text-success"></i> Copias de seguridad guardadas</span>
        <span class="badge badge-soft">{{ count($backups) }} archivo(s)</span>
    </div>
    <div class="card-body p-0">
        <table class="table tabla-backup mb-0">
            <thead><tr>
                <th>Archivo</th><th>Fecha</th><th>Tamaño</th><th class="text-right">Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($backups as $b)
                <tr>
                    <td>
                        <div class="archivo-backup">
                            <div class="ic"><i class="fas fa-file-code"></i></div>
                            <div>
                                <strong>{{ $b['filename'] }}</strong>
                                <div class="small text-muted">SQL · UTF-8 · MySQL</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong>{{ $b['modified']->format('d/m/Y') }}</strong>
                        <div class="small text-muted">{{ $b['modified']->format('H:i:s') }} ({{ $b['modified']->diffForHumans() }})</div>
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $service->formatearTamano($b['size']) }}</span></td>
                    <td class="text-right">
                        <a href="{{ route('backup.descargar', $b['filename']) }}" class="btn btn-sm btn-outline-success" title="Descargar"><i class="fas fa-download"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-warning" title="Restaurar desde esta copia"
                                onclick="abrirRestaurarGuardado('{{ $b['filename'] }}')">
                            <i class="fas fa-undo"></i>
                        </button>
                        <form action="{{ route('backup.eliminar', $b['filename']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta copia de seguridad?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-5 text-muted">
                    <i class="far fa-folder-open fa-3x mb-2 opacity-50"></i>
                    <p class="mb-0">Aún no has creado ninguna copia de seguridad.</p>
                    <small>Pulsa "Crear copia ahora" arriba para empezar.</small>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- INFO TÉCNICA --}}
<div class="card bg-light">
    <div class="card-body small text-muted">
        <i class="fas fa-info-circle text-info"></i>
        <strong>Cómo funciona:</strong>
        Las copias se guardan en <code>storage/app/backups/</code> y son archivos SQL planos compatibles con cualquier servidor MySQL/MariaDB.
        Para restauraciones manuales fuera de la app puedes usar phpMyAdmin con la opción "Importar".
        El reset solo elimina datos de negocio: nunca borrará al administrador con email <code>admin@tpv.local</code>.
    </div>
</div>

{{-- ============================================== --}}
{{-- MODAL RESTAURAR (subir archivo)                 --}}
{{-- ============================================== --}}
<div class="modal fade modal-restaurar" id="modalRestaurar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cloud-upload-alt"></i> Restaurar copia de seguridad</h5>
                <button type="button" class="btn-close btn-close-white close text-white" data-bs-dismiss="modal" data-dismiss="modal" style="opacity:1">×</button>
            </div>
            <form method="POST" action="{{ route('backup.restaurar') }}" enctype="multipart/form-data" id="formRestaurar">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning border-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> esta operación sobrescribirá <u>todos</u> los datos actuales con el contenido del archivo. Te recomendamos <a href="{{ route('backup.descargar-directo') }}" class="alert-link">descargar una copia actual</a> antes de continuar.
                    </div>

                    <label class="fw-bold mb-1">Archivo de copia (.sql o .txt)</label>
                    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('archivoSql').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div><strong>Haz clic o arrastra aquí</strong> tu archivo de copia</div>
                        <small class="text-muted">Tamaño máximo: 50 MB</small>
                        <div class="mt-2" id="filenameDisplay" style="display:none">
                            <span class="badge bg-success"><i class="fas fa-file-code"></i> <span id="fileLabel"></span></span>
                        </div>
                    </div>
                    <input type="file" name="archivo" id="archivoSql" accept=".sql,.txt" style="display:none" onchange="mostrarArchivo(this)">

                    <hr class="my-3">
                    <label class="fw-bold mb-1">Confirmación</label>
                    <p class="small text-muted mb-2">Para activar el botón, escribe la palabra <strong>RESTAURAR</strong> exactamente:</p>
                    <input type="text" name="confirmacion" class="form-control input-confirma" autocomplete="off" placeholder="ESCRIBE RESTAURAR" oninput="validarRestaurar()">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnRestaurar" disabled><i class="fas fa-undo"></i> Restaurar ahora</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================== --}}
{{-- MODAL RESETEAR                                  --}}
{{-- ============================================== --}}
<div class="modal fade modal-peligro" id="modalReset" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Resetear sistema para empresa nueva</h5>
                <button type="button" class="btn-close btn-close-white close text-white" data-bs-dismiss="modal" data-dismiss="modal" style="opacity:1">×</button>
            </div>
            <form method="POST" action="{{ route('backup.resetear') }}" id="formReset">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger border-0">
                        <i class="fas fa-skull-crossbones"></i>
                        <strong>Acción IRREVERSIBLE.</strong> Se eliminarán todos los datos seleccionados de forma permanente.
                        Antes de continuar, <a href="{{ route('backup.descargar-directo') }}" class="alert-link">descarga una copia de seguridad</a>.
                    </div>

                    <h6 class="fw-bold mt-3"><i class="fas fa-trash"></i> Se borrarán siempre</h6>
                    <div class="row">
                        <div class="col-md-6"><div class="opcion-mantener" style="background:#ffe6e6">
                            <span class="it-ico" style="background:#dc3545; color:#fff"><i class="fas fa-times"></i></span>
                            <span><strong>Pedidos</strong><br><small class="text-muted">Histórico de ventas, detalles, pagos</small></span>
                        </div></div>
                        <div class="col-md-6"><div class="opcion-mantener" style="background:#ffe6e6">
                            <span class="it-ico" style="background:#dc3545; color:#fff"><i class="fas fa-times"></i></span>
                            <span><strong>Cajas</strong><br><small class="text-muted">Aperturas, cierres, movimientos</small></span>
                        </div></div>
                    </div>

                    <h6 class="fw-bold mt-3"><i class="fas fa-shield-alt"></i> Selecciona qué quieres conservar</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="opcion-mantener checked">
                                <input type="checkbox" name="mantener_usuarios" value="1" checked onchange="this.parentElement.classList.toggle('checked', this.checked)">
                                <span class="it-ico"><i class="fas fa-user-shield"></i></span>
                                <span><strong>Usuarios</strong><br><small class="text-muted">admin@tpv.local nunca se borra</small></span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="opcion-mantener">
                                <input type="checkbox" name="mantener_config" value="1" onchange="this.parentElement.classList.toggle('checked', this.checked)">
                                <span class="it-ico"><i class="fas fa-cog"></i></span>
                                <span><strong>Configuración empresa</strong><br><small class="text-muted">Logo, datos, moneda, IVA</small></span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="opcion-mantener">
                                <input type="checkbox" name="mantener_catalogo" value="1" onchange="this.parentElement.classList.toggle('checked', this.checked)">
                                <span class="it-ico"><i class="fas fa-pizza-slice"></i></span>
                                <span><strong>Catálogo</strong><br><small class="text-muted">Productos y categorías</small></span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="opcion-mantener">
                                <input type="checkbox" name="mantener_mesas" value="1" onchange="this.parentElement.classList.toggle('checked', this.checked)">
                                <span class="it-ico"><i class="fas fa-utensils"></i></span>
                                <span><strong>Mesas y zonas</strong><br><small class="text-muted">Distribución del local</small></span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="opcion-mantener">
                                <input type="checkbox" name="mantener_clientes" value="1" onchange="this.parentElement.classList.toggle('checked', this.checked)">
                                <span class="it-ico"><i class="fas fa-users"></i></span>
                                <span><strong>Clientes</strong><br><small class="text-muted">Base de datos del CRM</small></span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-3">
                    <label class="fw-bold mb-1">Confirmación</label>
                    <p class="small text-muted mb-2">Para activar el botón, escribe la palabra <strong style="color:#dc3545">RESETEAR</strong> exactamente:</p>
                    <input type="text" name="confirmacion" class="form-control input-confirma" autocomplete="off" placeholder="ESCRIBE RESETEAR" oninput="validarReset()">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnReset" disabled><i class="fas fa-power-off"></i> Resetear el sistema</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================== --}}
{{-- MODAL RESTAURAR DESDE COPIA GUARDADA           --}}
{{-- ============================================== --}}
<div class="modal fade modal-restaurar" id="modalRestaurarGuardado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-undo"></i> Restaurar desde copia guardada</h5>
                <button type="button" class="btn-close btn-close-white close text-white" data-bs-dismiss="modal" data-dismiss="modal" style="opacity:1">×</button>
            </div>
            <form method="POST" id="formRestaurarGuardado">
                @csrf
                <div class="modal-body">
                    <p>Vas a restaurar el sistema desde la copia: <strong id="archivoGuardadoLabel"></strong></p>
                    <div class="alert alert-warning border-0"><i class="fas fa-exclamation-triangle"></i> Sobrescribirá todos los datos actuales.</div>
                    <label class="fw-bold mb-1">Confirmación</label>
                    <input type="text" name="confirmacion" class="form-control input-confirma" autocomplete="off" placeholder="ESCRIBE RESTAURAR" oninput="validarRestaurarGuardado()">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnRestaurarGuardado" disabled><i class="fas fa-undo"></i> Restaurar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function mostrarArchivo(input){
    if(input.files && input.files[0]){
        document.getElementById('fileLabel').textContent = input.files[0].name + ' (' + (input.files[0].size/1024/1024).toFixed(2) + ' MB)';
        document.getElementById('filenameDisplay').style.display = 'block';
    }
    validarRestaurar();
}

function validarRestaurar(){
    const conf = document.querySelector('#formRestaurar input[name=confirmacion]').value.trim();
    const file = document.getElementById('archivoSql').files.length > 0;
    document.getElementById('btnRestaurar').disabled = !(conf === 'RESTAURAR' && file);
}

function validarReset(){
    const conf = document.querySelector('#formReset input[name=confirmacion]').value.trim();
    document.getElementById('btnReset').disabled = (conf !== 'RESETEAR');
}

function abrirRestaurarGuardado(filename){
    document.getElementById('archivoGuardadoLabel').textContent = filename;
    document.getElementById('formRestaurarGuardado').action = '{{ url("backup/restaurar") }}/' + filename;
    document.querySelector('#formRestaurarGuardado input[name=confirmacion]').value = '';
    document.getElementById('btnRestaurarGuardado').disabled = true;
    if (typeof bootstrap !== 'undefined') new bootstrap.Modal('#modalRestaurarGuardado').show();
    else $('#modalRestaurarGuardado').modal('show');
}

function validarRestaurarGuardado(){
    const conf = document.querySelector('#formRestaurarGuardado input[name=confirmacion]').value.trim();
    document.getElementById('btnRestaurarGuardado').disabled = (conf !== 'RESTAURAR');
}

// Drag & drop
const zone = document.getElementById('uploadZone');
if (zone){
    ['dragenter','dragover'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();zone.classList.add('dragging');}));
    ['dragleave','drop'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();zone.classList.remove('dragging');}));
    zone.addEventListener('drop',e=>{
        if(e.dataTransfer.files.length){
            document.getElementById('archivoSql').files = e.dataTransfer.files;
            mostrarArchivo(document.getElementById('archivoSql'));
        }
    });
}

// Confirmación visual al guardar
document.getElementById('formReset')?.addEventListener('submit', e=>{
    const btn = document.getElementById('btnReset');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reseteando sistema...';
    btn.disabled = true;
});
document.getElementById('formRestaurar')?.addEventListener('submit', e=>{
    const btn = document.getElementById('btnRestaurar');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restaurando...';
    btn.disabled = true;
});
</script>
@endsection
