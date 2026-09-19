<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareConfiguracion
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $config = Configuracion::actual();
            View::share('config', $config);
        } catch (\Throwable $e) {
            // Antes de migrar, evita errores
            View::share('config', null);
        }

        return $next($request);
    }
}
