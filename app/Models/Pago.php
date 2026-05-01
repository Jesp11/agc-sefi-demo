<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_credito',
        'fecha_pago',
        'monto',
    ];

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'id_credito', 'id_credito');
    }
}
