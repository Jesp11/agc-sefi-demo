<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aval extends Model
{
    use HasFactory;

    protected $table = 'avales';
    protected $primaryKey = 'id_aval';

    protected $fillable = [
        'id_cliente',
        'nombre',
        'telefono',
        'direccion',
        'parentesco',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }
}
