<?php

namespace Tests\Feature;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RecetasTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create();
        $this->actingAs($this->usuario);
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Pastel de chocolate',
            'categoria' => 'postre',
            'tiempo_minutos' => 60,
            'dificultad' => 'media',
            'ingredientes' => "2 tazas de harina\n1 taza de azúcar\n3 huevos",
            'pasos' => "Mezclar los ingredientes secos.\nAgregar los huevos.\nHornear 40 minutos.",
            'nota' => 'Le gusta a toda la familia.',
        ], $cambios);
    }

    private function crearReceta(array $atributos = []): Receta
    {
        return Receta::factory()->for($this->usuario)->create($atributos);
    }

    // ---------- Fase 1: crear y ver ----------

    public function test_el_formulario_de_nueva_receta_tiene_todos_los_campos(): void
    {
        $this->get('/recetas/crear')
            ->assertOk()
            ->assertSee('Nueva receta')
            ->assertSee('name="titulo"', false)
            ->assertSee('name="categoria"', false)
            ->assertSee('name="tiempo_minutos"', false)
            ->assertSee('name="dificultad"', false)
            ->assertSee('name="ingredientes"', false)
            ->assertSee('name="pasos"', false)
            ->assertSee('name="nota"', false)
            ->assertSee('Selecciona…')
            ->assertSee('Escribe un ingrediente por línea')
            ->assertSee('Escribe un paso por línea')
            ->assertSee('(opcional)');
    }

    public function test_los_formularios_no_usan_validacion_html5_del_navegador(): void
    {
        $this->get('/recetas/crear')
            ->assertSee('novalidate', false)
            ->assertDontSee('required', false)
            ->assertDontSee(' min="', false)
            ->assertDontSee(' max="', false);
    }

    public function test_crear_una_receta_valida_la_muestra_en_el_listado_con_mensaje_de_exito(): void
    {
        $this->post('/recetas', $this->datosValidos())->assertRedirect('/recetas');

        $this->assertDatabaseHas('recetas', [
            'user_id' => $this->usuario->id,
            'titulo' => 'Pastel de chocolate',
            'categoria' => 'postre',
            'tiempo_minutos' => 60,
            'dificultad' => 'media',
            'nota' => 'Le gusta a toda la familia.',
        ]);

        $this->get('/recetas')
            ->assertOk()
            ->assertSee('Receta creada correctamente.')
            ->assertSee('Pastel de chocolate')
            ->assertSee('Postre')
            ->assertSee('60 min')
            ->assertSee('Media')
            ->assertDontSee('Aún no tienes recetas');
    }

    public function test_la_nota_es_opcional(): void
    {
        $this->post('/recetas', $this->datosValidos(['nota' => '']))->assertRedirect('/recetas');

        $this->assertDatabaseHas('recetas', ['titulo' => 'Pastel de chocolate', 'nota' => null]);
    }

    public static function datosInvalidos(): array
    {
        return [
            'título vacío' => ['titulo', '', 'El título es obligatorio.'],
            'título solo con espacios' => ['titulo', '     ', 'El título es obligatorio.'],
            'título demasiado largo' => ['titulo', str_repeat('a', 151), 'El título no puede tener más de 150 caracteres.'],
            'categoría vacía' => ['categoria', '', 'La categoría es obligatoria.'],
            'categoría fuera de la lista' => ['categoria', 'merienda', 'Elige una categoría de la lista.'],
            'tiempo vacío' => ['tiempo_minutos', '', 'El tiempo en minutos es obligatorio.'],
            'tiempo igual a 0' => ['tiempo_minutos', '0', 'El tiempo en minutos debe ser mayor a 0.'],
            'tiempo negativo' => ['tiempo_minutos', '-5', 'El tiempo en minutos debe ser mayor a 0.'],
            'tiempo no numérico' => ['tiempo_minutos', 'abc', 'El tiempo en minutos debe ser un número entero.'],
            'tiempo con decimales' => ['tiempo_minutos', '1.5', 'El tiempo en minutos debe ser un número entero.'],
            'tiempo exagerado' => ['tiempo_minutos', '10081', 'El tiempo en minutos no puede ser mayor a 10080 (una semana).'],
            'dificultad vacía' => ['dificultad', '', 'La dificultad es obligatoria.'],
            'dificultad fuera de la lista' => ['dificultad', 'imposible', 'Elige una dificultad de la lista.'],
            'ingredientes vacíos' => ['ingredientes', '', 'Los ingredientes son obligatorios.'],
            'pasos vacíos' => ['pasos', '', 'Los pasos de preparación son obligatorios.'],
        ];
    }

    #[DataProvider('datosInvalidos')]
    public function test_crear_con_datos_invalidos_muestra_el_error_junto_al_campo_y_no_guarda_nada(string $campo, string $valor, string $mensaje): void
    {
        // El error aparece justo después del campo que lo causó.
        $this->from('/recetas/crear')
            ->followingRedirects()
            ->post('/recetas', $this->datosValidos([$campo => $valor]))
            ->assertOk()
            ->assertSee('Nueva receta')
            ->assertSeeInOrder(['name="'.$campo.'"', $mensaje], false);

        $this->assertDatabaseCount('recetas', 0);
    }

    public function test_los_errores_aparecen_junto_a_cada_campo_y_se_conserva_lo_escrito(): void
    {
        $this->from('/recetas/crear')->post('/recetas', [
            'titulo' => '',
            'categoria' => '',
            'tiempo_minutos' => '0',
            'dificultad' => '',
            'ingredientes' => '',
            'pasos' => '',
            'nota' => 'Una nota que no se debe perder',
        ]);

        $this->get('/recetas/crear')->assertSeeInOrder([
            'name="titulo"', 'El título es obligatorio.',
            'name="categoria"', 'La categoría es obligatoria.',
            'name="tiempo_minutos"', 'El tiempo en minutos debe ser mayor a 0.',
            'name="dificultad"', 'La dificultad es obligatoria.',
            'name="ingredientes"', 'Los ingredientes son obligatorios.',
            'name="pasos"', 'Los pasos de preparación son obligatorios.',
            'name="nota"', 'Una nota que no se debe perder',
        ], false);
    }

    public function test_el_formulario_conserva_lo_escrito_cuando_hay_errores(): void
    {
        $this->from('/recetas/crear')->post('/recetas', $this->datosValidos([
            'titulo' => 'Sopa de lentejas',
            'categoria' => 'cena',
            'tiempo_minutos' => 0,
            'dificultad' => 'facil',
        ]));

        $this->get('/recetas/crear')
            ->assertSee('value="Sopa de lentejas"', false)
            ->assertSee('<option value="cena" selected>Cena</option>', false)
            ->assertSee('<option value="facil" selected>Fácil</option>', false)
            ->assertSee('1 taza de azúcar');
    }

    public function test_el_listado_muestra_el_mensaje_cuando_aun_no_hay_recetas(): void
    {
        $this->get('/recetas')
            ->assertOk()
            ->assertSee('Mis recetas')
            ->assertSee('Aún no tienes recetas')
            ->assertSee('Crear mi primera receta')
            ->assertDontSee('<table', false);
    }

    public function test_el_listado_es_una_tabla_con_titulo_categoria_tiempo_y_dificultad(): void
    {
        $receta = $this->crearReceta([
            'titulo' => 'Chilaquiles verdes',
            'categoria' => 'desayuno',
            'tiempo_minutos' => 25,
            'dificultad' => 'dificil',
        ]);

        $this->get('/recetas')
            ->assertOk()
            ->assertSee('<table', false)
            ->assertSeeInOrder(['Título', 'Categoría', 'Tiempo', 'Dificultad', 'Acciones'])
            ->assertSeeInOrder(['Chilaquiles verdes', 'Desayuno', '25 min', 'Difícil'])
            ->assertSee(route('recetas.show', $receta), false)
            ->assertSee(route('recetas.edit', $receta), false)
            ->assertDontSee('Aún no tienes recetas');
    }

    public function test_el_listado_muestra_primero_las_recetas_mas_recientes(): void
    {
        $this->crearReceta(['titulo' => 'Receta antigua', 'created_at' => now()->subDays(3)]);
        $this->crearReceta(['titulo' => 'Receta intermedia', 'created_at' => now()->subDay()]);
        $this->crearReceta(['titulo' => 'Receta nueva', 'created_at' => now()]);

        $this->get('/recetas')->assertSeeInOrder(['Receta nueva', 'Receta intermedia', 'Receta antigua']);
    }

    public function test_el_detalle_muestra_ingredientes_y_pasos_como_listas(): void
    {
        $receta = $this->crearReceta([
            'titulo' => 'Pastel de chocolate',
            'categoria' => 'postre',
            'tiempo_minutos' => 60,
            'dificultad' => 'media',
            'ingredientes' => "2 tazas de harina\r\n\r\n   1 taza de azúcar   \n3 huevos\n",
            'pasos' => "Mezclar los ingredientes secos.\nAgregar los huevos.\n\nHornear 40 minutos.",
            'nota' => "Primera línea de la nota\nSegunda línea de la nota",
        ]);

        $this->get("/recetas/{$receta->id}")
            ->assertOk()
            ->assertSee('Pastel de chocolate')
            ->assertSee('Postre')
            ->assertSee('60 min')
            ->assertSee('Media')
            ->assertSeeInOrder(['<ul', '<li>2 tazas de harina</li>', '<li>1 taza de azúcar</li>', '<li>3 huevos</li>', '</ul>'], false)
            ->assertSeeInOrder(['<ol', '<li>Mezclar los ingredientes secos.</li>', '<li>Agregar los huevos.</li>', '<li>Hornear 40 minutos.</li>', '</ol>'], false)
            ->assertDontSee('<li></li>', false)
            ->assertSee('Nota personal')
            ->assertSee("Primera línea de la nota\nSegunda línea de la nota", false)
            ->assertSee('whitespace-pre-line', false);
    }

    public function test_el_detalle_no_muestra_la_seccion_de_nota_si_no_hay_nota(): void
    {
        $receta = $this->crearReceta(['nota' => null]);

        $this->get("/recetas/{$receta->id}")
            ->assertOk()
            ->assertSee('Ingredientes')
            ->assertSee('Pasos de preparación')
            ->assertDontSee('Nota personal');
    }

    public function test_los_textos_se_escapan_para_evitar_codigo_en_las_paginas(): void
    {
        $peligroso = "<script>alert('x')</script>";
        $receta = $this->crearReceta(['titulo' => $peligroso, 'ingredientes' => $peligroso, 'pasos' => $peligroso, 'nota' => $peligroso]);

        $this->get('/recetas')->assertSee($peligroso)->assertDontSee($peligroso, false);
        $this->get("/recetas/{$receta->id}")->assertSee($peligroso)->assertDontSee($peligroso, false);
    }

    public function test_la_pagina_de_error_404_esta_en_espanol(): void
    {
        $this->get('/esta-pagina-no-existe')
            ->assertNotFound()
            ->assertSee('Página no encontrada');
    }

    public function test_el_servidor_responde_al_chequeo_de_salud(): void
    {
        $this->get('/up')->assertOk();
    }

    // ---------- Fase 2: editar y eliminar ----------

    public function test_el_formulario_de_edicion_aparece_precargado(): void
    {
        $receta = $this->crearReceta($this->datosValidos());

        $this->get("/recetas/{$receta->id}/editar")
            ->assertOk()
            ->assertSee('Editar receta')
            ->assertSee('value="Pastel de chocolate"', false)
            ->assertSee('<option value="postre" selected>Postre</option>', false)
            ->assertSee('value="60"', false)
            ->assertSee('<option value="media" selected>Media</option>', false)
            ->assertSee("2 tazas de harina\n1 taza de azúcar\n3 huevos", false)
            ->assertSee('Mezclar los ingredientes secos.')
            ->assertSee('Le gusta a toda la familia.')
            ->assertSee('name="_method" value="PUT"', false);
    }

    public function test_el_formulario_de_edicion_escapa_los_textos(): void
    {
        $peligroso = "<script>alert('x')</script>";
        $receta = $this->crearReceta(['titulo' => $peligroso, 'ingredientes' => $peligroso, 'pasos' => $peligroso, 'nota' => $peligroso]);

        $this->get("/recetas/{$receta->id}/editar")->assertDontSee($peligroso, false);
    }

    public function test_guardar_los_cambios_actualiza_la_receta_y_muestra_el_mensaje(): void
    {
        $receta = $this->crearReceta($this->datosValidos());

        $this->put("/recetas/{$receta->id}", $this->datosValidos([
            'titulo' => 'Pastel de zanahoria',
            'categoria' => 'desayuno',
            'tiempo_minutos' => 45,
            'dificultad' => 'facil',
            'nota' => '',
        ]))->assertRedirect("/recetas/{$receta->id}");

        $this->assertDatabaseHas('recetas', [
            'id' => $receta->id,
            'user_id' => $this->usuario->id,
            'titulo' => 'Pastel de zanahoria',
            'categoria' => 'desayuno',
            'tiempo_minutos' => 45,
            'dificultad' => 'facil',
            'nota' => null,
        ]);

        $this->get("/recetas/{$receta->id}")
            ->assertSee('Receta actualizada correctamente.')
            ->assertSee('Pastel de zanahoria')
            ->assertSee('45 min')
            ->assertSee('Fácil');
    }

    #[DataProvider('datosInvalidos')]
    public function test_editar_con_datos_invalidos_muestra_el_error_junto_al_campo_y_no_cambia_nada(string $campo, string $valor, string $mensaje): void
    {
        $receta = $this->crearReceta($this->datosValidos());

        $this->from("/recetas/{$receta->id}/editar")
            ->followingRedirects()
            ->put("/recetas/{$receta->id}", $this->datosValidos([$campo => $valor]))
            ->assertOk()
            ->assertSee('Editar receta')
            ->assertSeeInOrder(['name="'.$campo.'"', $mensaje], false);

        $this->assertDatabaseHas('recetas', ['id' => $receta->id, 'titulo' => 'Pastel de chocolate', 'tiempo_minutos' => 60]);
    }

    public function test_eliminar_una_receta_la_quita_del_listado_y_muestra_el_mensaje(): void
    {
        $receta = $this->crearReceta(['titulo' => 'Receta para borrar']);

        $this->delete("/recetas/{$receta->id}")->assertRedirect('/recetas');

        $this->assertDatabaseMissing('recetas', ['id' => $receta->id]);

        $this->get('/recetas')
            ->assertSee('Receta eliminada correctamente.')
            ->assertDontSee('Receta para borrar')
            ->assertSee('Aún no tienes recetas');
    }

    public function test_eliminar_pide_confirmacion_en_el_listado_y_en_el_detalle(): void
    {
        $receta = $this->crearReceta(['titulo' => 'Sopa de fideo']);

        foreach (['/recetas', "/recetas/{$receta->id}"] as $pagina) {
            $this->get($pagina)
                ->assertSee('onsubmit="return confirm(', false)
                ->assertSee('Eliminar la receta')
                ->assertSee('Sopa de fideo')
                ->assertSee('name="_method" value="DELETE"', false)
                ->assertSee('action="'.route('recetas.destroy', $receta).'"', false);
        }
    }

    public function test_el_texto_de_la_confirmacion_no_rompe_el_html_con_comillas_en_el_titulo(): void
    {
        $this->crearReceta(['titulo' => 'La "mejor" receta de la abuela\'s']);

        $this->get('/recetas')
            ->assertSee('onsubmit="return confirm(', false)
            ->assertDontSee('"mejor"', false)
            ->assertDontSee('abuela\'s', false);
    }
}
