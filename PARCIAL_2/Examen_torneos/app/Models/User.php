<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function torneos()
    {
        return $this->belongsToMany(Torneo::class, 'inscripciones')->withTimestamps();
    }
}