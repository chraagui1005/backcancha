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
        Schema::create('reservas', function (Blueprint $table) {
            $table->bigIncrements('reservaId');
            $table->dateTime('horarioInicio');
            $table->dateTime('horarioFin');
            $table->string('canchaNombre');
            $table->string('bebidaId');
            $table->foreign('bebidaId')->references('bebidaId')->on('bebidas')->onDelete('restrict')->onUpdate('cascade');

            $table->integer('cantidadBebidas');
            $table->decimal('precioTotal', 4, 2);

            $table->string('email');
            $table->timestamps();
            $table->softDeletes();

            // Definir clave única
            $table->unique(['horarioInicio', 'horarioFin', 'canchaNombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
