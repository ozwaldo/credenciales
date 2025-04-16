<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditEvento extends EditRecord
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $eventoData = [
            'nombre' => $data['nombre'],
            'dpto_organizador' => $data['dpto_organizador'],
            'descripcion' => $data['descripcion'],
            'fecha_evento' => $data['fecha_evento'],
            'lugar' => $data['lugar'],
        ];

        $record->update($eventoData);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


}
