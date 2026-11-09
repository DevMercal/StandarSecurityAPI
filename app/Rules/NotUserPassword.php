<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Support\Facades\Hash; 

class NotUserPassword implements ValidationRule
{
    /**
     * Inyectamos el usuario a evaluar a través del constructor.
     */
    public function __construct(protected mixed $user)
    {
    }
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Usamos el usuario inyectado
        $targetUser = $this->user;

        // Si por alguna razón no hay usuario, no podemos validar historial
        if (!$targetUser) {
            return;
        }

        // Buscamos en el historial del usuario editado
        $histories = $targetUser->passwordHistories()
            ->latest()
            ->take(5)
            ->get();

        foreach ($histories as $history) {
            // Comparamos el texto plano enviado contra los hashes del historial
            if (Hash::check($value, $history->password)) {
                $fail('Esta contraseña ya ha sido utilizada por el usuario anteriormente.');
                return; // Detener validación al encontrar coincidencia
            }
        }
    }
}
