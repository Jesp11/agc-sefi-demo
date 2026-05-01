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
        Schema::create('ahorro_voluntarios', function (Blueprint $table) {
            $table->id('id_ahorro');
            $table->unsignedBigInteger('id_asesor');
            $table->decimal('monto', 12, 2);
            $table->date('fecha');
            $table->string('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_asesor')->references('id_asesor')->on('asesores')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ahorro_voluntarios');
    }
};
