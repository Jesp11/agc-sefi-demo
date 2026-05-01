<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Credito extends Model
{
    use HasFactory;

    protected $table = 'creditos';
    protected $primaryKey = 'id_credito';

    protected $fillable = [
        'num_prog',
        'id_cliente',
        'fecha',
        'ciclo',
        'dias_pago',
        'plazo',
        'monto_otorgado',
        'interes',
        'total',
        'valor_ficha',
        'id_asesor',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'id_asesor', 'id_asesor');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_credito', 'id_credito');
    }
}
