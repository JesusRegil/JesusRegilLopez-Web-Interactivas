<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Al borrar un torneo se borran sus inscripciones (cascada)
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->timestamps();

            // Evita inscripciones duplicadas a nivel de base de datos
            $table->unique(['user_id', 'torneo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};