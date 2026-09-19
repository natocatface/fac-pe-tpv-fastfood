<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $table = 'mesas';
    protected $guarded = ['id'];

    protected $casts = ['activa' => 'boolean'];

    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function pedidoActivo()
    {
        return $this->hasOne(Pedido::class)
            ->whereNotIn('estado', ['cobrado', 'anulado'])
            ->latestOfMany();
    }
}
