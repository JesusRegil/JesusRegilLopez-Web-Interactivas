<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torneos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('juego');
            $table->dateTime('fecha');
            $table->unsignedInteger('cupo')->default(16);
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('abierto'); // 'abierto' o 'cerrado'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torneos');
    }
};