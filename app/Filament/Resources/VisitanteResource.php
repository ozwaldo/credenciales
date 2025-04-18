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
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Auth;

class VisitanteResource extends Resource
{
    protected static ?string $model = Visitante::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos Personales')
                    ->icon('heroicon-o-user')
                    ->columns(2) // Dos columnas
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
                                'M' => 'Mujer',
                                'H' => 'Hombre',
                                'O' => 'Otro',
                            ])
                            ->label('Género'),
                    ]),
                Section::make('Información Institucional')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\TextInput::make('institucion_origen')
                            ->required()
                            ->maxLength(255)
                            ->label('Institución de Origen'),
                    ]),
                Section::make('Foto de Perfil')
                    ->schema([
                        Forms\Components\FileUpload::make('user.ruta_foto_perfil')
                            ->label('Foto de Perfil')
                            ->image()
                            ->disk('public')
                            ->directory('profile_photos_visitantes')
                            ->imageEditor() // Permite editar la imagen
                            ->nullable(),
                    ]),
                Section::make('Información de la Cuenta')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('user.email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->label('Correo Electrónico'),
                        Forms\Components\TextInput::make('user.password')
                            ->password()
                            ->label('Contraseña')
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create'),
                    ]),
                Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('user.is_active')
                            ->required()
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(true)
                            ->label('Activo'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.ruta_foto_perfil')
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
                Tables\Columns\TextColumn::make('user.genero')->searchable()->sortable()->label('Género')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'M' => 'Mujer',
                        'H' => 'Hombre',
                        'O' => 'Otro',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    public static function canViewAny(): bool
    {
        return Auth::user()->can('manage visitantes');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('manage visitantes');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }


}
