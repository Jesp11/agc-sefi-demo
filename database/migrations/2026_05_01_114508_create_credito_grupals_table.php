<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('creditos_grupales', function (Blueprint $table) {
            $table->id('id_credito_grupal');
            $table->integer('num_prog')->nullable();
            $table->date('fecha')->nullable();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_cliente');
            $table->foreignId('id_grupo')->constrained('grupos', 'id_grupo');
            $table->foreignId('id_asesor')->constrained('asesores', 'id_asesor');
            $table->integer('ciclo')->nullable();
            $table->string('dias_pago', 50)->nullable();
            $table->decimal('valor', 10, 2)->nullable();
            $table->integer('plazos')->nullable();
            $table->decimal('monto_otorgado', 10, 2)->nullable();
            $table->decimal('interes', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('saldo_total', 10, 2)->nullable();
            $table->decimal('saldo_inversion', 10, 2)->nullable();
            $table->integer('semanas_faltantes')->nullable();
            $table->decimal('credito_total', 10, 2)->nullable();
            $table->decimal('saldo_grupal', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos_grupales');
    }
};
