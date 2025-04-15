<?php

namespace App\Observers;

use App\Models\Estudiante;

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
