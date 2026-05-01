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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('nombre', 255);
            $table->string('curp', 50)->nullable();
            $table->string('clave_elector', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('ocupacion', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('direcciones', function (Blueprint $table) {
            $table->id('id_direccion');
            $table->text('direccion');
            $table->text('entre_calles')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_direcciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_direccion');
            $table->enum('tipo', ['casa', 'trabajo']);
            $table->timestamps();

            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
            $table->foreign('id_direccion')->references('id_direccion')->on('direcciones')->onDelete('cascade');
        });

        Schema::create('asesores', function (Blueprint $table) {
            $table->id('id_asesor');
            $table->string('nombre', 255);
            $table->timestamps();
        });

        Schema::create('creditos', function (Blueprint $table) {
            $table->id('id_credito');
            $table->integer('num_prog')->nullable();
            $table->unsignedBigInteger('id_cliente');
            $table->date('fecha')->nullable();
            $table->integer('ciclo')->nullable();
            $table->integer('dias_pago')->nullable();
            $table->integer('plazo')->nullable();
            $table->decimal('monto_otorgado', 10, 2)->nullable();
            $table->decimal('interes', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('valor_ficha', 10, 2)->nullable();
            $table->unsignedBigInteger('id_asesor')->nullable();
            $table->timestamps();

            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
            $table->foreign('id_asesor')->references('id_asesor')->on('asesores')->onDelete('set null');
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->unsignedBigInteger('id_credito');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_credito')->references('id_credito')->on('creditos')->onDelete('cascade');
        });

        Schema::create('referencias', function (Blueprint $table) {
            $table->id('id_referencia');
            $table->unsignedBigInteger('id_cliente');
            $table->string('nombre', 255)->nullable();
            $table->enum('tipo', ['familiar', 'amistad'])->nullable();
            $table->string('parentesco', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->integer('anios_relacion')->nullable();
            $table->timestamps();

            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
        });

        Schema::create('avales', function (Blueprint $table) {
            $table->id('id_aval');
            $table->unsignedBigInteger('id_cliente');
            $table->string('nombre', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('parentesco', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avales');
        Schema::dropIfExists('referencias');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('creditos');
        Schema::dropIfExists('asesores');
        Schema::dropIfExists('cliente_direcciones');
        Schema::dropIfExists('direcciones');
        Schema::dropIfExists('clientes');
    }
};
