<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'codigo_visitante',
        'institucion_origen',
        'fecha_emision'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
