<?php

namespace App\Http\Controllers;

use App\Services\QrCodeService;
use Illuminate\Http\Request;

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

        $user->loadMissing(['estudiante', 'visitante']); // Cargar los perfiles relacionados

        $svgContent = $this->qrCodeService->generateSecureQrCodeSvg($user); // Generar el contenido SVG del código QR

        // Verificar si se generó el contenido SVG
        // si no se generó, devolver un SVG de error
        if (!$svgContent) {
            //return response()->file(public_path('images/qr_code_error.svg'))->header('Content-Type', 'image/svg+xml');
            abort(500, 'No se puede generar el código QR.');
        }

        return response($svgContent)
                ->header('Content-Type', 'image/svg+xml')
                // Evitar que el navegador almacene en caché la imagen
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }
}
