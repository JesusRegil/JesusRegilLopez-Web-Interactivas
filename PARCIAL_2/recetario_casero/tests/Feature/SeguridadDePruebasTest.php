<?php

namespace Tests\Feature;

use RuntimeException;
use Tests\TestCase;

class SeguridadDePruebasTest extends TestCase
{
    public function test_las_pruebas_usan_sqlite_en_memoria_y_nunca_postgresql(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('testing', config('app.env'));
    }

    public function test_la_guarda_aborta_si_la_conexion_no_es_sqlite(): void
    {
        config(['database.default' => 'pgsql']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Las pruebas solo pueden usar SQLite en memoria');

        $this->setUpTraits();
    }

    public function test_la_aplicacion_esta_en_espanol_y_con_la_zona_horaria_de_mexico(): void
    {
        $this->assertSame('es', app()->getLocale());
        $this->assertSame('es', config('app.fallback_locale'));
        $this->assertSame('America/Mexico_City', config('app.timezone'));
    }
}