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
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_asesor')->nullable()->after('id_cliente');
            $table->foreign('id_asesor')->references('id_asesor')->on('asesores')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['id_asesor']);
            $table->dropColumn('id_asesor');
        });
    }
};
