<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'dpto_organizador',
        'descripcion',
        'fecha_evento',
        'lugar'
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
    ];
}
