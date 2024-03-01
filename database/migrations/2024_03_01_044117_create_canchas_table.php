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
        Schema::create('canchas', function (Blueprint $table) {
            $table->bigIncrements('canchaId');
            $table->dateTime('horario');
            $table->decimal('precioCancha', 6, 2);
            $table->decimal('tiempoCancha', 4, 2);

            $table->bigInteger('reservaId')->unsigned();
            $table->foreign('reservaId')->references('reservaId')->on('reservas')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};
