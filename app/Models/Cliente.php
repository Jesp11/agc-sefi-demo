<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'id_asesor',
        'id_grupo',
        'nombre',
        'curp',
        'clave_elector',
        'telefono',
        'ocupacion',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function direcciones(): BelongsToMany
    {
        return $this->belongsToMany(Direccion::class, 'cliente_direcciones', 'id_cliente', 'id_direccion')
            ->withPivot('tipo')
            ->withTimestamps();
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'id_cliente', 'id_cliente');
    }

    public function referencias(): HasMany
    {
        return $this->hasMany(Referencia::class, 'id_cliente', 'id_cliente');
    }

    public function avales(): HasMany
    {
        return $this->hasMany(Aval::class, 'id_cliente', 'id_cliente');
    }

    public function asesor()
    {
        return $this->belongsTo(Asesor::class, 'id_asesor', 'id_asesor');
    }
}
