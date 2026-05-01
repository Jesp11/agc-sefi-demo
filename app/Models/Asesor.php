<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asesor extends Model
{
    use HasFactory;

    protected $table = 'asesores';
    protected $primaryKey = 'id_asesor';

    protected $fillable = [
        'nombre',
    ];

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'id_asesor', 'id_asesor');
    }

    public function ahorros(): HasMany
    {
        return $this->hasMany(AhorroVoluntario::class, 'id_asesor', 'id_asesor');
    }
}
