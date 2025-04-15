<?php

namespace App\Filament\Resources\VisitanteResource\Pages;

use App\Filament\Resources\VisitanteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EditVisitante extends EditRecord
{
    protected static string $resource = VisitanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // $record es Visitante
        $user = $record->user;

        // 1. Actualizar datos del perfil del visitante
        $visitanteData = [
            //'codigo_visitante' => $data['codigo_visitante'],
            'institucion_origen' => $data['institucion_origen'],
            //'fecha_emision' => $data['fecha_emision'],
        ];
        $record->update($visitanteData);

        // 2. Actualizar datos del usuario asociado al perfil del visitante
        $userData = [
            'name' => $data['user']['name'],
            'apellido_paterno' => $data['user']['apellido_paterno'],
            'apellido_materno' => $data['user']['apellido_materno'],
            'genero' => $data['user']['genero'],
            'email' => $data['user']['email'],
            'is_active' => $data['user']['is_active'],
            'ruta_foto_perfil' => $data['user']['ruta_foto_perfil'] ?? $user->ruta_foto_perfil,
        ];

        // 3. Actualizar la contraseña si se proporciona una nueva
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $user->update($userData);

        // Asegurarse de que se tenga el rol correcto
        if (!$user->hasRole('visitante')) {
            $user->assignRole('visitante');
        }

        return $record;

    }

    // Redirigir a la lista de estudiantes después de la edición
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Cargar los datos del usuario en el formulario
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los datos del usuario asociado al perfil del estudiante
        $data['user'] = $this->record->user->toArray();

        // No llenamos el campo de contraseña
        unset($data['password']);

        return $data;
    }
}
