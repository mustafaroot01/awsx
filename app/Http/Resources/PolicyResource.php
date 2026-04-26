<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $categoryLabels = [
            'vehicle'           => 'تأمين السيارات',
            'fire_theft'        => 'الحريق والسرقة',
            'group_health'      => 'الصحي الجماعي',
            'transport_marine'  => 'النقل / البحري',
            'engineering'       => 'التأمين الهندسي',
            'life'              => 'تأمين الحياة',
            'personal_accident' => 'الحوادث الشخصية',
            'cash'              => 'تأمين النقد',
        ];

        $lifeDetails = null;
        if ($this->category === 'life' && $this->lifeDetails) {
            $d = $this->lifeDetails;
            $lifeDetails = [
                'paymentCycle'         => $d->payment_cycle,
                'accidentFee'          => (float) $d->accident_fee,
                'durationYears'        => $d->duration_years,
                'idNumber'             => $d->id_number,
                'birthDate'            => $d->birth_date?->format('Y-m-d'),
                'phone'                => $d->phone,
                'address'              => $d->address,
                'beneficiaryName'      => $d->beneficiary_name,
                'beneficiaryRelation'  => $d->beneficiary_relation,
            ];
        }

        return [
            'id'            => $this->id,
            'policyNo'      => $this->policy_no,
            'category'      => $this->category,
            'categoryLabel' => $categoryLabels[$this->category] ?? $this->category,
            'clientName'    => $this->client_name,
            'amount'        => (float) $this->amount,
            'issueDate'     => $this->issue_date?->format('Y-m-d'),
            'expiryDate'    => $this->expiry_date?->format('Y-m-d'),
            'branchId'      => $this->branch_id,
            'branchName'    => $this->branch?->name,
            'employeeId'    => $this->employee_id,
            'notes'         => $this->notes,
            'lifeDetails'   => $lifeDetails,
            'createdAt'     => $this->created_at?->format('Y-m-d'),
        ];
    }
}
