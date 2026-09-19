<?php

namespace App\Services;

use App\Models\Configuracion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    public const DIR = 'backups';

    /**
     * Genera el contenido SQL completo de la base de datos.
     * Implementación pura PHP (no depende de mysqldump).
     */
    public function generarSQL(): string
    {
        $dbName = config('database.connections.mysql.database');

        $sql  = "-- ============================================================\n";
        $sql .= "--   CRM TPV FastFood - Copia de seguridad\n";
        $sql .= "--   Fecha:       " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "--   Base datos:  " . $dbName . "\n";
        $sql .= "--   Servidor:    " . config('database.connections.mysql.host') . "\n";
        $sql .= "-- ============================================================\n\n";

        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET CHARACTER SET utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

        $tables = $this->listarTablas();

        foreach ($tables as $table) {
            $sql .= "-- ----------------------------------------\n";
            $sql .= "-- Tabla: `{$table}`\n";
            $sql .= "-- ----------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            // Estructura de la tabla
            $createRow = (array) DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $createSql = $createRow['Create Table'] ?? array_values($createRow)[1];
            $sql .= $createSql . ";\n\n";

            // Datos
            $rows = DB::select("SELECT * FROM `{$table}`");
            if (count($rows) > 0) {
                $columns = array_keys((array) $rows[0]);
                $colsList = '`' . implode('`,`', $columns) . '`';

                foreach ($rows as $row) {
                    $values = [];
                    foreach ((array) $row as $value) {
                        $values[] = $this->formatearValor($value);
                    }
                    $sql .= "INSERT INTO `{$table}` ({$colsList}) VALUES (" . implode(',', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        return $sql;
    }

    /**
     * Crea y guarda una copia de seguridad en disco.
     */
    public function guardarBackup(): array
    {
        $disk = Storage::disk('local');
        if (!$disk->exists(self::DIR)) {
            $disk->makeDirectory(self::DIR);
        }

        $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
        $sql = $this->generarSQL();
        $disk->put(self::DIR . '/' . $filename, $sql);

        return [
            'filename' => $filename,
            'size'     => strlen($sql),
            'path'     => self::DIR . '/' . $filename,
        ];
    }

    /**
     * Lista las copias guardadas, más recientes primero.
     */
    public function listarBackups(): array
    {
        $disk = Storage::disk('local');
        if (!$disk->exists(self::DIR)) {
            return [];
        }

        $files = $disk->files(self::DIR);
        $list = [];
        foreach ($files as $file) {
            if (!str_ends_with($file, '.sql')) continue;
            $list[] = [
                'filename' => basename($file),
                'size'     => $disk->size($file),
                'modified' => Carbon::createFromTimestamp($disk->lastModified($file)),
            ];
        }
        usort($list, fn ($a, $b) => $b['modified']->getTimestamp() - $a['modified']->getTimestamp());
        return $list;
    }

    /**
     * Restaura el sistema desde una cadena SQL.
     */
    public function restaurar(string $sqlContent): array
    {
        $inicio = microtime(true);
        $exitos = 0;
        $errores = 0;
        $mensajesError = [];

        DB::unprepared('SET FOREIGN_KEY_CHECKS = 0');

        $statements = $this->dividirSQL($sqlContent);

        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || str_starts_with($stmt, '--')) continue;
            // Quitar el ; final si existe
            $stmt = rtrim($stmt, ";\n\r\t ");
            if ($stmt === '') continue;

            try {
                DB::unprepared($stmt);
                $exitos++;
            } catch (\Throwable $e) {
                $errores++;
                if (count($mensajesError) < 5) {
                    $mensajesError[] = $e->getMessage();
                }
            }
        }

        DB::unprepared('SET FOREIGN_KEY_CHECKS = 1');

        return [
            'exitos'    => $exitos,
            'errores'   => $errores,
            'duracion'  => round(microtime(true) - $inicio, 2),
            'mensajes'  => $mensajesError,
        ];
    }

    /**
     * Resetea el sistema para una empresa nueva.
     */
    public function resetear(array $opciones = []): array
    {
        $mantenerUsuarios = $opciones['mantener_usuarios'] ?? true;
        $mantenerConfig   = $opciones['mantener_config']   ?? false;
        $mantenerCatalogo = $opciones['mantener_catalogo'] ?? false;
        $mantenerMesas    = $opciones['mantener_mesas']    ?? false;
        $mantenerClientes = $opciones['mantener_clientes'] ?? false;

        $tablasResumen = [];

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // 1. Pedidos y ventas (siempre se borran)
        foreach (['pedido_pagos', 'pedido_detalles', 'pedidos', 'caja_movimientos', 'cajas'] as $t) {
            $count = DB::table($t)->count();
            DB::table($t)->truncate();
            $tablasResumen[$t] = $count;
        }

        // 2. Clientes (opcional)
        if (!$mantenerClientes) {
            foreach (['cliente_direcciones', 'clientes'] as $t) {
                $count = DB::table($t)->count();
                DB::table($t)->truncate();
                $tablasResumen[$t] = $count;
            }
        }

        // 3. Mesas y zonas (opcional)
        if (!$mantenerMesas) {
            foreach (['mesas', 'zonas'] as $t) {
                $count = DB::table($t)->count();
                DB::table($t)->truncate();
                $tablasResumen[$t] = $count;
            }
        }

        // 4. Catálogo: productos y categorías (opcional)
        if (!$mantenerCatalogo) {
            foreach (['producto_extras', 'producto_tamanos', 'productos', 'categorias'] as $t) {
                $count = DB::table($t)->count();
                DB::table($t)->truncate();
                $tablasResumen[$t] = $count;
            }
            // Limpiar imágenes
            try {
                Storage::disk('public')->deleteDirectory('productos');
                Storage::disk('public')->deleteDirectory('categorias');
            } catch (\Throwable $e) {}
        }

        // 5. Usuarios (opcional)
        if (!$mantenerUsuarios) {
            $count = DB::table('users')->where('email', '!=', 'admin@tpv.local')->count();
            DB::table('users')->where('email', '!=', 'admin@tpv.local')->delete();
            $tablasResumen['users'] = $count;
        }

        // 6. Configuración (opcional)
        if (!$mantenerConfig) {
            DB::table('configuracion')->truncate();
            $tablasResumen['configuracion'] = 1;
            // Limpiar logo y favicon
            try {
                Storage::disk('public')->deleteDirectory('empresa');
            } catch (\Throwable $e) {}
            // Re-crear configuración base
            Configuracion::create([
                'nombre_empresa'    => 'Mi Negocio FastFood',
                'pais'              => 'España',
                'moneda_codigo'     => 'EUR',
                'moneda_simbolo'    => '€',
                'moneda_posicion'   => 'derecha',
                'decimales'         => 2,
                'separador_decimal' => ',',
                'separador_miles'   => '.',
                'iva_general'       => 10.00,
                'iva_reducido'      => 4.00,
                'precios_con_iva'   => true,
                'serie_ticket'      => 'T',
                'serie_factura'     => 'F',
                'serie_pedido'      => 'P',
                'proximo_ticket'    => 1,
                'proximo_pedido'    => 1,
                'proximo_factura'   => 1,
                'imprimir_logo_ticket' => true,
                'ancho_ticket_mm'   => 80,
                'modulo_domicilio'  => true,
                'modulo_mesas'      => true,
                'modulo_recogida'   => true,
                'modulo_telefono'   => true,
                'coste_envio'       => 0,
                'pedido_minimo_envio' => 0,
                'hora_apertura'     => '09:00',
                'hora_cierre'       => '23:30',
                'color_primario'    => '#28a745',
                'color_secundario'  => '#343a40',
                'tema'              => 'claro',
                'alerta_stock_bajo' => true,
                'umbral_stock_bajo' => 5,
                'programa_puntos'   => false,
                'puntos_por_euro'   => 1,
                'valor_punto'       => 0.01,
            ]);
            Configuracion::refrescar();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        return [
            'tablas'  => $tablasResumen,
            'total'   => array_sum($tablasResumen),
        ];
    }

    /**
     * Estadísticas del sistema.
     */
    public function estadisticas(): array
    {
        $dbName = config('database.connections.mysql.database');
        $tamano = 0;
        try {
            $r = DB::select("SELECT SUM(data_length + index_length) AS s FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
            $tamano = (int) ($r[0]->s ?? 0);
        } catch (\Throwable $e) {}

        return [
            'tablas'    => count($this->listarTablas()),
            'pedidos'   => DB::table('pedidos')->count(),
            'productos' => DB::table('productos')->count(),
            'clientes'  => DB::table('clientes')->count(),
            'tamano_bd' => $tamano,
        ];
    }

    /** Tamaño legible (B/KB/MB). */
    public function formatearTamano(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        $units = ['KB', 'MB', 'GB'];
        $i = -1;
        do {
            $bytes /= 1024;
            $i++;
        } while ($bytes >= 1024 && $i < 2);
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /** Lista las tablas de la BD actual. */
    private function listarTablas(): array
    {
        $tables = DB::select('SHOW TABLES');
        $list = [];
        foreach ($tables as $t) {
            $arr = (array) $t;
            $list[] = array_values($arr)[0];
        }
        return $list;
    }

    /** Formatea un valor para SQL INSERT. */
    private function formatearValor($value): string
    {
        if ($value === null) return 'NULL';
        if (is_bool($value)) return $value ? '1' : '0';
        if (is_int($value) || is_float($value)) return (string) $value;
        // Texto: escape via PDO quote (devuelve con comillas incluidas)
        return DB::getPdo()->quote((string) $value);
    }

    /**
     * Divide un SQL en sentencias separando por ; al final de línea.
     * Funciona bien con dumps generados por este servicio.
     */
    private function dividirSQL(string $sql): array
    {
        $statements = [];
        $current = '';
        $lines = preg_split('/\R/u', $sql);

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '--')) continue;
            $current .= $line . "\n";
            if (str_ends_with(rtrim($line), ';')) {
                $statements[] = $current;
                $current = '';
            }
        }
        if (trim($current) !== '') {
            $statements[] = $current;
        }
        return $statements;
    }
}
