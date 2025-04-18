<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

/**
 * Controlador para manejar la visualización de la credencial del usuario.
 */
class CredentialController extends Controller
{
    /**
     * Muestra la credencial del usuario autenticado.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        $user = $request->user()->loadMissing(['estudiante', 'visitante']); // Obtener el usuario autenticado

        // Verificar si el usuario está autenticado y tiene el rol adecuado
        if (!$user->is_active) {
            Auth::logout(); // Cerrar sesión del usuario
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return Redirect::route('login')->withErrors(['login_error' => __('Tu cuenta ha sido desactivada. Por favor, contacta al administrador.')]);
        }

        // Verificar si el usuario tiene el rol de estudiante o visitante
        if (!$user->hasRole(['estudiante', 'visitante'])) {
            abort(403, 'Lo siento, no tienes permiso para ver la credencial.');
        }

        // Regresar la vista de la credencial
        return view('credential.show', ['user' => $user]);
    }
}
