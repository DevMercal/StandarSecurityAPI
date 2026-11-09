<?php

namespace App\Observers;

use App\Models\AuditLogs;
use Illuminate\Database\Eloquent\Model;

class GenericObserver
{
    protected static bool $isAuditing = false;

    public function created(Model $model)
    {
        $this->log($model, 'created', null, $model->toArray());
    }

    public function updated(Model $model)
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (count($changes) > 0) {
            $oldValues = array_intersect_key($model->getOriginal(), $changes);
            $this->log($model, 'updated', $oldValues, $changes);
        }
    }

    public function deleted(Model $model)
    {
        $this->log($model, 'deleted', $model->toArray(), null);
    }

    public function restored(Model $model)
    {
        $this->log($model, 'restored', ['deleted_at' => $model->getOriginal('deleted_at')], ['deleted_at' => null]);
    }

    protected function log(Model $model, string $action, ?array $old, ?array $new)
    {
        // 1. Evitar que la tabla de logs u operaciones de auditoría activen un nuevo log
        if (static::$isAuditing || $model instanceof AuditLogs) {
            return;
        }

        if (app()->runningInConsole() && !app()->environment('testing') || !app()->environment('local')) {
            return;
        }

        static::$isAuditing = true;

        try {
            $userId = null;

            // Extraemos el ID del usuario de forma directa y segura
            if ($model instanceof \App\Models\User) {
                $userId = $model->getKey();
            } else {
                $userId = auth()->guard('api')->id() ?? auth()->id();
            }

            AuditLogs::create([
                'entity'     => get_class($model),
                'entity_id'  => $model->getKey(),
                'action'     => $action,
                'old_values' => $this->filterFields($old),
                'new_values' => $this->filterFields($new),
                'user_id'    => $userId,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            logger()->error('Error guardando auditoría: ' . $e->getMessage());
        } finally {
            static::$isAuditing = false;
        }
    }

    protected function filterFields(?array $data): ?array
    {
        if (!$data) return null;
        $exclude = ['password', 'id_time_token', 'remember_token', 'id_expiry_month'];
        return array_diff_key($data, array_flip($exclude));
    }
}
