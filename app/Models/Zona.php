<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zonas';
    protected $guarded = ['id'];

    protected $casts = ['activa' => 'boolean'];

    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }
}
