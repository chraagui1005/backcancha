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
        Schema::create('bebidas', function (Blueprint $table) {
            $table->string('bebidaId')->lenght(10);
            $table->primary('bebidaId');
            $table->decimal('precioBebida', 4, 2);
            $table->integer('stockBebida')->lenght(6);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bebidas');
    }
};
