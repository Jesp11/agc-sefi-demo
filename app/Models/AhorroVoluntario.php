<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AhorroVoluntario extends Model
{
    use HasFactory;

    protected $table = 'ahorro_voluntarios';
    protected $primaryKey = 'id_ahorro';

    protected $fillable = [
        'id_asesor',
        'monto',
        'fecha',
        'observaciones',
    ];

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'id_asesor', 'id_asesor');
    }
}
