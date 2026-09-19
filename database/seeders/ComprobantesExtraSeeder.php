<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\ComprobanteElectronico;
use App\Models\ComprobanteLinea;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * ComprobantesExtraSeeder
 * -------------------------------------------------------------
 * Añade SOLO los 10 comprobantes electrónicos extra (boletas,
 * facturas y nota de crédito) en fechas variadas. Pensado para
 * ejecutarse cuando el módulo SUNAT/Perú ya está migrado pero
 * el DatosExtraSeeder se saltó esta parte.
 *
 * Pre-requisitos:
 *   - Migración 2025_01_01_000020_add_peru_sunat_fields aplicada
 *
 * Uso:
 *   php artisan migrate
 *   php artisan db:seed --class=ComprobantesExtraSeeder
 * -------------------------------------------------------------
 */
class ComprobantesExtraSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Validar que la migración SUNAT esté aplicada
        if (!Schema::hasTable('comprobantes_electronicos')
            || !Schema::hasColumn('configuracion', 'facturacion_electronica_pe')) {
            $this->command->error('✗ La migración SUNAT no está aplicada.');
            $this->command->warn('   Ejecuta primero:  php artisan migrate');
            return;
        }

        $config = Configuracion::actual();

        // 2) Activar facturación electrónica si no lo está aún
        if (!$config->facturacion_electronica_pe) {
            $config->update([
                'facturacion_electronica_pe' => true,
                'ruc'                        => $config->ruc ?: '20123456789',
                'igv_porcentaje'             => 18.00,
                'serie_factura_pe'           => $config->serie_factura_pe ?: 'F001',
                'serie_boleta_pe'            => $config->serie_boleta_pe  ?: 'B001',
            ]);
        }

        $clientes  = Cliente::orderBy('id')->take(20)->get();
        $usuarios  = User::where('activo', true)->pluck('id')->toArray();
        $productos = Producto::where('activo', true)->take(20)->get();

        if ($clientes->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('✗ Faltan clientes o productos para crear comprobantes.');
            return;
        }

        // 3) Definir los 10 comprobantes en fechas variadas
        $comps = [
            ['03', 'B001', 'aceptado',  Carbon::today()],
            ['03', 'B001', 'aceptado',  Carbon::today()->subDay()],
            ['01', 'F001', 'aceptado',  Carbon::today()->subDays(2)],
            ['03', 'B001', 'enviado',   Carbon::today()->subDays(5)],
            ['01', 'F001', 'aceptado',  Carbon::today()->subDays(8)],
            ['03', 'B001', 'aceptado',  Carbon::today()->subDays(14)],
            ['01', 'F001', 'observado', Carbon::today()->subDays(20)],
            ['03', 'B001', 'aceptado',  Carbon::today()->subDays(28)],
            ['03', 'B001', 'rechazado', Carbon::today()->subDays(40)],
            ['07', 'FC01', 'aceptado',  Carbon::today()->subDays(50)],
        ];

        $numFacturaPe = (int) ($config->proximo_factura_pe ?: 1);
        $numBoletaPe  = (int) ($config->proximo_boleta_pe  ?: 1);
        $numNC        = 1;
        $creados      = 0;

        foreach ($comps as $i => $cdef) {
            [$tipo, $serie, $estado, $fecha] = $cdef;
            $cli = $clientes[$i % $clientes->count()];

            if ($tipo === '01') {
                $numero          = $numFacturaPe++;
                $tipoDocReceptor = '6';
                $numDoc          = '20' . str_pad((string)rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
                $razon           = $cli->tipo === 'empresa'
                    ? $cli->nombre
                    : 'EMPRESA ' . strtoupper($cli->nombre) . ' SAC';
            } elseif ($tipo === '07') {
                $numero          = $numNC++;
                $tipoDocReceptor = '1';
                $numDoc          = (string) rand(10000000, 99999999);
                $razon           = trim($cli->nombre . ' ' . $cli->apellidos);
            } else {
                $numero          = $numBoletaPe++;
                $tipoDocReceptor = '1';
                $numDoc          = (string) rand(10000000, 99999999);
                $razon           = trim($cli->nombre . ' ' . $cli->apellidos);
            }

            $numeroCompleto = $serie . '-' . str_pad((string)$numero, 8, '0', STR_PAD_LEFT);

            // Si ya existe, lo saltamos (idempotente)
            if (ComprobanteElectronico::where('numero_completo', $numeroCompleto)->exists()) {
                continue;
            }

            // 1-3 líneas por comprobante
            $numLineas = rand(1, 3);
            $totalGrav = 0.0;
            $totalIgv  = 0.0;
            $total     = 0.0;
            $lineasData = [];
            for ($l = 0; $l < $numLineas; $l++) {
                $prod   = $productos->random();
                $cant   = rand(1, 4);
                $precio = (float) $prod->precio;
                $sub    = $precio / 1.18;
                $igv    = $precio - $sub;
                $valTot = round($sub * $cant, 2);
                $igvTot = round($igv * $cant, 2);
                $tot    = round($precio * $cant, 2);

                $totalGrav += $valTot;
                $totalIgv  += $igvTot;
                $total     += $tot;

                $lineasData[] = [
                    'producto_id'         => $prod->id,
                    'orden'               => $l + 1,
                    'codigo_producto'     => $prod->codigo ?: 'P-' . $prod->id,
                    'descripcion'         => $prod->nombre,
                    'unidad_medida'       => 'NIU',
                    'cantidad'            => $cant,
                    'valor_unitario'      => round($sub, 4),
                    'precio_unitario'     => round($precio, 4),
                    'descuento'           => 0,
                    'tipo_afectacion_igv' => '10',
                    'igv'                 => $igvTot,
                    'valor_total'         => $valTot,
                    'total'               => $tot,
                ];
            }

            $comp = ComprobanteElectronico::create([
                'pedido_id'             => null,
                'cliente_id'            => $cli->id,
                'user_id'               => $usuarios[array_rand($usuarios)],
                'tipo'                  => $tipo,
                'serie'                 => $serie,
                'numero'                => $numero,
                'numero_completo'       => $numeroCompleto,
                'fecha_emision'         => $fecha->toDateString(),
                'hora_emision'          => '13:' . str_pad((string)rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00',
                'fecha_vencimiento'     => $fecha->copy()->addDays(15)->toDateString(),
                'tipo_doc_receptor'     => $tipoDocReceptor,
                'num_doc_receptor'      => $numDoc,
                'razon_social_receptor' => $razon,
                'direccion_receptor'    => $cli->direccion,
                'email_receptor'        => $cli->email,
                'moneda'                => 'PEN',
                'total_gravado'         => round($totalGrav, 2),
                'igv'                   => round($totalIgv, 2),
                'total'                 => round($total, 2),
                'total_letras'          => 'SON ' . strtoupper(number_format($total, 2, ' CON ', '.')) . ' SOLES',
                'estado'                => $estado,
                'codigo_sunat'          => $estado === 'aceptado' ? '0' : ($estado === 'rechazado' ? '2335' : null),
                'mensaje_sunat'         => $estado === 'aceptado'
                    ? 'La Factura ha sido aceptada'
                    : ($estado === 'observado' ? 'Aceptado con observación' : null),
                'intentos_envio'        => in_array($estado, ['aceptado','observado','enviado','rechazado'], true) ? 1 : 0,
                'enviado_at'            => in_array($estado, ['aceptado','observado','enviado','rechazado'], true) ? $fecha : null,
                'aceptado_at'           => in_array($estado, ['aceptado','observado'], true) ? $fecha : null,
                'created_at'            => $fecha,
                'updated_at'            => $fecha,
            ]);

            foreach ($lineasData as $linea) {
                $linea['comprobante_id'] = $comp->id;
                $linea['created_at']     = $fecha;
                $linea['updated_at']     = $fecha;
                ComprobanteLinea::create($linea);
            }

            $creados++;
        }

        $config->update([
            'proximo_factura_pe' => $numFacturaPe,
            'proximo_boleta_pe'  => $numBoletaPe,
        ]);

        $this->command->info("✓ {$creados} comprobantes electrónicos añadidos.");
    }
}
