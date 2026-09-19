<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoPago extends Model
{
    protected $table = 'pedido_pagos';
    protected $guarded = ['id'];

    protected $casts = ['fecha' => 'datetime'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }
}
