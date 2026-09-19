<?php

namespace App\Http\Controllers;

use App\Models\ComprobanteElectronico;
use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\Sunat\ComprobantePdfService;
use App\Services\Sunat\FacturacionElectronicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComprobanteElectronicoController extends Controller
{
    public function __construct(
        private FacturacionElectronicaService $service,
        private ComprobantePdfService $pdfService,
    ) {}

    public function index(Request $request)
    {
        $q = ComprobanteElectronico::with(['cliente', 'pedido']);

        if ($request->filled('tipo'))      $q->where('tipo', $request->tipo);
        if ($request->filled('estado'))    $q->where('estado', $request->estado);
        if ($buscar = $request->buscar) {
            $q->where(function ($w) use ($buscar) {
                $w->where('numero_completo', 'like', "%$buscar%")
                  ->orWhere('num_doc_receptor', 'like', "%$buscar%")
                  ->orWhere('razon_social_receptor', 'like', "%$buscar%");
            });
        }
        if ($request->filled('desde'))     $q->whereDate('fecha_emision', '>=', $request->desde);
        if ($request->filled('hasta'))     $q->whereDate('fecha_emision', '<=', $request->hasta);

        $comprobantes = $q->orderByDesc('fecha_emision')
                          ->orderByDesc('id')
                          ->paginate(20)->withQueryString();

        // Estadísticas rápidas
        $stats = [
            'total'      => ComprobanteElectronico::count(),
            'aceptados'  => ComprobanteElectronico::where('estado', 'aceptado')->count(),
            'pendientes' => ComprobanteElectronico::whereIn('estado', ['borrador', 'generado', 'firmado', 'enviado'])->count(),
            'rechazados' => ComprobanteElectronico::whereIn('estado', ['rechazado', 'error'])->count(),
        ];

        return view('comprobantes.index', compact('comprobantes', 'stats'));
    }

    public function show(ComprobanteElectronico $comprobante)
    {
        $comprobante->load('lineas', 'cliente', 'pedido', 'user');
        return view('comprobantes.show', compact('comprobante'));
    }

    /** Emite un comprobante a partir de un pedido. */
    public function emitir(Request $request, Pedido $pedido)
    {
        $datos = $request->validate([
            'tipo'                  => 'required|in:01,03',
            'tipo_doc_receptor'     => 'required|in:0,1,4,6,7',
            'num_doc_receptor'      => 'required|string|max:20',
            'razon_social_receptor' => 'required|string|max:250',
            'direccion_receptor'    => 'nullable|string|max:300',
            'email_receptor'        => 'nullable|email|max:150',
        ]);

        // Validación cruzada tipo doc / longitud
        $errores = $this->validarDocumento($datos['tipo'], $datos['tipo_doc_receptor'], $datos['num_doc_receptor']);
        if ($errores) {
            return back()->with('error', $errores)->withInput();
        }

        try {
            $c = $this->service->emitirDesdePedido(
                $pedido,
                $datos['tipo'],
                $datos['tipo_doc_receptor'],
                $datos['num_doc_receptor'],
                $datos['razon_social_receptor'],
                $datos['direccion_receptor'] ?? null,
                $datos['email_receptor'] ?? null,
            );
            return redirect()->route('comprobantes.show', $c)
                ->with('success', "Comprobante {$c->numero_completo} creado. Pulsa 'Procesar y enviar a SUNAT'.");
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo emitir: ' . $e->getMessage());
        }
    }

    /** Procesa el comprobante: genera XML, firma y envía a SUNAT. */
    public function procesar(ComprobanteElectronico $comprobante)
    {
        try {
            $c = $this->service->procesar($comprobante);

            $msg = "Estado: " . ComprobanteElectronico::ESTADOS[$c->estado];
            if ($c->codigo_sunat) $msg .= " · SUNAT [{$c->codigo_sunat}] {$c->mensaje_sunat}";

            $level = $c->estado === 'aceptado' ? 'success' : ($c->estado === 'rechazado' || $c->estado === 'error' ? 'error' : 'success');
            return back()->with($level, $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    /** Descarga XML, XML firmado, CDR o PDF. */
    public function descargar(ComprobanteElectronico $comprobante, string $tipo)
    {
        $map = [
            'xml'        => $comprobante->xml_path,
            'firmado'    => $comprobante->xml_firmado_path,
            'cdr'        => $comprobante->cdr_path,
            'pdf'        => $comprobante->pdf_path,
        ];
        $ruta = $map[$tipo] ?? null;
        $disk = Storage::disk('local');
        if (!$ruta || !$disk->exists($ruta)) {
            abort(404, 'Archivo no disponible.');
        }
        // Usar el path real del disco (Laravel 11: storage/app/private).
        return response()->download($disk->path($ruta));
    }

    /** Devuelve el formulario inline para emitir desde un pedido. */
    public function formularioEmision(Pedido $pedido)
    {
        $pedido->load('cliente', 'detalles');
        return view('comprobantes.emitir', compact('pedido'));
    }

    /** Genera (o regenera) y devuelve el PDF del comprobante para descargar. */
    public function pdf(ComprobanteElectronico $comprobante, Request $request)
    {
        try {
            $disk = Storage::disk('local');

            // Regeneramos si:
            //   a) No hay pdf_path en BD
            //   b) El archivo cacheado no existe en disco
            //   c) Se pasa ?refresh=1 (forzar regeneración tras cambios en plantilla)
            //   d) El cache es anterior al último update del comprobante
            $forzar = $request->boolean('refresh');
            $cacheValido = $comprobante->pdf_path
                && $disk->exists($comprobante->pdf_path)
                && !$forzar
                && $disk->lastModified($comprobante->pdf_path) >= $comprobante->updated_at->timestamp;

            if (!$cacheValido) {
                $this->pdfService->generarYGuardar($comprobante);
                $comprobante->refresh();
            }

            // Resolver la ruta absoluta REAL del disco (no asumir storage/app/)
            // En Laravel 11 el disco "local" apunta a storage/app/private por defecto.
            $rutaAbsoluta = $disk->path($comprobante->pdf_path);

            // Salvaguarda: si por alguna razón el archivo no está en disco,
            // generamos el PDF al vuelo en memoria y lo devolvemos directamente.
            if (!is_file($rutaAbsoluta)) {
                $binario = $this->pdfService->generar($comprobante);
                return response($binario, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $comprobante->numero_completo . '.pdf"',
                ]);
            }

            return response()->file($rutaAbsoluta, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($comprobante->pdf_path) . '"',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error generando PDF de comprobante ' . $comprobante->id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Devolver un mensaje legible en la pestaña nueva en vez de redirigir silenciosamente.
            return response(
                '<!doctype html><html><head><meta charset="utf-8"><title>Error PDF</title></head>'
                . '<body style="font-family:system-ui,sans-serif;padding:2rem;max-width:720px;margin:auto">'
                . '<h2 style="color:#c8102e">No se pudo generar el PDF</h2>'
                . '<p><strong>Comprobante:</strong> ' . e($comprobante->numero_completo) . '</p>'
                . '<p><strong>Detalle:</strong></p>'
                . '<pre style="background:#f8f9fa;padding:1rem;border-radius:6px;white-space:pre-wrap">' . e($e->getMessage()) . '</pre>'
                . '<p><a href="javascript:history.back()">← Volver</a></p>'
                . '</body></html>',
                500,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }
    }

    /** Fuerza la regeneración del PDF (útil tras cambios visuales). */
    public function regenerarPdf(ComprobanteElectronico $comprobante)
    {
        try {
            $this->pdfService->generarYGuardar($comprobante);
            return back()->with('success', 'PDF regenerado correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo regenerar el PDF: ' . $e->getMessage());
        }
    }

    /** Anula un comprobante aceptado mediante Comunicación de Baja. */
    public function anular(Request $request, ComprobanteElectronico $comprobante)
    {
        $request->validate(['motivo' => 'required|string|max:250']);

        if (!in_array($comprobante->estado, ['aceptado', 'observado'])) {
            return back()->with('error', 'Solo se pueden anular comprobantes que han sido aceptados por SUNAT.');
        }

        try {
            $comprobante->update([
                'motivo_anulacion' => $request->motivo,
            ]);
            $result = $this->service->anularComprobante($comprobante);
            $msg = "Comunicación de Baja enviada. Ticket SUNAT: {$result['ticket']}";
            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    private function validarDocumento(string $tipoComp, string $tipoDoc, string $numDoc): ?string
    {
        // Factura SOLO con RUC
        if ($tipoComp === '01' && $tipoDoc !== '6') {
            return 'La factura sólo puede emitirse a un RUC (11 dígitos).';
        }
        if ($tipoDoc === '6' && !preg_match('/^[12]\d{10}$/', $numDoc)) {
            return 'RUC inválido (debe tener 11 dígitos y empezar por 1 o 2).';
        }
        if ($tipoDoc === '1' && !preg_match('/^\d{8}$/', $numDoc)) {
            return 'DNI inválido (8 dígitos).';
        }
        return null;
    }
}
