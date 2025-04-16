<?php

namespace App\Observers;

use App\Models\Estudiante;
use Illuminate\Support\Facades\Storage;

class EstudianteObserver
{
    /**
     * Handle the Estudiante "created" event.
     */
    public function created(Estudiante $estudiante): void
    {
        //
    }

    /**
     * Handle the Estudiante "updated" event.
     */
    public function updated(Estudiante $estudiante): void
    {
        //
    }

    /**
     * Handle the Estudiante "deleted" event.
     */
    public function deleted(Estudiante $estudiante): void
    {
        if ($estudiante->user) {
            // Eliminar la foto de perfil si existe
            if ($estudiante->user->ruta_foto_perfil) {
                Storage::disk('public')->delete($estudiante->user->ruta_foto_perfil);
            }
            // Eliminar el usuario asociado al estudiante
            $estudiante->user->delete();
        }
    }

    /**
     * Handle the Estudiante "restored" event.
     */
    public function restored(Estudiante $estudiante): void
    {
        //
    }

    /**
     * Handle the Estudiante "force deleted" event.
     */
    public function forceDeleted(Estudiante $estudiante): void
    {
        //
    }
}
