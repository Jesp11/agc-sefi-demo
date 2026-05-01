<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';
    protected $primaryKey = 'id_direccion';

    protected $fillable = [
        'direccion',
        'entre_calles',
    ];

    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(Cliente::class, 'cliente_direcciones', 'id_direccion', 'id_cliente')
            ->withPivot('tipo')
            ->withTimestamps();
    }
}
