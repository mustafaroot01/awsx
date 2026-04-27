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

        return [
            'id'                => $this->id,
            'policyNo'          => $this->policy_no,
            'category'          => $this->category,
            'categoryLabel'     => $categoryLabels[$this->category] ?? $this->category,
            'status'            => $this->status,
            'clientName'        => $this->client_name,
            'trade_name'        => $this->trade_name,
            'permanent_address' => $this->permanent_address,
            'phone'             => $this->phone,
            'occupation'        => $this->occupation,
            'district'          => $this->district,
            'mahalla'           => $this->mahalla,
            'zuqaq'             => $this->zuqaq,
            'dar'               => $this->dar,
            'shop_no'           => $this->shop_no,
            'street_region'     => $this->street_region,
            'shop_phone'        => $this->shop_phone,
            'amount'            => (float) $this->amount,
            'issueDate'         => $this->issue_date?->format('Y-m-d'),
            'expiryDate'        => $this->expiry_date?->format('Y-m-d'),
            'branchId'          => $this->branch_id,
            'branchName'        => $this->branch?->name,
            'notes'             => $this->notes,
            'source_of_funds'   => $this->source_of_funds ? explode(',', $this->source_of_funds) : [],
            'monthly_income'    => $this->monthly_income,
            'business_type'     => $this->business_type,
            'aml_officer_name'  => $this->aml_officer_name,

            // Relations — only serialized when explicitly eager-loaded (prevents N+1)
            'lifeDetails'       => $this->whenLoaded('lifeDetails', function () {
                $d = $this->lifeDetails;
                return [
                    'paymentCycle'              => $d->payment_cycle,
                    'accidentFee'               => (float) $d->accident_fee,
                    'durationYears'             => $d->duration_years,
                    'marital_status'            => $d->marital_status,
                    'id_card_no'                => $d->id_card_no,
                    'issue_authority_date'      => $d->issue_authority_date,
                    'spouse_name'               => $d->spouse_name,
                    'work_address'              => $d->work_address,
                    'home_address_detail'       => $d->home_address_detail,
                    'height_cm'                 => $d->height_cm,
                    'weight_kg'                 => $d->weight_kg,
                    'weight_change_last_year'   => $d->weight_change_last_year,
                    'health_questionnaire'      => $d->health_questionnaire ?? array_fill(0, 7, null),
                    'idNumber'                  => $d->id_number,
                    'birthDate'                 => $d->birth_date?->format('Y-m-d'),
                    'beneficiaryName'           => $d->beneficiary_name,
                    'beneficiaryRelation'       => $d->beneficiary_relation,
                ];
            }),
            'fireTheftDetails'  => $this->whenLoaded('fireTheftDetails'),
            'companyDetails'    => $this->whenLoaded('companyDetails'),
            'beneficiaries'     => $this->whenLoaded('beneficiaries'),
            'funds'             => $this->whenLoaded('fundsSchedule'),
            'inspections'       => $this->whenLoaded('inspectionReports'),

            'createdAt'         => $this->created_at?->format('Y-m-d'),
        ];
    }
}
