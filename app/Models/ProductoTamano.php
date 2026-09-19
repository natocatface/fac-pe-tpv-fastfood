<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoTamano extends Model
{
    protected $table = 'producto_tamanos';
    protected $guarded = ['id'];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
