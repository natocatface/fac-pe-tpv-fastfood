<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $guarded = ['id'];

    protected $casts = [
        'fecha_pedido'    => 'datetime',
        'fecha_entrega'   => 'datetime',
        'fecha_cobro'     => 'datetime',
        'factura_emitida' => 'boolean',
    ];

    public const ESTADOS = [
        'borrador'        => 'Borrador',
        'pendiente'       => 'Pendiente',
        'en_preparacion'  => 'En preparación',
        'preparado'       => 'Preparado',
        'en_camino'       => 'En camino',
        'entregado'       => 'Entregado',
        'cobrado'         => 'Cobrado',
        'anulado'         => 'Anulado',
    ];

    public const TIPOS = [
        'mesa'        => 'Mesa',
        'mostrador'   => 'Mostrador',
        'domicilio'   => 'A domicilio',
        'recogida'    => 'Recogida en local',
        'telefono'    => 'Pedido telefónico',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function pagos()
    {
        return $this->hasMany(PedidoPago::class);
    }

    public function colorEstado(): string
    {
        return match ($this->estado) {
            'borrador'       => 'secondary',
            'pendiente'      => 'warning',
            'en_preparacion' => 'info',
            'preparado'      => 'primary',
            'en_camino'      => 'info',
            'entregado'      => 'success',
            'cobrado'        => 'success',
            'anulado'        => 'danger',
            default          => 'light',
        };
    }

    public function totalPagado(): float
    {
        return (float) $this->pagos()->sum('importe');
    }

    public function pendientePago(): float
    {
        return (float) $this->total - $this->totalPagado();
    }

    public function recalcularTotales(): void
    {
        $this->subtotal  = (float) $this->detalles()->sum('subtotal');
        $this->total_iva = (float) $this->detalles()->sum(\DB::raw('total - subtotal'));
        $this->total     = $this->subtotal + $this->total_iva + $this->coste_envio - $this->descuento;
        $this->save();
    }
}
