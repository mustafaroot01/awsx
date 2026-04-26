<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $categoryLabels = [
            'life'             => 'تأمين الحياة',
            'group_health'     => 'الصحي الجماعي',
            'general_property' => 'الممتلكات العامة',
        ];

        $categories = $this->categories->map(fn($cat) => [
            'id'           => $cat->id,
            'category'     => $cat->category,
            'categoryLabel'=> $categoryLabels[$cat->category] ?? $cat->category,
            'targetAmount' => (float) $cat->target_amount,
        ]);

        $branchTargets = $this->branchTargets->map(fn($bt) => [
            'id'           => $bt->id,
            'branchId'     => $bt->branch_id,
            'branchName'   => $bt->branch?->name,
            'category'     => $bt->category,
            'categoryLabel'=> $categoryLabels[$bt->category] ?? $bt->category,
            'targetAmount' => (float) $bt->target_amount,
        ]);

        return [
            'id'           => $this->id,
            'year'         => $this->year,
            'title'        => $this->title,
            'totalAmount'  => (float) $this->total_amount,
            'isLocked'     => $this->is_locked,
            'categories'   => $categories,
            'branchTargets'=> $branchTargets,
            'createdAt'    => $this->created_at?->format('Y-m-d'),
        ];
    }
}
