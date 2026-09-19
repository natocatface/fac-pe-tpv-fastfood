<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Producto extends Model
{
    protected $table = 'productos';

    protected $guarded = ['id'];

    protected $casts = [
        'imagenes_extra'        => 'array',
        'controla_stock'        => 'boolean',
        'es_vegetariano'        => 'boolean',
        'es_vegano'             => 'boolean',
        'es_sin_gluten'         => 'boolean',
        'picante'               => 'boolean',
        'disponible_local'      => 'boolean',
        'disponible_domicilio'  => 'boolean',
        'disponible_recogida'   => 'boolean',
        'destacado'             => 'boolean',
        'activo'                => 'boolean',
        'oferta_inicio'         => 'date',
        'oferta_fin'            => 'date',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function extras()
    {
        return $this->hasMany(ProductoExtra::class)->orderBy('orden');
    }

    public function tamanos()
    {
        return $this->hasMany(ProductoTamano::class)->orderBy('orden');
    }

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function imagenUrl(): string
    {
        if ($this->imagen) {
            return asset('storage/'.$this->imagen);
        }
        // Sin imagen: devolvemos un SVG inline como data URI para evitar
        // peticiones a archivos inexistentes (que provocaban parpadeo al
        // dispararse el onerror hacia un placeholder externo descontinuado).
        $color = $this->categoria->color ?? '#cbd2da';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
             . '<rect width="64" height="64" rx="10" fill="#f1f3f5"/>'
             . '<circle cx="32" cy="32" r="14" fill="' . htmlspecialchars($color, ENT_QUOTES) . '" opacity="0.25"/>'
             . '<text x="32" y="38" text-anchor="middle" font-family="Arial,sans-serif" font-size="20" fill="' . htmlspecialchars($color, ENT_QUOTES) . '" opacity="0.7">?</text>'
             . '</svg>';
        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
    }

    public function precioActual(): float
    {
        if ($this->precio_oferta) {
            $hoy = Carbon::today();
            $inicio = $this->oferta_inicio ?: $hoy;
            $fin = $this->oferta_fin ?: $hoy;
            if ($hoy->between($inicio, $fin)) {
                return (float) $this->precio_oferta;
            }
        }
        return (float) $this->precio;
    }

    public function tieneStock(): bool
    {
        return ! $this->controla_stock || $this->stock > 0;
    }

    public function stockBajo(): bool
    {
        return $this->controla_stock && $this->stock <= $this->stock_minimo;
    }

    protected static function booted(): void
    {
        static::saving(function (self $p) {
            if (! $p->slug) {
                $p->slug = Str::slug($p->nombre).'-'.Str::random(4);
            }
        });
    }

    public function scopeActivos($q)
    {
        return $q->where('activo', true);
    }

    public function scopeDestacados($q)
    {
        return $q->where('destacado', true)->where('activo', true);
    }
}
