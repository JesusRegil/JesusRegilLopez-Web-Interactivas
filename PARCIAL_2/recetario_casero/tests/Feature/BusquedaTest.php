<?php

namespace Tests\Feature;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusquedaTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create();
        $this->actingAs($this->usuario);

        $this->crearReceta('Pastel de chocolate', 'postre');
        $this->crearReceta('Galletas de avena', 'postre');
        $this->crearReceta('Chilaquiles verdes', 'desayuno');
        $this->crearReceta('Jugo de naranja', 'bebida');
        $this->crearReceta('Pasta con crema', 'cena');
    }

    private function crearReceta(string $titulo, string $categoria): Receta
    {
        return Receta::factory()->for($this->usuario)->create(['titulo' => $titulo, 'categoria' => $categoria]);
    }

    public function test_sin_filtros_el_listado_muestra_todas_las_recetas(): void
    {
        $this->get('/recetas')
            ->assertOk()
            ->assertSee('Pastel de chocolate')
            ->assertSee('Galletas de avena')
            ->assertSee('Chilaquiles verdes')
            ->assertSee('Jugo de naranja')
            ->assertSee('Pasta con crema');
    }

    public function test_buscar_por_titulo_encuentra_coincidencias_parciales(): void
    {
        $this->get('/recetas?q=choco')
            ->assertOk()
            ->assertSee('Pastel de chocolate')
            ->assertDontSee('Galletas de avena')
            ->assertDontSee('Chilaquiles verdes')
            ->assertDontSee('Jugo de naranja')
            ->assertDontSee('Pasta con crema');
    }

    public function test_la_busqueda_no_distingue_mayusculas_de_minusculas(): void
    {
        $this->get('/recetas?q=CHOCOLATE')->assertSee('Pastel de chocolate')->assertDontSee('Galletas de avena');
        $this->get('/recetas?q=gAlLeTaS')->assertSee('Galletas de avena')->assertDontSee('Pastel de chocolate');
    }

    public function test_la_busqueda_ignora_espacios_al_principio_y_al_final(): void
    {
        $this->get('/recetas?q=%20%20avena%20%20')
            ->assertSee('Galletas de avena')
            ->assertDontSee('Pastel de chocolate');
    }

    public function test_filtrar_por_categoria_muestra_solo_esa_categoria(): void
    {
        $this->get('/recetas?categoria=postre')
            ->assertOk()
            ->assertSee('Pastel de chocolate')
            ->assertSee('Galletas de avena')
            ->assertDontSee('Chilaquiles verdes')
            ->assertDontSee('Jugo de naranja')
            ->assertDontSee('Pasta con crema');
    }

    public function test_el_buscador_y_el_filtro_de_categoria_se_combinan(): void
    {
        // "pas" coincide con "Pastel..." (postre) y con "Pasta..." (cena); la categoría deja solo una.
        $this->get('/recetas?q=pas')->assertSee('Pastel de chocolate')->assertSee('Pasta con crema');

        $this->get('/recetas?q=pas&categoria=postre')
            ->assertSee('Pastel de chocolate')
            ->assertDontSee('Pasta con crema')
            ->assertDontSee('Galletas de avena');

        $this->get('/recetas?q=pas&categoria=cena')
            ->assertSee('Pasta con crema')
            ->assertDontSee('Pastel de chocolate');
    }

    public function test_sin_resultados_muestra_el_aviso_y_un_enlace_para_limpiar(): void
    {
        $this->get('/recetas?q=zzzz')
            ->assertOk()
            ->assertSee('No se encontraron recetas con esa búsqueda o categoría')
            ->assertSee('Limpiar filtros')
            ->assertDontSee('Aún no tienes recetas')
            ->assertDontSee('<table', false);

        // Combinación sin coincidencias: existe "chocolate" pero no es de la categoría "bebida".
        $this->get('/recetas?q=chocolate&categoria=bebida')
            ->assertSee('No se encontraron recetas con esa búsqueda o categoría');
    }

    public function test_el_formulario_conserva_lo_escrito_y_ofrece_limpiar(): void
    {
        $this->get('/recetas?q=choco&categoria=postre')
            ->assertSee('value="choco"', false)
            ->assertSee('<option value="postre" selected>Postre</option>', false)
            ->assertSee('Limpiar')
            ->assertSee('href="'.route('recetas.index').'"', false);
    }

    public function test_con_filtros_y_sin_ninguna_receta_tambien_se_avisa_que_no_hay_resultados(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/recetas')->assertSee('Aún no tienes recetas');
        $this->get('/recetas?q=algo')->assertSee('No se encontraron recetas con esa búsqueda o categoría');
    }

    public function test_una_categoria_invalida_se_ignora(): void
    {
        $this->get('/recetas?categoria=inexistente')
            ->assertOk()
            ->assertSee('Pastel de chocolate')
            ->assertSee('Jugo de naranja')
            ->assertDontSee('No se encontraron recetas');
    }

    public function test_los_comodines_de_like_no_coinciden_con_todo(): void
    {
        $this->get('/recetas?q=%25')->assertOk()->assertSee('No se encontraron recetas');
        $this->get('/recetas?q=_')->assertOk()->assertSee('No se encontraron recetas');
        $this->get('/recetas?q='.urlencode('\\'))->assertOk()->assertSee('No se encontraron recetas');
    }

    public function test_los_parametros_raros_no_rompen_el_listado(): void
    {
        $this->get('/recetas?q[]=a&categoria[]=postre')
            ->assertOk()
            ->assertSee('Pastel de chocolate')
            ->assertSee('Jugo de naranja');
    }

    public function test_el_texto_buscado_se_escapa_en_el_formulario(): void
    {
        $peligroso = '"><script>alert(1)</script>';

        $this->get('/recetas?q='.urlencode($peligroso))
            ->assertOk()
            ->assertDontSee($peligroso, false)
            ->assertSee('No se encontraron recetas');
    }
}