<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AuditLogs;

class UserObserver
{
    public function created(User $user): void
    {
        $user->passwordHistories()->create([
            'password' => $user->password,
        ]);

        $this->createAuditLog($user, 'created', null, $user->makeHidden(['password'])->toArray());
    }
    public function updated(User $user): void
    {
        if ($user->wasChanged('password')) {
                $user->passwordHistories()->create([
                'password' => $user->password, // Ya viene hasheado del modelo
            ]);
        }
        // --- LÓGICA DE AUDITORÍA GENERAL ---
        // Obtenemos solo los campos que cambiaron (excepto el password por seguridad)
        $changes = $user->getChanges();
        unset($changes['password']); // No queremos el hash en el log de auditoría general

        if (count($changes) > 0) {
            $oldValues = array_intersect_key($user->getOriginal(), $changes);
            
            $this->createAuditLog($user, 'updated', $oldValues, $changes);
        }
    }

    public function deleted(User $user): void
    {
        $this->createAuditLog($user, 'deleted', $user->makeHidden(['password'])->toArray(), null);
    }

    public function restored(User $user): void
    {
        //
    }
    public function forceDeleted(User $user): void
    {
        //
    }
    /**
     * Método privado para no repetir código de inserción en AuditLog
     */
    private function createAuditLog(User $user, string $action, ?array $old, ?array $new): void
    {
        $responsibleId = Auth::id() ?? $user->id;
        AuditLogs::create([
            'entity'     => User::class,
            'entity_id'  => $user->id,
            'action'     => $action,
            'old_values' => $old,
            'new_values' => $new,
            //'user_id'    => Auth::id(), // ID de quien realiza la acción
            'user_id'    => $responsibleId, // ID de quien realiza la acción
            'ip_address' => request()->ip(),
        ]);
    }

    public static function logManual(User $user, string $action, array $old, array $new): void
    {
        AuditLogs::create([
            'entity'     => User::class,
            'entity_id'  => $user->id,
            'action'     => $action,
            'old_values' => $old,
            'new_values' => $new,
            'user_id'    => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }
}
