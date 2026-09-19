<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $guarded = ['id'];

    protected $casts = [
        'activa'       => 'boolean',
        'mostrar_tpv'  => 'boolean',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function productosActivos()
    {
        return $this->hasMany(Producto::class)->where('activo', true);
    }

    protected static function booted(): void
    {
        static::saving(function (self $cat) {
            if (! $cat->slug) {
                $cat->slug = Str::slug($cat->nombre);
            }
        });
    }

    public function imagenUrl(): ?string
    {
        return $this->imagen ? asset('storage/'.$this->imagen) : null;
    }

    public function scopeActivas($q)
    {
        return $q->where('activa', true)->orderBy('orden');
    }
}
