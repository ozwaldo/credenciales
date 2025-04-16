<?php

namespace App\Filament\Resources\EstudianteResource\Pages;

use App\Filament\Resources\EstudianteResource;
use App\Imports\EstudiantesImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


class ListEstudiantes extends ListRecords
{
    protected static string $resource = EstudianteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('importEstudiantes')
                ->label('Importar Estudiantes')
                ->action('importEstudiantes')
                ->color('primary')
                //->icon('heroicon-o-upload'),
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Archivo CSV/Excel')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ])
                ->action(function (array $data) {

                    $fileName = $data['attachment']; // ej: 01JRYSBAAR3SYJ5PE1JHDT7F31.csv
                    $import = new EstudiantesImport(); // Usa la versión SIMPLIFICADA

                    // --- Disco y Ruta CORREGIDOS ---
                    // Basado en tu observación, el archivo está en el disco 'public'
                    $diskName = 'public'; // <-- ¡CAMBIO IMPORTANTE!
                    // Como se guarda directamente en public/storage (físicamente storage/app/public)
                    // la ruta relativa DENTRO de ese disco es solo el nombre del archivo.
                    $correctRelativePath = $fileName;
                    $disk = Storage::disk($diskName);
                    // ------------------------------


                    // --- VERIFICACIÓN en disco 'public' ---
                    Log::channel('single')->info('===== Verificando Archivo en Disco (Intento 3 - Disco Public) =====');
                    Log::channel('single')->info('Nombre/Ruta recibida ($fileName): ' . $fileName);
                    Log::channel('single')->info('Disco de almacenamiento a verificar: ' . $diskName);
                    Log::channel('single')->info('Ruta relativa a verificar ($correctRelativePath): ' . $correctRelativePath);
                    $fileExistsOnDisk = $disk->exists($correctRelativePath);
                    Log::channel('single')->info('¿Existe archivo [' . $correctRelativePath . '] en disco [' . $diskName . ']?: ' . ($fileExistsOnDisk ? 'Sí' : 'No'));
                    Log::channel('single')->info('===== Fin verificación Archivo en Disco =====');
                    // ---------------------------------------


                    if (!$fileExistsOnDisk) {
                        Log::channel('single')->error('Filament Action: El archivo subido no se encontró en el disco "public". Abortando.', ['path_checked' => $correctRelativePath]);
                        Notification::make() /* ... (error) ... */
                            ->send();
                        return;
                    }

                    // --- LOG ANTES DE IMPORTAR ---
                    Log::channel('single')->info('Filament Action: intentando importar archivo con Excel::import(clase, rutaRelativa, nombreDisco).');
                    // ----------------------------
                    try {
                        // --- Pasar la ruta RELATIVA y el NOMBRE DEL DISCO ('public') ---
                        Excel::import($import, $correctRelativePath, $diskName);
                        // --------------------------------------------------------------


                        $failures = $import->getFailures();

                        Log::channel('single')->info('Filament Action: Proceso de impartar finalizado.', [
                            'failure_count' => count($failures)
                        ]);

                        if (!empty($failures)) {

                            Log::channel('single')->warning('Filament Action: Importación completada con errores de validación.', ['failures' => $failures]);


                            // Informar sobre errores
                            $errorList = "";
                            foreach ($failures as $failure) {
                                $errorList .= "<li>Fila {$failure->row()}: " .
                                    implode(", ", $failure->errors()) .
                                    " (Valor: " . json_encode($failure->values()) . ")</li>";
                            }
                            Notification::make()
                                ->title('Importación competada con errores')
                                ->danger()
                                ->body("Se encontraron errores en las siguientes filas: <ul>$errorList</ul>")
                                ->persistent()
                                ->send();
                        } else {

                            Log::channel('single')->info('Filament Action: Importación completada exitosamente (SkipsOnFailure no reportó fallas de validación).');

                            Notification::make()
                                ->title('Importación completada con éxito')
                                ->success()
                                ->body('Todos los estudiantes han sido importados exitosamente.')
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        // Manejo de errores
                        Log::channel('single')->error('Filament Action: EXCEPTION durante Excel::import() process.', [
                            'disk' => $diskName,
                            'relative_path' => $correctRelativePath,
                            'exception_message' => $e->getMessage(),
                            'trace' => $e->getTraceAsString() // Cuidado: largo
                        ]);

                        Notification::make()
                            ->title('Error al importar')
                            ->danger()
                            ->body('Ocurrió un error al importar el archivo: ' . $e->getMessage())
                            ->send();
                    }
                })
        ];
    }
}
