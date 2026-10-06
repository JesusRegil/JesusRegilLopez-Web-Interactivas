<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Torneo extends Model
{
    protected $table = 'torneos';

    protected $fillable = ['nombre', 'juego', 'fecha', 'cupo', 'descripcion', 'estado'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'inscripciones')->withTimestamps();
    }

    // Torneos disponibles: abiertos, con fecha futura y con cupo libre
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'abierto')
            ->where('fecha', '>', now())
            ->whereRaw('(select count(*) from inscripciones where inscripciones.torneo_id = torneos.id) < torneos.cupo');
    }

    public function inscritos(): int
    {
        // Usa el conteo precargado (withCount) si existe
        return $this->inscripciones_count ?? $this->inscripciones()->count();
    }

    public function plazasLibres(): int
    {
        return max(0, $this->cupo - $this->inscritos());
    }

    public function haPasado(): bool
    {
        return $this->fecha->isPast();
    }

    public function estaLleno(): bool
    {
        return $this->inscritos() >= $this->cupo;
    }

    // Regla de cerrado: marcado cerrado, fecha pasada o cupo lleno
    public function estaDisponible(): bool
    {
        return $this->estado === 'abierto' && ! $this->haPasado() && ! $this->estaLleno();
    }

    // Texto para mostrar el estado real del torneo
    public function estadoReal(): string
    {
        if ($this->haPasado()) return 'finalizado';
        if ($this->estado === 'cerrado') return 'cerrado';
        if ($this->estaLleno()) return 'lleno';
        return 'abierto';
    }
}