<?php

namespace App\Http\Controllers;

use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para manejar la generación de códigos QR.
 */
class QrCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Genera y devuelve un código QR en formato SVG para el usuario autenticado.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = $request->user(); // Obtener el usuario autenticado

        // Verificar si el usuario está autenticado y tiene el rol adecuado
        if (!$user || !$user->is_active || (!$user->hasRole('estudiante') && !$user->hasRole('visitante'))) {

            abort(403, 'Aceso no autorizado o usuario inactivo.');
        }

        try {
            $user->loadMissing(['estudiante', 'visitante']); // Cambia los nombres si tus relaciones se llaman diferente (ej. estudiante, visitante)

        } catch (\Throwable $e) {


            abort(500, 'Error interno al cargar datos del usuario.');
        }

        try {

            $svgContent = $this->qrCodeService->generateSecureQrCodeSvg($user);

            if (!$svgContent) {

                // Podrías retornar una imagen placeholder o un error 404/500
                abort(500, 'No se pudo generar el código QR.');
            }

            // Devolver respuesta
            return response($svgContent)
                    ->header('Content-Type', 'image/svg+xml')
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

        } catch (\Throwable $e) { // Capturar cualquier excepción del servicio o respuesta

            // Log::error('QrCodeController@show: EXCEPTION during QR generation or response.', [
            //      'user_id' => $user->id,
            //      'error_message' => $e->getMessage(),
            //      'trace' => $e->getTraceAsString() // Cuidado: largo

            //     ]);

            abort(500, 'Error interno del servidor al generar QR.'); // Abortar explícitamente
        }

    }
}
