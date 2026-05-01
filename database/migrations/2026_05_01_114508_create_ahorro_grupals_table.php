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
        Schema::create('ahorros_grupales', function (Blueprint $table) {
            $table->id('id_ahorro_grupal');
            $table->foreignId('id_credito_grupal')->constrained('creditos_grupales', 'id_credito_grupal')->onDelete('cascade');
            $table->integer('numero_pago');
            $table->decimal('monto', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahorros_grupales');
    }
};
