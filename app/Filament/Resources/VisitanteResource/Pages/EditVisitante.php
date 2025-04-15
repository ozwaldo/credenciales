<?php

namespace App\Filament\Resources\VisitanteResource\Pages;

use App\Filament\Resources\VisitanteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisitante extends EditRecord
{
    protected static string $resource = VisitanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
