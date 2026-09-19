<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComprobanteElectronico extends Model
{
    protected $table = 'comprobantes_electronicos';
    protected $guarded = ['id'];

    protected $casts = [
        'fecha_emision'   => 'date',
        'fecha_vencimiento' => 'date',
        'observaciones'   => 'array',
        'enviado_at'      => 'datetime',
        'aceptado_at'     => 'datetime',
    ];

    /** Catálogo 01 SUNAT */
    public const TIPOS = [
        '01' => 'Factura',
        '03' => 'Boleta de venta',
        '07' => 'Nota de crédito',
        '08' => 'Nota de débito',
    ];

    /** Catálogo 06 SUNAT - Tipos de documento de identidad */
    public const TIPOS_DOC_RECEPTOR = [
        '0' => 'Sin documento',
        '1' => 'DNI',
        '4' => 'Carnet de extranjería',
        '6' => 'RUC',
        '7' => 'Pasaporte',
        'A' => 'Cédula diplomática',
    ];

    /** Etiquetas legibles de estado */
    public const ESTADOS = [
        'borrador'   => 'Borrador',
        'generado'   => 'XML generado',
        'firmado'    => 'XML firmado',
        'enviado'    => 'Enviado a SUNAT',
        'aceptado'   => 'Aceptado',
        'observado'  => 'Aceptado con observaciones',
        'rechazado'  => 'Rechazado',
        'anulado'    => 'Anulado',
        'error'      => 'Error de envío',
    ];

    /** Color de badge por estado */
    public function colorEstado(): string
    {
        return match ($this->estado) {
            'borrador'  => 'secondary',
            'generado'  => 'info',
            'firmado'   => 'primary',
            'enviado'   => 'warning',
            'aceptado'  => 'success',
            'observado' => 'warning',
            'rechazado' => 'danger',
            'anulado'   => 'dark',
            'error'     => 'danger',
            default     => 'light',
        };
    }

    public function iconoEstado(): string
    {
        return match ($this->estado) {
            'borrador'  => 'far fa-file',
            'generado'  => 'fas fa-file-code',
            'firmado'   => 'fas fa-signature',
            'enviado'   => 'fas fa-paper-plane',
            'aceptado'  => 'fas fa-check-circle',
            'observado' => 'fas fa-exclamation-circle',
            'rechazado' => 'fas fa-times-circle',
            'anulado'   => 'fas fa-ban',
            'error'     => 'fas fa-exclamation-triangle',
            default     => 'fas fa-file',
        };
    }

    public function pedido(): BelongsTo  { return $this->belongsTo(Pedido::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function lineas(): HasMany    { return $this->hasMany(ComprobanteLinea::class, 'comprobante_id'); }

    /** Identificador único SUNAT (RUC-tipo-serie-numero). */
    public function identificadorSunat(string $rucEmisor): string
    {
        return "{$rucEmisor}-{$this->tipo}-{$this->serie}-" . str_pad((string) $this->numero, 8, '0', STR_PAD_LEFT);
    }

    /** Nombre del XML según especificación SUNAT. */
    public function nombreXml(string $rucEmisor): string
    {
        return $this->identificadorSunat($rucEmisor) . '.xml';
    }

    public function esFactura(): bool { return $this->tipo === '01'; }
    public function esBoleta(): bool  { return $this->tipo === '03'; }
    public function esNotaCredito(): bool { return $this->tipo === '07'; }
    public function esNotaDebito(): bool  { return $this->tipo === '08'; }
}
