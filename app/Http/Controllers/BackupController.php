<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct(private BackupService $service) {}

    /** Página principal del módulo. */
    public function index()
    {
        $backups = $this->service->listarBackups();
        $stats   = $this->service->estadisticas();
        $service = $this->service;
        return view('backup.index', compact('backups', 'stats', 'service'));
    }

    /** Crea una copia y la guarda en disco. */
    public function crear()
    {
        try {
            $info = $this->service->guardarBackup();
            return back()->with('success', "Copia de seguridad creada: {$info['filename']} ({$this->service->formatearTamano($info['size'])})");
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo crear la copia: ' . $e->getMessage());
        }
    }

    /** Descarga directamente sin guardar en disco. */
    public function descargarDirecto()
    {
        $sql = $this->service->generarSQL();
        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
        return response($sql)
            ->header('Content-Type', 'application/sql; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /** Descarga un backup guardado en disco. */
    public function descargar(string $filename)
    {
        $this->validarFilename($filename);
        $path = storage_path('app/private/' . BackupService::DIR . '/' . $filename);
        if (!file_exists($path)) {
            $path = storage_path('app/' . BackupService::DIR . '/' . $filename);
        }
        if (!file_exists($path)) {
            abort(404, 'Copia no encontrada.');
        }
        return response()->download($path);
    }

    /** Elimina un backup. */
    public function eliminar(string $filename)
    {
        $this->validarFilename($filename);
        $disk = Storage::disk('local');
        $disk->delete(BackupService::DIR . '/' . $filename);
        return back()->with('success', 'Copia eliminada.');
    }

    /** Restaura desde un archivo SQL subido. */
    public function restaurar(Request $request)
    {
        $request->validate([
            'archivo'      => 'required|file|max:51200', // 50 MB
            'confirmacion' => 'required|in:RESTAURAR',
        ], [
            'confirmacion.in' => 'Debes escribir RESTAURAR exactamente para confirmar.',
        ]);

        $file = $request->file('archivo');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['sql', 'txt'])) {
            return back()->with('error', 'El archivo debe ser .sql o .txt');
        }

        try {
            $sql = file_get_contents($file->getRealPath());
            $r = $this->service->restaurar($sql);

            $msg = "✓ Restauración completada en {$r['duracion']}s. " .
                   "Sentencias OK: {$r['exitos']}";
            if ($r['errores'] > 0) {
                $msg .= ", errores: {$r['errores']}.";
            }
            if (!empty($r['mensajes'])) {
                $msg .= ' Primer error: ' . $r['mensajes'][0];
            }
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    /** Restaura desde un backup guardado en disco. */
    public function restaurarGuardado(Request $request, string $filename)
    {
        $this->validarFilename($filename);
        $request->validate([
            'confirmacion' => 'required|in:RESTAURAR',
        ]);

        $disk = Storage::disk('local');
        $path = BackupService::DIR . '/' . $filename;
        if (!$disk->exists($path)) {
            return back()->with('error', 'Copia no encontrada.');
        }

        try {
            $sql = $disk->get($path);
            $r = $this->service->restaurar($sql);
            return back()->with('success', "✓ Restaurado desde {$filename}. Sentencias: {$r['exitos']}, errores: {$r['errores']}, duración {$r['duracion']}s.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    /** Resetea el sistema para una empresa nueva. */
    public function resetear(Request $request)
    {
        $request->validate([
            'confirmacion'        => 'required|in:RESETEAR',
            'mantener_usuarios'   => 'nullable',
            'mantener_config'     => 'nullable',
            'mantener_catalogo'   => 'nullable',
            'mantener_mesas'      => 'nullable',
            'mantener_clientes'   => 'nullable',
        ], [
            'confirmacion.in' => 'Debes escribir RESETEAR exactamente para confirmar.',
        ]);

        try {
            $r = $this->service->resetear([
                'mantener_usuarios' => $request->boolean('mantener_usuarios'),
                'mantener_config'   => $request->boolean('mantener_config'),
                'mantener_catalogo' => $request->boolean('mantener_catalogo'),
                'mantener_mesas'    => $request->boolean('mantener_mesas'),
                'mantener_clientes' => $request->boolean('mantener_clientes'),
            ]);

            // Si se reseteó la configuración, redirige a configuración
            if (!$request->boolean('mantener_config')) {
                return redirect()->route('configuracion.edit')
                    ->with('success', "Sistema reseteado correctamente. Se eliminaron {$r['total']} registros. Configura ahora los datos de tu nueva empresa.");
            }

            return redirect()->route('backup.index')
                ->with('success', "Sistema reseteado. Se eliminaron {$r['total']} registros.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al resetear: ' . $e->getMessage());
        }
    }

    private function validarFilename(string $filename): void
    {
        if (preg_match('#[\\\\/\.\.]#', $filename) || !str_ends_with($filename, '.sql')) {
            abort(400, 'Nombre de archivo inválido.');
        }
    }
}
