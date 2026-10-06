<?php

namespace Database\Factories;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receta>
 */
class RecetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'titulo' => fake()->words(3, true),
            'categoria' => fake()->randomElement(array_keys(Receta::CATEGORIAS)),
            'tiempo_minutos' => fake()->numberBetween(5, 180),
            'dificultad' => fake()->randomElement(array_keys(Receta::DIFICULTADES)),
            'ingredientes' => "2 huevos\n1 taza de harina\n1 pizca de sal",
            'pasos' => "Mezcla todos los ingredientes.\nCocina a fuego medio.\nSirve caliente.",
            'nota' => null,
        ];
    }
}