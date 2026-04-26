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
            'rank'         => $this->rank,
            'education'    => $this->education,
            'gender'       => $this->gender,
            'jobType'      => $this->job_type,
            'productionNo' => $this->production_no,
            'hireDate'     => $this->hire_date?->format('Y-m-d'),
            'avatar'       => $this->avatar,
            'branchId'     => $this->branch_id,
        ];
    }
}
