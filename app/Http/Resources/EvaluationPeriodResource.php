<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'year'         => $this->year,
            'periodNo'     => $this->period_no,
            'periodLabel'  => $this->period_label,
            'startDate'    => $this->start_date?->format('Y-m-d'),
            'endDate'      => $this->end_date?->format('Y-m-d'),
            'status'       => $this->status,
            'statusLabel'  => $this->status === 'locked' ? 'مقفلة' : 'مفتوحة',
            'evalCount'    => $this->evaluations_count ?? 0,
            'branchIds'    => $this->whenLoaded('branches', fn() => $this->branches->pluck('id')->toArray(), null),
        ];
    }
}
