<?php

namespace App\Http\Requests;

use App\Models\Receta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Las mismas reglas para crear y para editar una receta.
 */
class RecetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo llegan usuarios con sesión (middleware "auth") y la receta ya es suya (ver AppServiceProvider).
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'categoria' => ['required', Rule::in(array_keys(Receta::CATEGORIAS))],
            'tiempo_minutos' => ['required', 'integer', 'min:1', 'max:10080'],
            'dificultad' => ['required', Rule::in(array_keys(Receta::DIFICULTADES))],
            'ingredientes' => ['required', 'string'],
            'pasos' => ['required', 'string'],
            'nota' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede tener más de :max caracteres.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.in' => 'Elige una categoría de la lista.',
            'tiempo_minutos.required' => 'El tiempo en minutos es obligatorio.',
            'tiempo_minutos.integer' => 'El tiempo en minutos debe ser un número entero.',
            'tiempo_minutos.min' => 'El tiempo en minutos debe ser mayor a 0.',
            'tiempo_minutos.max' => 'El tiempo en minutos no puede ser mayor a :max (una semana).',
            'dificultad.required' => 'La dificultad es obligatoria.',
            'dificultad.in' => 'Elige una dificultad de la lista.',
            'ingredientes.required' => 'Los ingredientes son obligatorios.',
            'pasos.required' => 'Los pasos de preparación son obligatorios.',
        ];
    }
}