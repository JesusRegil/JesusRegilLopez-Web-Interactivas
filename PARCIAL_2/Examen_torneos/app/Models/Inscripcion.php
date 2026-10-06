<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    protected $fillable = ['user_id', 'torneo_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }
}