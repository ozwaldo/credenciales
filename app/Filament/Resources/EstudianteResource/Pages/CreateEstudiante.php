<?php

namespace App\Filament\Resources\EstudianteResource\Pages;

use App\Filament\Resources\EstudianteResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateEstudiante extends CreateRecord
{
    protected static string $resource = EstudianteResource::class;

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

        // Asignar el rol de estudiante
        $user->assignRole('estudiante');

        // 2. Crear el perfil del estudiante asociado al usuario
        $estudianteData = [
            'numero_control' => $data['numero_control'],
            'carrera' => $data['carrera'],
            'semestre' => $data['semestre'],
        ];

        $estudiante = $user->estudiante()->create($estudianteData);

        return $estudiante;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
