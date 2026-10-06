<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Los estilos no importan en las pruebas: no hace falta compilar public/build.
        $this->withoutVite();
    }

    /**
     * Guarda de seguridad. Se ejecuta ANTES de preparar la base de datos (RefreshDatabase):
     * si por error la conexión no fuera SQLite, se aborta para no tocar el PostgreSQL de desarrollo.
     */
    protected function setUpTraits()
    {
        $conexion = config('database.default');

        if (config("database.connections.{$conexion}.driver") !== 'sqlite') {
            throw new RuntimeException(
                "Las pruebas solo pueden usar SQLite en memoria (conexión actual: {$conexion}). Se aborta para no tocar PostgreSQL."
            );
        }

        return parent::setUpTraits();
    }
}