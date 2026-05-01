<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AhorroGrupal extends Model
{
    protected $table = 'ahorros_grupales';
    protected $primaryKey = 'id_ahorro_grupal';
    protected $fillable = ['id_credito_grupal', 'numero_pago', 'monto'];

    public function creditoGrupal()
    {
        return $this->belongsTo(CreditoGrupal::class, 'id_credito_grupal', 'id_credito_grupal');
    }
}
