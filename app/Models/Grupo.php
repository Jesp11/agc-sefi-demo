<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';
    protected $fillable = ['nombre', 'id_asesor', 'id_responsable'];

    public function asesor()
    {
        return $this->belongsTo(Asesor::class, 'id_asesor');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_grupo');
    }

    public function responsable()
    {
        return $this->belongsTo(Cliente::class, 'id_responsable', 'id_cliente');
    }
}
