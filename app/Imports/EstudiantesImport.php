<?php

namespace App\Imports;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnFailure; // Para saltar filas con errores
use Maatwebsite\Excel\Validators\Failure; // Para manejar errores
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Para usar nombres de columna
use Maatwebsite\Excel\Concerns\WithValidation; // Para validar filas
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EstudiantesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{

    use Importable;

    private $failures = [];

    private static $rowCount = 0; // Contador simple para saber qué fila es

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

       // dd($row); // Para depurar y ver el contenido de la fila
        self::$rowCount++; // Incrementar contador

        Log::channel('single')->info('EstudianteImport::model() CALLED for row number (approx): ' . self::$rowCount, ['row_keys' => array_keys($row)]);

        $password = Str::random(10);

        try {
            // 1. Crear el usuario
            Log::info('EstudianteImport: Intentando crear un usuario para el correo electrónico: ' . ($row['email'] ?? 'N/A'));
            //echo "Intentando crear un usuario para el correo electrónico: " . ($row['email'] ?? 'N/A') . "\n";
            $user = User::create([
                'name' => $row['name'],
                'apellido_paterno' => $row['apellido_paterno'],
                'apellido_materno' => $row['apellido_materno'],
                'email' => $row['email'],
                'password' => Hash::make($password),
                'genero' => $row['genero'],
                'is_active' => true,
                'email_verified_at' => now(),
                'qr_secret' => Str::random(32),
            ]);

            Log::info('EstudianteImport: Usuario creado exitosamente con ID: ' . $user->id);

            Log::info('EstudianteImport: Asignando el rol "estudiante" al usuario con ID: ' . $user->id);

            $user -> assignRole('estudiante');

            Log::info('EstudianteImport: Rol asignado.');


            $estudianteData = [
                'user_id' => $user->id,
                'numero_control' => $row['numero_control'],
                'carrera' => $row['carrera'],
                'semestre' => $row['semestre'],
            ];

            Log::info('EstudianteImport: Intentando crear Estudiante para el Usuario ID: ' . $user->id, $estudianteData);

            return new Estudiante($estudianteData);

        } catch (\Exception $e) {
            // Manejar la excepción si es necesario

            Log::error('Error importando estudiante: ' . $e->getMessage(), [
                'row_data' => $row,
                'exception' => $e // Opcional: $e->getTraceAsString() para más detalle
            ]);

            return null;
        }

    }

    // Validación de los datos
    public function rules(): array
    {
        Log::channel('single')->info('EstudiantesImport::rules() CALLED.');

        return [
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'genero' => ['required', Rule::in(['M', 'H', 'O'])],
            'email' => ['required', 'email', Rule::unique(User::class,'email'), 'max:255'],
            'numero_control' => ['required', 'string', Rule::unique(Estudiante::class,'numero_control'), 'max:50'],
            'carrera' => ['required', 'string', 'max:255'],
            'semestre' => ['required', 'integer', 'min:1', 'max:16'],
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        // Almacena las fallas para poder mostrarlas después

        Log::warning('Errores de validación durante la importación:', ['Falla' => $failures]);

        $this->failures = array_merge($this->failures, $failures);
    }

    // Método para obtener las fallas
    public function getFailures(): array
    {
        return $this->failures;
    }

    // Para colas
    public function chunkSize(): int
    {
        return 1000; // numero de filas por chunk
    }
}
