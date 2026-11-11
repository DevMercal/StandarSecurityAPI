<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cedula' => $this->cedula,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'description_token'  => $this->timeToken?->description,
            'description_expiry' => $this->expiryMonth?->description,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name')->toArray();
            }),
            'is_active' => $this->is_active,
            //'active_jti' => $this->active_jti,
            //'session_expires_at' => $this->session_expires_at,

            'permissions' => $this->whenLoaded('roles', function () {
                return $this->getAllPermissions()->pluck('name')->unique()->sort()->values()->toArray();
            })
        ];
    }
}
