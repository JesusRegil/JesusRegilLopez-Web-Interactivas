<?php

namespace App\Models;

use Database\Factories\RecetaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Receta extends Model
{
    /** @use HasFactory<RecetaFactory> */
    use HasFactory;

    // En la base de datos se guarda el valor (sin acentos); en pantalla se muestra la etiqueta.
    public const CATEGORIAS = [
        'desayuno' => 'Desayuno',
        'almuerzo' => 'Almuerzo',
        'cena' => 'Cena',
        'postre' => 'Postre',
        'bebida' => 'Bebida',
    ];

    public const DIFICULTADES = [
        'facil' => 'Fácil',
        'media' => 'Media',
        'dificil' => 'Difícil',
    ];

    // user_id NO va aquí: nunca viene del formulario, la receta se crea desde $usuario->recetas().
    protected $fillable = [
        'titulo',
        'categoria',
        'tiempo_minutos',
        'dificultad',
        'ingredientes',
        'pasos',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'tiempo_minutos' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoriaEtiqueta(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? (string) $this->categoria;
    }

    public function dificultadEtiqueta(): string
    {
        return self::DIFICULTADES[$this->dificultad] ?? (string) $this->dificultad;
    }

    /** Ingredientes como lista: cada línea de texto es un ingrediente. */
    public function ingredientesLista(): array
    {
        return self::lineas($this->ingredientes);
    }

    /** Pasos como lista: cada línea de texto es un paso. */
    public function pasosLista(): array
    {
        return self::lineas($this->pasos);
    }

    /** Parte un texto en líneas, sin espacios sobrantes y sin líneas vacías. */
    private static function lineas(?string $texto): array
    {
        $lineas = preg_split('/\R/u', (string) $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter(
            array_map(Str::trim(...), $lineas),
            fn (string $linea) => $linea !== '',
        ));
    }
}