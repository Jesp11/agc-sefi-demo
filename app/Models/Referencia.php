<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referencia extends Model
{
    use HasFactory;

    protected $table = 'referencias';
    protected $primaryKey = 'id_referencia';

    protected $fillable = [
        'id_cliente',
        'nombre',
        'tipo',
        'parentesco',
        'telefono',
        'direccion',
        'anios_relacion',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
