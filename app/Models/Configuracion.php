<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $guarded = ['id'];

    protected $casts = [
        'precios_con_iva'      => 'boolean',
        'imprimir_logo_ticket' => 'boolean',
        'modulo_domicilio'     => 'boolean',
        'modulo_mesas'         => 'boolean',
        'modulo_recogida'      => 'boolean',
        'modulo_telefono'      => 'boolean',
        'email_pedidos'        => 'boolean',
        'alerta_stock_bajo'    => 'boolean',
        'programa_puntos'      => 'boolean',
        'iva_general'          => 'decimal:2',
        'iva_reducido'         => 'decimal:2',
        'coste_envio'          => 'decimal:2',
        'pedido_minimo_envio'  => 'decimal:2',
        'puntos_por_euro'      => 'decimal:2',
        'valor_punto'          => 'decimal:4',
        'hora_apertura'        => 'datetime:H:i',
        'hora_cierre'          => 'datetime:H:i',
    ];

    public static function actual(): self
    {
        return Cache::remember('configuracion.actual', 600, function () {
            return self::firstOrCreate(['id' => 1]);
        });
    }

    public static function refrescar(): void
    {
        Cache::forget('configuracion.actual');
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::refrescar());
    }

    public function formatearPrecio(float $precio): string
    {
        $numero = number_format(
            $precio,
            $this->decimales,
            $this->separador_decimal,
            $this->separador_miles
        );

        return $this->moneda_posicion === 'izquierda'
            ? "{$this->moneda_simbolo} {$numero}"
            : "{$numero} {$this->moneda_simbolo}";
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }
}
