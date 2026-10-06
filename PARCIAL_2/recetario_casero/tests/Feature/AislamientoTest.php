<?php

namespace Tests\Feature;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AislamientoTest extends TestCase
{
    use RefreshDatabase;

    private User $ana;

    private User $luis;

    private Receta $recetaDeAna;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ana = User::factory()->create(['name' => 'Ana']);
        $this->luis = User::factory()->create(['name' => 'Luis']);

        $this->recetaDeAna = Receta::factory()->for($this->ana)->create([
            'titulo' => 'Mole poblano de Ana',
            'categoria' => 'almuerzo',
        ]);
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Título intruso',
            'categoria' => 'cena',
            'tiempo_minutos' => 10,
            'dificultad' => 'facil',
            'ingredientes' => '1 cosa',
            'pasos' => '1 paso',
            'nota' => '',
        ], $cambios);
    }

    // ---------- Fase 1: crear y ver ----------

    public function test_cada_usuario_ve_solo_sus_propias_recetas(): void
    {
        $this->actingAs($this->ana)->get('/recetas')->assertSee('Mole poblano de Ana');

        $this->actingAs($this->luis)->get('/recetas')
            ->assertOk()
            ->assertDontSee('Mole poblano de Ana')
            ->assertSee('Aún no tienes recetas');
    }

    public function test_otro_usuario_distinto_entra_a_un_recetario_vacio_aunque_el_primero_tenga_recetas(): void
    {
        Receta::factory()->for($this->ana)->count(3)->create();

        $this->actingAs($this->luis)->get('/recetas')
            ->assertOk()
            ->assertSee('Aún no tienes recetas')
            ->assertDontSee('<table', false);
    }

    public function test_otro_usuario_recibe_404_al_ver_la_receta(): void
    {
        $this->actingAs($this->luis)->get("/recetas/{$this->recetaDeAna->id}")->assertNotFound();
    }

    public function test_la_receta_nueva_siempre_pertenece_a_quien_la_crea_aunque_se_mande_otro_user_id(): void
    {
        $this->actingAs($this->luis)
            ->post('/recetas', $this->datosValidos(['titulo' => 'Receta de Luis', 'user_id' => $this->ana->id]))
            ->assertRedirect('/recetas');

        $this->assertDatabaseHas('recetas', ['titulo' => 'Receta de Luis', 'user_id' => $this->luis->id]);
        $this->assertDatabaseMissing('recetas', ['titulo' => 'Receta de Luis', 'user_id' => $this->ana->id]);
    }

    public function test_los_ids_que_no_existen_o_no_son_numericos_dan_404(): void
    {
        $this->actingAs($this->ana);

        $this->get('/recetas/99999')->assertNotFound();
        $this->get('/recetas/abc')->assertNotFound();
        $this->get('/recetas/1-or-1=1')->assertNotFound();
        $this->get('/recetas/99999999999999999999')->assertNotFound();
        $this->delete('/recetas/abc')->assertNotFound();
    }

    public function test_al_borrar_un_usuario_se_borran_sus_recetas(): void
    {
        $this->assertDatabaseCount('recetas', 1);

        $this->ana->delete();

        $this->assertDatabaseCount('recetas', 0);
    }

    // ---------- Fase 2: editar y eliminar ----------

    public function test_otro_usuario_recibe_404_al_abrir_el_formulario_de_edicion(): void
    {
        $this->actingAs($this->luis)->get("/recetas/{$this->recetaDeAna->id}/editar")->assertNotFound();
    }

    public function test_otro_usuario_recibe_404_al_actualizar_y_la_receta_no_cambia(): void
    {
        $this->actingAs($this->luis)
            ->put("/recetas/{$this->recetaDeAna->id}", $this->datosValidos())
            ->assertNotFound();

        $this->assertDatabaseHas('recetas', ['id' => $this->recetaDeAna->id, 'titulo' => 'Mole poblano de Ana']);
        $this->assertDatabaseMissing('recetas', ['titulo' => 'Título intruso']);
    }

    public function test_otro_usuario_recibe_404_al_eliminar_y_la_receta_sigue_existiendo(): void
    {
        $this->actingAs($this->luis)->delete("/recetas/{$this->recetaDeAna->id}")->assertNotFound();

        $this->assertDatabaseHas('recetas', ['id' => $this->recetaDeAna->id]);
    }

    public function test_el_dueno_si_puede_ver_editar_y_eliminar_su_receta(): void
    {
        $this->actingAs($this->ana);

        $this->get("/recetas/{$this->recetaDeAna->id}")->assertOk();
        $this->get("/recetas/{$this->recetaDeAna->id}/editar")->assertOk();
        $this->put("/recetas/{$this->recetaDeAna->id}", $this->datosValidos(['titulo' => 'Mole mejorado']))->assertRedirect();
        $this->delete("/recetas/{$this->recetaDeAna->id}")->assertRedirect('/recetas');

        $this->assertDatabaseCount('recetas', 0);
    }

    public function test_al_editar_no_se_puede_cambiar_el_dueno_de_la_receta(): void
    {
        $recetaDeLuis = Receta::factory()->for($this->luis)->create(['titulo' => 'Receta de Luis']);

        $this->actingAs($this->luis)
            ->put("/recetas/{$recetaDeLuis->id}", $this->datosValidos(['titulo' => 'Sigue siendo de Luis', 'user_id' => $this->ana->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('recetas', ['id' => $recetaDeLuis->id, 'titulo' => 'Sigue siendo de Luis', 'user_id' => $this->luis->id]);
    }

    // ---------- Fase 3: buscar y filtrar ----------

    public function test_la_busqueda_solo_recorre_las_recetas_propias(): void
    {
        $this->actingAs($this->luis)->get('/recetas?q=Mole')
            ->assertOk()
            ->assertDontSee('Mole poblano de Ana')
            ->assertSee('No se encontraron recetas');

        $this->actingAs($this->luis)->get('/recetas?categoria=almuerzo')
            ->assertDontSee('Mole poblano de Ana');
    }
}