<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEvento extends CreateRecord
{
    protected static string $resource = EventoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $evento = Evento::create([
            'nombre' => $data['nombre'],
            'dpto_organizador' => $data['dpto_organizador'],
            'descripcion' => $data['descripcion'],
            'fecha_evento' => $data['fecha_evento'],
            'lugar' => $data['lugar'],
        ]);

        return $evento;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
