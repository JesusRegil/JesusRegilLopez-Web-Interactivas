<?php

namespace Database\Seeders;

use App\Models\Torneo;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cuentas demo (ver README)
        User::create(['name' => 'Administrador', 'email' => 'admin@torneos.test', 'password' => 'admin123', 'role' => 'admin']);
        $ana = User::create(['name' => 'Ana Jugadora', 'email' => 'ana@torneos.test', 'password' => 'jugador123', 'role' => 'jugador']);
        $beto = User::create(['name' => 'Beto Jugador', 'email' => 'beto@torneos.test', 'password' => 'jugador123', 'role' => 'jugador']);

        $abierto = Torneo::create([
            'nombre' => 'Copa Relámpago de Fútbol', 'juego' => 'Fútbol', 'cupo' => 16,
            'fecha' => now()->addDays(7)->setTime(10, 0),
            'descripcion' => 'Torneo de fútbol 7 en la cancha principal.',
        ]);
        Torneo::create([
            'nombre' => 'Liga FIFA 26', 'juego' => 'Videojuegos', 'cupo' => 32,
            'fecha' => now()->addDays(14)->setTime(16, 0),
            'descripcion' => 'Torneo 1 vs 1 en consola.',
        ]);
        Torneo::create([
            'nombre' => 'Básquet 3x3', 'juego' => 'Básquetbol', 'cupo' => 12,
            'fecha' => now()->addDays(3)->setTime(18, 0),
        ]);
        $lleno = Torneo::create([
            'nombre' => 'Duelo de Ajedrez (lleno)', 'juego' => 'Ajedrez', 'cupo' => 2,
            'fecha' => now()->addDays(5)->setTime(12, 0),
            'descripcion' => 'Ya está lleno: no aparece en el listado.',
        ]);
        Torneo::create([
            'nombre' => 'Torneo de Voleibol (cerrado)', 'juego' => 'Voleibol', 'cupo' => 10,
            'fecha' => now()->addDays(9)->setTime(9, 0), 'estado' => 'cerrado',
            'descripcion' => 'Cerrado por el admin: no aparece en el listado.',
        ]);
        Torneo::create([
            'nombre' => 'Torneo pasado de Tenis', 'juego' => 'Tenis', 'cupo' => 8,
            'fecha' => now()->subDays(5),
            'descripcion' => 'Ya pasó: no aparece en el listado.',
        ]);

        foreach (['Carla', 'Diego'] as $nombre) {
            $extra = User::create(['name' => $nombre.' Jugador', 'email' => strtolower($nombre).'@torneos.test', 'password' => 'jugador123', 'role' => 'jugador']);
            $abierto->inscripciones()->create(['user_id' => $extra->id]);
        }
        $abierto->inscripciones()->create(['user_id' => $ana->id]);
        $lleno->inscripciones()->createMany([['user_id' => $ana->id], ['user_id' => $beto->id]]);
    }
}