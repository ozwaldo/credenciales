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
        // Log::debug('QrCodeController: Constructor called.'); // Log constructor
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
        // Log::debug('QrCodeController@show: Method entered.'); // Log entrada al método

        $user = $request->user(); // Obtener el usuario autenticado

        // Verificar si el usuario está autenticado y tiene el rol adecuado
        if (!$user || !$user->is_active || (!$user->hasRole('estudiante') && !$user->hasRole('visitante'))) {

            // Log::error('QrCodeController@show: User not authenticated.'); // Log si no hay usuario

            abort(403, 'Aceso no autorizado o usuario inactivo.');
        }

        // Log::debug('QrCodeController@show: User authenticated.', ['user_id' => $user->id, 'user_email' => $user->email]); // Log datos usuario

        try {
            // Log::debug('QrCodeController@show: Loading user relations (estudiante, visitante).', ['user_id' => $user->id]);

            $user->loadMissing(['estudiante', 'visitante']); // Cambia los nombres si tus relaciones se llaman diferente (ej. estudiante, visitante)

            // Log::debug('QrCodeController@show: Relations loaded.');
        } catch (\Throwable $e) {

            // Log::error('QrCodeController@show: Error loading relations.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            abort(500, 'Error interno al cargar datos del usuario.');
        }

        try {
            // Log::debug('QrCodeController@show: Calling QrCodeService->generateSecureQrCodeSvg()', ['user_id' => $user->id]);

            $svgContent = $this->qrCodeService->generateSecureQrCodeSvg($user);

            // Log::debug('QrCodeController@show: Returned from QrCodeService->generateSecureQrCodeSvg()');

            if (!$svgContent) {

                // Log::error('QrCodeController@show: generateSecureQrCodeSvg() returned null or empty.', ['user_id' => $user->id]);

                // Podrías retornar una imagen placeholder o un error 404/500
                abort(500, 'No se pudo generar el código QR.');
            }

            // Log::debug('QrCodeController@show: SVG content generated, length: ' . strlen($svgContent)); // Log tamaño SVG

            // Devolver respuesta
            // Log::debug('QrCodeController@show: Returning SVG response.');

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

    //     $svgContent = $this->qrCodeService->generateSecureQrCodeSvg($user); // Generar el contenido SVG del código QR

    //     // Verificar si se generó el contenido SVG
    //     // si no se generó, devolver un SVG de error
    //     if (!$svgContent) {
    //         //return response()->file(public_path('images/qr_code_error.svg'))->header('Content-Type', 'image/svg+xml');
    //         abort(500, 'No se puede generar el código QR.');
    //     }

    //     return response($svgContent)
    //             ->header('Content-Type', 'image/svg+xml')
    //             // Evitar que el navegador almacene en caché la imagen
    //             ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
    //             ->header('Pragma', 'no-cache')
    //             ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }
}
