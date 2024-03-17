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
        Schema::create('facturas', function (Blueprint $table) {
            $table->bigIncrements('facturaId');
            $table->integer('cedulaFact')->lenght(13);
            $table->string('nombreFact', 30);
            $table->string('apellidoFact', 30);
            $table->string('direccionFact', 50);
            $table->integer('celularFact')->lenght(10)->nullable();

            $table->bigInteger('reservaId')->unsigned()->unique();
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
        Schema::dropIfExists('facturas');
    }
};
