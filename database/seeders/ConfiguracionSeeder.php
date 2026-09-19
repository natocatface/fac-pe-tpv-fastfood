<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        Configuracion::updateOrCreate(['id' => 1], [
            'nombre_empresa'   => 'Pizzería La Italiana',
            'razon_social'     => 'La Italiana SL',
            'cif_nif'          => 'B12345678',
            'direccion'        => 'Calle Mayor 25',
            'codigo_postal'    => '28001',
            'ciudad'           => 'Madrid',
            'provincia'        => 'Madrid',
            'pais'             => 'España',
            'telefono'         => '912 345 678',
            'movil'            => '600 123 456',
            'email'            => 'pedidos@laitaliana.com',
            'web'              => 'https://laitaliana.com',
            'moneda_codigo'    => 'EUR',
            'moneda_simbolo'   => '€',
            'moneda_posicion'  => 'derecha',
            'decimales'        => 2,
            'separador_decimal'=> ',',
            'separador_miles'  => '.',
            'iva_general'      => 10.00,
            'iva_reducido'     => 4.00,
            'precios_con_iva'  => true,
            'serie_ticket'     => 'T',
            'serie_factura'    => 'F',
            'serie_pedido'     => 'P',
            'proximo_ticket'   => 1,
            'proximo_pedido'   => 1,
            'proximo_factura'  => 1,
            'texto_ticket_cabecera' => "¡Bienvenido a Pizzería La Italiana!\nLa mejor pizza de Madrid",
            'texto_ticket_pie' => "Gracias por su visita\nSíganos en @laitalianamadrid",
            'imprimir_logo_ticket' => true,
            'ancho_ticket_mm'  => 80,
            'modulo_domicilio' => true,
            'modulo_mesas'     => true,
            'modulo_recogida'  => true,
            'modulo_telefono'  => true,
            'coste_envio'      => 2.50,
            'pedido_minimo_envio' => 12.00,
            'hora_apertura'    => '12:00',
            'hora_cierre'      => '23:30',
            'color_primario'   => '#28a745',
            'color_secundario' => '#343a40',
            'tema'             => 'claro',
            'alerta_stock_bajo' => true,
            'umbral_stock_bajo' => 5,
            'programa_puntos'  => true,
            'puntos_por_euro'  => 1,
            'valor_punto'      => 0.01,
        ]);
    }
}
