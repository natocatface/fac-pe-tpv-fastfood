<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function edit()
    {
        $config = Configuracion::actual();
        return view('configuracion.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $config = Configuracion::actual();

        $datos = $request->validate([
            'nombre_empresa'    => 'required|string|max:150',
            'razon_social'      => 'nullable|string|max:200',
            'cif_nif'           => 'nullable|string|max:30',
            'direccion'         => 'nullable|string|max:250',
            'codigo_postal'     => 'nullable|string|max:15',
            'ciudad'            => 'nullable|string|max:100',
            'provincia'         => 'nullable|string|max:100',
            'pais'              => 'nullable|string|max:80',
            'telefono'          => 'nullable|string|max:30',
            'movil'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:150',
            'web'               => 'nullable|url|max:150',
            'logo'              => 'nullable|image|max:2048',
            'favicon'           => 'nullable|image|max:512',

            'moneda_codigo'     => 'required|string|max:5',
            'moneda_simbolo'    => 'required|string|max:5',
            'moneda_posicion'   => 'required|in:izquierda,derecha',
            'decimales'         => 'required|integer|min:0|max:4',
            'separador_decimal' => 'required|string|size:1',
            'separador_miles'   => 'required|string|size:1',
            'iva_general'       => 'required|numeric|min:0|max:100',
            'iva_reducido'      => 'required|numeric|min:0|max:100',
            'precios_con_iva'   => 'nullable|boolean',

            'serie_ticket'      => 'required|string|max:10',
            'serie_factura'     => 'required|string|max:10',
            'serie_pedido'      => 'required|string|max:10',
            'texto_ticket_cabecera' => 'nullable|string',
            'texto_ticket_pie'      => 'nullable|string',
            'imprimir_logo_ticket'  => 'nullable|boolean',
            'ancho_ticket_mm'   => 'required|integer|in:58,80',

            'modulo_domicilio'  => 'nullable|boolean',
            'modulo_mesas'      => 'nullable|boolean',
            'modulo_recogida'   => 'nullable|boolean',
            'modulo_telefono'   => 'nullable|boolean',
            'coste_envio'       => 'nullable|numeric|min:0',
            'pedido_minimo_envio' => 'nullable|numeric|min:0',
            'hora_apertura'     => 'required|date_format:H:i',
            'hora_cierre'       => 'required|date_format:H:i',

            'color_primario'    => 'required|string|max:7',
            'color_secundario'  => 'required|string|max:7',
            'tema'              => 'required|in:claro,oscuro,auto',

            'email_pedidos'         => 'nullable|boolean',
            'email_notificaciones'  => 'nullable|email|max:150',
            'alerta_stock_bajo'     => 'nullable|boolean',
            'umbral_stock_bajo'     => 'nullable|integer|min:0',

            'programa_puntos'   => 'nullable|boolean',
            'puntos_por_euro'   => 'nullable|numeric|min:0',
            'valor_punto'       => 'nullable|numeric|min:0',

            // SUNAT / Perú
            'facturacion_electronica_pe' => 'nullable|boolean',
            'ruc'                  => 'nullable|string|max:11',
            'ubigeo'               => 'nullable|string|max:6',
            'urbanizacion'         => 'nullable|string|max:100',
            'distrito'             => 'nullable|string|max:80',
            'departamento'         => 'nullable|string|max:80',
            'usuario_sol'          => 'nullable|string|max:50',
            'clave_sol'            => 'nullable|string|max:100',
            'sunat_modo'           => 'nullable|in:beta,produccion',
            'certificado_archivo'  => 'nullable|file|max:2048',
            'certificado_password' => 'nullable|string|max:100',
            'serie_factura_pe'     => 'nullable|string|max:10',
            'serie_boleta_pe'      => 'nullable|string|max:10',
            'proximo_factura_pe'   => 'nullable|integer|min:1',
            'proximo_boleta_pe'    => 'nullable|integer|min:1',
            'igv_porcentaje'       => 'nullable|numeric|min:0|max:100',
        ]);

        // Booleanos no enviados llegan como null -> false
        foreach ([
            'precios_con_iva','imprimir_logo_ticket','modulo_domicilio',
            'modulo_mesas','modulo_recogida','modulo_telefono',
            'email_pedidos','alerta_stock_bajo','programa_puntos',
            'facturacion_electronica_pe',
        ] as $b) {
            $datos[$b] = (bool) $request->input($b);
        }

        // Certificado digital SUNAT
        if ($request->hasFile('certificado_archivo')) {
            $path = $request->file('certificado_archivo')->store('sunat/certificados', 'local');
            $datos['certificado_path'] = $path;
        }
        unset($datos['certificado_archivo']);

        // Logo
        if ($request->hasFile('logo')) {
            if ($config->logo && Storage::disk('public')->exists($config->logo)) {
                Storage::disk('public')->delete($config->logo);
            }
            $datos['logo'] = $request->file('logo')->store('empresa', 'public');
        } else {
            unset($datos['logo']);
        }

        if ($request->hasFile('favicon')) {
            if ($config->favicon && Storage::disk('public')->exists($config->favicon)) {
                Storage::disk('public')->delete($config->favicon);
            }
            $datos['favicon'] = $request->file('favicon')->store('empresa', 'public');
        } else {
            unset($datos['favicon']);
        }

        $config->update($datos);

        return redirect()->route('configuracion.edit')
            ->with('success', 'Configuración actualizada correctamente.');
    }
}
