<?php

namespace Tests\Unit;

use App\Models\Receta;
use PHPUnit\Framework\TestCase;

class RecetaTest extends TestCase
{
    public function test_cada_linea_de_ingredientes_es_un_elemento_de_la_lista(): void
    {
        $receta = new Receta(['ingredientes' => "2 huevos\n1 taza de harina\n1 pizca de sal"]);

        $this->assertSame(['2 huevos', '1 taza de harina', '1 pizca de sal'], $receta->ingredientesLista());
    }

    public function test_las_lineas_vacias_y_los_espacios_sobrantes_se_ignoran(): void
    {
        $receta = new Receta(['pasos' => "  Mezclar  \r\n\r\n   \nHornear\r\n\t Servir \n"]);

        $this->assertSame(['Mezclar', 'Hornear', 'Servir'], $receta->pasosLista());
    }

    public function test_funciona_con_saltos_de_linea_de_windows_linux_y_mac_antiguo(): void
    {
        $receta = new Receta(['ingredientes' => "uno\r\ndos\ntres\rcuatro"]);

        $this->assertSame(['uno', 'dos', 'tres', 'cuatro'], $receta->ingredientesLista());
    }

    public function test_un_texto_vacio_o_nulo_da_una_lista_vacia(): void
    {
        $this->assertSame([], (new Receta(['ingredientes' => '']))->ingredientesLista());
        $this->assertSame([], (new Receta(['pasos' => " \n \r\n "]))->pasosLista());
        $this->assertSame([], (new Receta)->ingredientesLista());
    }

    public function test_una_linea_con_un_cero_no_se_pierde(): void
    {
        $receta = new Receta(['pasos' => "0\n1"]);

        $this->assertSame(['0', '1'], $receta->pasosLista());
    }

    public function test_las_etiquetas_se_muestran_con_acentos(): void
    {
        $receta = new Receta(['categoria' => 'postre', 'dificultad' => 'dificil']);

        $this->assertSame('Postre', $receta->categoriaEtiqueta());
        $this->assertSame('Difícil', $receta->dificultadEtiqueta());
    }

    public function test_un_valor_desconocido_se_muestra_tal_cual(): void
    {
        $receta = new Receta(['categoria' => 'otra', 'dificultad' => 'rara']);

        $this->assertSame('otra', $receta->categoriaEtiqueta());
        $this->assertSame('rara', $receta->dificultadEtiqueta());
    }

    public function test_los_valores_guardados_no_llevan_acentos(): void
    {
        $this->assertSame(['desayuno', 'almuerzo', 'cena', 'postre', 'bebida'], array_keys(Receta::CATEGORIAS));
        $this->assertSame(['facil', 'media', 'dificil'], array_keys(Receta::DIFICULTADES));
    }

    public function test_el_dueno_no_se_puede_asignar_en_masa(): void
    {
        $receta = new Receta(['titulo' => 'Sopa', 'user_id' => 99]);

        $this->assertSame('Sopa', $receta->titulo);
        $this->assertNull($receta->user_id);
    }
}