<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    private function datosDeRegistro(array $cambios = []): array
    {
        return array_merge([
            'name' => 'Ana Torres',
            'email' => 'ana@example.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
        ], $cambios);
    }

    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get('/recetas')->assertRedirect('/login');
        $this->get('/recetas/crear')->assertRedirect('/login');
        $this->get('/recetas/1')->assertRedirect('/login');
        $this->get('/recetas/1/editar')->assertRedirect('/login');
        $this->post('/recetas', [])->assertRedirect('/login');
        $this->put('/recetas/1', [])->assertRedirect('/login');
        $this->delete('/recetas/1')->assertRedirect('/login');
    }

    public function test_la_portada_lleva_al_recetario_y_los_invitados_terminan_en_el_login(): void
    {
        $this->get('/')->assertRedirect('/recetas');

        $this->followingRedirects()->get('/')
            ->assertOk()
            ->assertSee('Iniciar sesión');
    }

    public function test_las_pantallas_de_acceso_estan_en_espanol(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertSee('Iniciar sesión')
            ->assertSee('Correo electrónico')
            ->assertSee('Contraseña')
            ->assertSee('Regístrate');

        $this->get('/registro')
            ->assertOk()
            ->assertSee('Crear cuenta')
            ->assertSee('Nombre')
            ->assertSee('Confirmar contraseña')
            ->assertSee('Inicia sesión');
    }

    public function test_un_usuario_nuevo_se_registra_y_entra_a_un_recetario_vacio(): void
    {
        $this->post('/registro', $this->datosDeRegistro())->assertRedirect('/recetas');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['name' => 'Ana Torres', 'email' => 'ana@example.com']);
        $this->assertDatabaseCount('recetas', 0);

        $this->get('/recetas')
            ->assertOk()
            ->assertSee('Tu cuenta se creó correctamente.')
            ->assertSee('Aún no tienes recetas')
            ->assertSee('Crear mi primera receta')
            ->assertSee('Ana Torres');
    }

    public function test_el_registro_guarda_la_contrasena_con_hash_y_el_correo_en_minusculas(): void
    {
        $this->post('/registro', $this->datosDeRegistro(['email' => '  Ana.Torres@Example.COM  ']));

        $usuario = User::firstWhere('name', 'Ana Torres');

        $this->assertSame('ana.torres@example.com', $usuario->email);
        $this->assertNotSame('secreto123', $usuario->password);
        $this->assertTrue(Hash::check('secreto123', $usuario->password));
    }

    public static function registrosInvalidos(): array
    {
        return [
            'nombre vacío' => [['name' => ''], 'name', 'El campo nombre es obligatorio.'],
            'correo vacío' => [['email' => ''], 'email', 'El campo correo electrónico es obligatorio.'],
            'correo sin formato' => [['email' => 'esto-no-es-un-correo'], 'email', 'Escribe un correo electrónico válido.'],
            'contraseña vacía' => [['password' => '', 'password_confirmation' => ''], 'password', 'El campo contraseña es obligatorio.'],
            'contraseña corta' => [['password' => 'corta12', 'password_confirmation' => 'corta12'], 'password', 'El campo contraseña debe tener al menos 8 caracteres.'],
            'confirmación distinta' => [['password_confirmation' => 'otra-cosa-123'], 'password', 'La confirmación de contraseña no coincide.'],
        ];
    }

    #[DataProvider('registrosInvalidos')]
    public function test_el_registro_con_datos_invalidos_muestra_el_error_junto_al_campo_y_no_crea_la_cuenta(array $cambios, string $campo, string $mensaje): void
    {
        // El error aparece justo después del campo que lo causó.
        $this->from('/registro')
            ->followingRedirects()
            ->post('/registro', $this->datosDeRegistro($cambios))
            ->assertOk()
            ->assertSeeInOrder(['name="'.$campo.'"', $mensaje], false);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_no_se_puede_registrar_un_correo_repetido_aunque_cambien_las_mayusculas(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->from('/registro')
            ->post('/registro', $this->datosDeRegistro(['email' => 'ANA@example.com']))
            ->assertSessionHasErrors(['email' => 'Ya existe una cuenta con ese correo electrónico.']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_iniciar_sesion_con_credenciales_correctas_lleva_al_recetario(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'secreto123']);

        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'secreto123'])
            ->assertRedirect('/recetas');

        $this->assertAuthenticated();
    }

    public function test_iniciar_sesion_ignora_mayusculas_y_espacios_en_el_correo(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'secreto123']);

        $this->post('/login', ['email' => '  ANA@Example.com ', 'password' => 'secreto123'])
            ->assertRedirect('/recetas');

        $this->assertAuthenticated();
    }

    public function test_iniciar_sesion_con_credenciales_incorrectas_muestra_el_error(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'secreto123']);

        $this->from('/login')
            ->post('/login', ['email' => 'ana@example.com', 'password' => 'incorrecta'])
            ->assertRedirect('/login');

        $this->assertGuest();

        $this->get('/login')
            ->assertSeeInOrder(['name="email"', 'Estas credenciales no coinciden con nuestros registros.', 'name="password"'], false)
            ->assertSee('value="ana@example.com"', false);
    }

    public function test_el_login_exige_correo_y_contrasena(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors([
                'email' => 'El campo correo electrónico es obligatorio.',
                'password' => 'El campo contraseña es obligatorio.',
            ]);
    }

    public function test_despues_de_iniciar_sesion_se_vuelve_a_la_pagina_que_se_pedia(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'secreto123']);

        $this->get('/recetas/crear')->assertRedirect('/login');

        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'secreto123'])
            ->assertRedirect('/recetas/crear');
    }

    public function test_cerrar_sesion_devuelve_al_login_con_un_aviso(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();

        $this->get('/login')->assertSee('Sesión cerrada correctamente.');
        $this->get('/recetas')->assertRedirect('/login');
    }

    public function test_el_boton_de_cerrar_sesion_y_el_nombre_aparecen_en_el_menu(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Luis Pérez']));

        $this->get('/recetas')
            ->assertSee('Luis Pérez')
            ->assertSee('Cerrar sesión')
            ->assertSee('Mis recetas')
            ->assertSee('Nueva receta');
    }

    public function test_quien_ya_inicio_sesion_no_ve_el_login_ni_el_registro(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')->assertRedirect('/recetas');
        $this->get('/registro')->assertRedirect('/recetas');
    }

    public function test_el_inicio_de_sesion_se_limita_a_diez_intentos_por_minuto(): void
    {
        for ($intento = 1; $intento <= 10; $intento++) {
            $this->post('/login', ['email' => 'nadie@example.com', 'password' => 'incorrecta'])
                ->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => 'nadie@example.com', 'password' => 'incorrecta'])
            ->assertStatus(429)
            ->assertSee('Demasiadas solicitudes');
    }
}