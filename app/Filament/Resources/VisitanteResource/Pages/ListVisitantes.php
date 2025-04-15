<?php

namespace App\Filament\Resources\VisitanteResource\Pages;

use App\Filament\Resources\VisitanteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVisitantes extends ListRecords
{
    protected static string $resource = VisitanteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
