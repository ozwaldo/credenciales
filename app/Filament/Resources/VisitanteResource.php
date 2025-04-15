<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitanteResource\Pages;
use App\Filament\Resources\VisitanteResource\RelationManagers;
use App\Models\Visitante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitanteResource extends Resource
{
    protected static ?string $model = Visitante::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre'),
                Forms\Components\TextInput::make('user.apellido_paterno')
                    ->required()
                    ->maxLength(255)
                    ->label('Apellido Paterno'),
                Forms\Components\TextInput::make('user.apellido_materno')
                    ->required()
                    ->maxLength(255)
                    ->label('Apellido Materno'),
                Forms\Components\Select::make('user.genero')
                    ->required()
                    ->options([
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                        'O' => 'Otro',
                    ])
                    ->label('Género'),
                Forms\Components\TextInput::make('user.email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->label('Correo Electrónico'),
                Forms\Components\TextInput::make('user.password')
                    ->password()
                    ->label('Contraseña')
                    ->dehydrated(fn ($state) => filled($state)) // Se guarda solamente cuando se cambia la contraseña
                    ->required(fn(string $context):bool => $context === 'create'), // Requerido solo al crear un nuevo usuario
                Forms\Components\TextInput::make('institucion_origen')
                    ->required()
                    ->maxLength(255)
                    ->label('Institución de Origen'),
                Forms\Components\FileUpload::make('user.ruta_foto_perfil')
                    ->label('Foto de Perfil')
                    ->image()
                    ->disk('public')
                    ->directory('profile_photos')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.profile_photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable()->label('Nombre'),
                Tables\Columns\TextColumn::make('user.apellido_paterno')->searchable()->sortable()->label('Apellido Paterno'),
                Tables\Columns\TextColumn::make('user.apellido_materno')->searchable()->sortable()->label('Apellido Materno'),
                Tables\Columns\TextColumn::make('user.email')->searchable()->sortable()->label('Email'),
                Tables\Columns\TextColumn::make('institucion_origen')->searchable()->sortable()->label('Institución de Origen'),
                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitantes::route('/'),
            'create' => Pages\CreateVisitante::route('/create'),
            'edit' => Pages\EditVisitante::route('/{record}/edit'),
        ];
    }
}
