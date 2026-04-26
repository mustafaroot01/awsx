<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'location'    => $this->location,
            'governorate' => $this->governorate,
            'managerId'   => $this->manager_id,
            'deputyId'    => $this->deputy_id,
            'managerName' => $this->manager?->name,
            'deputyName'  => $this->deputy?->name,
        ];
    }
}
