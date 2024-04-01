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
            $table->id();
            $table->string('canchaNombre',20);
            $table->dateTime('horarioInicio');
            $table->dateTime('horarioFin');
            $table->decimal('precioCancha', 6, 2);
            $table->string('estado',20);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['canchaNombre', 'horarioInicio', 'horarioFin']);

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
