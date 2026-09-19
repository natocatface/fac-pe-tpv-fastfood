<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoExtra extends Model
{
    protected $table = 'producto_extras';
    protected $guarded = ['id'];

    protected $casts = [
        'obligatorio' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
