<?php

namespace App\Services; // <-- Asegúrate que el namespace sea correcto

use App\Models\User;
use Doctrine\DBAL\Schema\Identifier;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

/**
 * Servicio para manejar la generación y verificación de códigos QR.
 */
class QrCodeService // <-- Define la clase
{
    const QR_CODE_INTERVAL = 300; // 5 minutos

    // Genera el contenido json para un codigo QR seguro
    protected function generatePayload(User $user): string|null
    {
        if ($user->qr_secret) {
            $user->update(['qr_secret' => Str::random(32)]);
            return null;
        }

        $identifier = null;

        if ($user->hasRole('estudiante') && $user->estudiante) {
            $identifier = $user->estudiante->numero_control;
        } elseif ($user->hasRole('visitante') && $user->visitante) {
            $identifier = $user->visitante->codigo_visitante;
        }

        if (!$identifier) return null; // no se puede obtener un identificador

        $currentTime = time();

        $timeStep = floor($currentTime / self::QR_CODE_INTERVAL);

        $dataForHash = $identifier . ':' . $timeStep;
        $token = hash_hmac('sha256', $dataForHash, $user->qr_secret);

        $payload = [
            'id' => $identifier,
            'ts' => $timeStep,
            'tkn' => $token,
            'v' => 1, // versión del payload
        ];

        return json_encode($payload);

    }

    // Genera el código QR en una imagen SVG
    public function generateSecureQrCodeSvg(User $user): string|null
    {
        $payload = $this->generatePayload($user); // Generar el payload JSON

        if (!$payload) return null; // no se puede generar el QR

        // Generar el código QR usando la librería SimpleSoftwareIO\QrCode
        $qrCode = QrCode::format('svg')
            ->size(250)
            ->errorCorrection('L')
            ->generate($payload);

        return $qrCode;
    }

    // Verifica el código QR recibido
    // El payload es un JSON que contiene el identificador, timestamp, token y versión
    // El token es un hash HMAC SHA256 del identificador y el timestamp, usando el qr_secret del usuario
    // El timestamp es el tiempo actual dividido por el intervalo de 5 minutos
    public function verifyQrPayload(string $payloadJson): array
    {
        try {
            $payload = json_decode($payloadJson, true);

            if (!$payload || !isset($payload['id'], $payload['ts'], $payload['tkn'], $payload['v']) || $payload['v'] !== 1) {
                return [
                    'success' => false,
                    'error' => 'Datos (payload) invalidos o version incorrecta',
                ];
            }

            // Buscar usuario por el identificador (puede ser numero_control o codigo_visitante)
            $user = User::whereHas('estudiante', fn($q) => $q->where('numero_control', $payload['id']))
                ->orWhereHas('visitante', fn($q) => $q->where('codigo_visitante', $payload['id']))
                ->with(['estudiante', 'visitante']) // Cargar perfiles
                ->first();

            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Identificador no encontrado',
                ];
            }

            if (!$user->is_active) {
                return [
                    'success' => false,
                    'error' => 'Usuario inactivo',
                    'user_data' => $this->getUserData($user),
                ];
            }

            if (!$user->qr_secret) {
                return [
                    'success' => false,
                    'error' => 'Usuario sin qr_secret configurado',
                ];
            }

            $currentTime = time(); // Obtener el tiempo actual
            $currentTimeStep = floor($currentTime / self::QR_CODE_INTERVAL); // Calcular el intervalo actual
            $receivedTimeStep = $payload['ts']; // Obtener el intervalo del payload

            $validTokenFound = false; // Variable para verificar si el token es válido

            // Verificar el token en el intervalo actual y el anterior (tolerancia)
            foreach ([$currentTimeStep, $currentTimeStep - 1] as $tsToVerify) {
                $dataForHash = $payload['id'] . ':' . $tsToVerify; // Crear el hash para el intervalo actual
                $expectedToken = hash_hmac('sha256', $dataForHash, $user->qr_secret); // Generar el token esperado usando hmac, dataForHash y qr_secret

                if (hash_equals($expectedToken, $payload['tkn'])) { // Comparar el token esperado con el recibido
                    if ($receivedTimeStep == $tsToVerify) { // Verificar si el intervalo coincide
                        $validTokenFound = true; // Token válido encontrado
                        break;
                    }
                }
            }

            if ($validTokenFound) { // Si el token es válido
                return [
                    'success' => true,
                    'user_data' => $this->getUserData($user), // Obtener datos del usuario
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Token inválido o expirado',
                ];
            }

        } catch(\Exception $e) {
            Log::error('QrCodeService: Error verificado QR: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno al verificar el código QR',
            ];
        }
    }

    // Obtener datos relevante del usuario para mostrar despues de la verificación
    private function getUserData(User $user): array
    {
        $data = [
            'name' => $user->name,
            'apellido_paterno' => $user->apellido_paterno,
            'apellido_materno' => $user->apellido_materno,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'ruta_foto_perfil' => $user->ruta_foto_perfil ? asset('storage/profile_photos/' . $user->ruta_foto_perfil) : null,
        ];

        if ($user->hasRole('estudiante') && $user->estudiante) {
            $data['type'] = 'estudiante';
            $data['numero_control'] = $user->estudiante->numero_control;
            $data['carrera'] = $user->estudiante->carrera;
            $data['semestre'] = $user->estudiante->semestre;
        } elseif ($user->hasRole('visitante') && $user->visitante) {
            $data['type'] = 'visitante';
            $data['codigo_visitante'] = $user->visitante->codigo_visitante;
            $data['institucion_origen'] = $user->visitante->institucion_origen;
            $data['fecha_emision'] = $user->visitante->fecha_emision->format('Y-m-d H:i');
        }

        return $data;
    }

} // Fin de la clase
