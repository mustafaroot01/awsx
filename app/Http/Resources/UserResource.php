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
        $superAdmins = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com'];
        $hasSuperRole = $this->roles->where('name', 'إدارة النظام')->first();
        
        $roleName = (in_array($this->email, $superAdmins) || $hasSuperRole)
            ? 'إدارة النظام' 
            : ($this->roles->pluck('name')->first() ?? 'بدون مجموعة');

        return [
            'id'        => $this->id,
            'fullName'  => $this->name,
            'email'     => $this->email,
            'role'      => $roleName,
            'roles'     => $this->roles->map(fn($role) => ['id' => $role->id, 'name' => $role->name]),
            'status'    => 'active',
            'avatar'    => null,
            'branch_id' => $this->branch_id,
            'branch'    => $this->branch?->name,
            'currentPlan' => 'Basic',
        ];
    }
}
