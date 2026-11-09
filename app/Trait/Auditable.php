<?php

namespace App\Trait;

trait Auditable
{
    // El trait simplemente sirve de marcador/contrato para los modelos auditables.
    // Mantenemos bootAuditable vacío para evitar que se ejecute durante el ciclo 'boot' del Eloquent Model.
    public static function bootAuditable()
    {
        // Vacio intencionalmente para evitar la recursión en el boot.
    }
}
