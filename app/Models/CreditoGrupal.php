<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditoGrupal extends Model
{
    protected $table = 'creditos_grupales';
    protected $primaryKey = 'id_credito_grupal';
    protected $fillable = [
        'num_prog', 'fecha', 'id_cliente', 'id_grupo', 'id_asesor',
        'ciclo', 'dias_pago', 'valor', 'plazos', 'monto_otorgado',
        'interes', 'total', 'saldo_total', 'saldo_inversion',
        'semanas_faltantes', 'credito_total', 'saldo_grupal'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function asesor()
    {
        return $this->belongsTo(Asesor::class, 'id_asesor', 'id_asesor');
    }

    public function pagos()
    {
        return $this->hasMany(PagoGrupal::class, 'id_credito_grupal', 'id_credito_grupal');
    }

    public function ahorros()
    {
        return $this->hasMany(AhorroGrupal::class, 'id_credito_grupal', 'id_credito_grupal');
    }
}
