<?php

namespace App\Filament\Resources\VisitanteResource\Pages;

use App\Filament\Resources\VisitanteResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateVisitante extends CreateRecord
{
    protected static string $resource = VisitanteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // 1. Crear el usuario
        $user = User::create([
            'name' => $data['user']['name'],
            'apellido_paterno' => $data['user']['apellido_paterno'],
            'apellido_materno' => $data['user']['apellido_materno'],
            'genero' => $data['user']['genero'],
            'email' => $data['user']['email'],
            'password' => Hash::make($data['user']['password']),
            'is_active' => $data['user']['is_active'] ?? true,
            'ruta_foto_perfil' => $data['user']['ruta_foto_perfil'] ?? null,
            'email_verified_at' => now(),
            'qr_secret' => Str::random(32),
        ]);

        // Asignar el rol de visitante
        $user->assignRole('visitante');

        // 2. Crear el perfil del visitante asociado al usuario
        $visitanteData = [
            'codigo_visitante' => Str::random(8),
            'institucion_origen' => $data['institucion_origen'],
            'fecha_emision' => now(),
        ];

        $visitante = $user->visitante()->create($visitanteData);

        return $visitante;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
