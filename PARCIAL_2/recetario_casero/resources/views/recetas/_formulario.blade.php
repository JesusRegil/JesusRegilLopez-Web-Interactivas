{{-- Campos compartidos por "Nueva receta" y "Editar receta".
     Sin atributos HTML5 de validación (required, min, max): los errores salen del servidor, en español. --}}

<div>
    <label for="titulo" class="block text-sm font-medium">Título</label>
    <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $receta->titulo) }}"
           class="mt-1 w-full rounded border px-3 py-2 @error('titulo') border-red-500 @else border-stone-300 @enderror">
    @error('titulo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label for="categoria" class="block text-sm font-medium">Categoría</label>
        <select id="categoria" name="categoria"
                class="mt-1 w-full rounded border px-3 py-2 @error('categoria') border-red-500 @else border-stone-300 @enderror">
            <option value="">Selecciona…</option>
            @foreach (\App\Models\Receta::CATEGORIAS as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('categoria', $receta->categoria) === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('categoria')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tiempo_minutos" class="block text-sm font-medium">Tiempo (minutos)</label>
        <input type="number" id="tiempo_minutos" name="tiempo_minutos" value="{{ old('tiempo_minutos', $receta->tiempo_minutos) }}"
               class="mt-1 w-full rounded border px-3 py-2 @error('tiempo_minutos') border-red-500 @else border-stone-300 @enderror">
        @error('tiempo_minutos')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="dificultad" class="block text-sm font-medium">Dificultad</label>
        <select id="dificultad" name="dificultad"
                class="mt-1 w-full rounded border px-3 py-2 @error('dificultad') border-red-500 @else border-stone-300 @enderror">
            <option value="">Selecciona…</option>
            @foreach (\App\Models\Receta::DIFICULTADES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('dificultad', $receta->dificultad) === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('dificultad')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label for="ingredientes" class="block text-sm font-medium">Ingredientes</label>
    <textarea id="ingredientes" name="ingredientes" rows="6"
              class="mt-1 w-full rounded border px-3 py-2 @error('ingredientes') border-red-500 @else border-stone-300 @enderror">{{ old('ingredientes', $receta->ingredientes) }}</textarea>
    <p class="mt-1 text-sm text-stone-500">Escribe un ingrediente por línea</p>
    @error('ingredientes')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="pasos" class="block text-sm font-medium">Pasos de preparación</label>
    <textarea id="pasos" name="pasos" rows="6"
              class="mt-1 w-full rounded border px-3 py-2 @error('pasos') border-red-500 @else border-stone-300 @enderror">{{ old('pasos', $receta->pasos) }}</textarea>
    <p class="mt-1 text-sm text-stone-500">Escribe un paso por línea</p>
    @error('pasos')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="nota" class="block text-sm font-medium">Nota personal <span class="font-normal text-stone-500">(opcional)</span></label>
    <textarea id="nota" name="nota" rows="3"
              class="mt-1 w-full rounded border px-3 py-2 @error('nota') border-red-500 @else border-stone-300 @enderror">{{ old('nota', $receta->nota) }}</textarea>
    @error('nota')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>