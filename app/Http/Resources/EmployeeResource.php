<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'employeeNo'   => $this->employee_no,
            'firstName'    => $this->first_name,
            'secondName'   => $this->second_name,
            'thirdName'    => $this->third_name,
            'fourthName'   => $this->fourth_name,
            'lastName'     => $this->last_name,
            'degree'       => $this->degree,
            'rank'           => $this->rank,
            'adminPosition'  => $this->admin_position,
            'education'      => $this->education,
            'gender'       => $this->gender,
            'jobType'      => $this->job_type,
            'jobTrack'     => $this->job_track,
            'birthDate'    => $this->birth_date?->format('Y-m-d'),
            'nationalId'   => $this->national_id,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'productionNo' => $this->production_no,
            'hireDate'     => $this->hire_date?->format('Y-m-d'),
            'avatar'       => $this->avatar,
            'branchId'     => $this->branch_id,
            'branch'       => $this->whenLoaded('branch', fn() => [
                'id'   => $this->branch?->id,
                'name' => $this->branch?->name,
            ]),
        ];
    }
}
