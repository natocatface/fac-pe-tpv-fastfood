<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $guarded = ['id'];

    protected $casts = [
        'preferencias'      => 'array',
        'fecha_nacimiento'  => 'date',
        'ultimo_pedido_at'  => 'datetime',
        'acepta_marketing'  => 'boolean',
        'activo'            => 'boolean',
    ];

    public function direcciones()
    {
        return $this->hasMany(ClienteDireccion::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellidos);
    }

    public function cumpleHoy(): bool
    {
        return $this->fecha_nacimiento
            && $this->fecha_nacimiento->format('m-d') === now()->format('m-d');
    }
}
