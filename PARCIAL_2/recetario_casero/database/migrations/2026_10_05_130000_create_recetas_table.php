<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->string('categoria', 20);       // desayuno, almuerzo, cena, postre o bebida
            $table->unsignedSmallInteger('tiempo_minutos');
            $table->string('dificultad', 20);      // facil, media o dificil
            $table->text('ingredientes');          // uno por línea
            $table->text('pasos');                 // uno por línea
            $table->text('nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};