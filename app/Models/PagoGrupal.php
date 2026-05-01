<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoGrupal extends Model
{
    protected $table = 'pagos_grupales';
    protected $primaryKey = 'id_pago_grupal';
    protected $fillable = ['id_credito_grupal', 'numero_pago', 'monto'];

    public function creditoGrupal()
    {
        return $this->belongsTo(CreditoGrupal::class, 'id_credito_grupal', 'id_credito_grupal');
    }
}
