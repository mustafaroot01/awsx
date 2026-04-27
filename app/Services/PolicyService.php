<?php

namespace App\Services;

use App\Models\Policy;
use App\Models\BranchProductionTarget;
use Illuminate\Support\Facades\DB;

class PolicyService
{
    /**
     * Update policy status and handle production plan deductions.
     */
    public function updateStatus(Policy $policy, string $newStatus)
    {
        return DB::transaction(function () use ($policy, $newStatus) {
            $oldStatus = $policy->status;
            if ($oldStatus === $newStatus) {
                return $policy;
            }

            $policy->status = $newStatus;
            $policy->save();

            // Logic: Production plan achievement
            // For Life: Acceptance counts as 100% achievement
            // For others: Active counts as 100% achievement
            
            $isLife = $policy->category === 'life';
            
            // Trigger achievement update if:
            // 1. Life policy moves to 'acceptance' or 'active'
            // 2. Other policy moves to 'active'
            
            $wasCounted = ($isLife && in_array($oldStatus, ['acceptance', 'active'])) || (!$isLife && $oldStatus === 'active');
            $shouldBeCounted = ($isLife && in_array($newStatus, ['acceptance', 'active'])) || (!$isLife && $newStatus === 'active');

            if (!$wasCounted && $shouldBeCounted) {
                $this->updateProductionAchievement($policy, $policy->amount);
            } elseif ($wasCounted && !$shouldBeCounted) {
                $this->updateProductionAchievement($policy, -$policy->amount);
            }

            return $policy;
        });
    }

    /**
     * Deprecated: using updateStatus instead
     */
    public function activatePolicy(Policy $policy)
    {
        return $this->updateStatus($policy, 'active');
    }

    /**
     * Cancel/Stop a policy and pull back the amount from the production plan achievement.
     */
    public function cancelPolicy(Policy $policy)
    {
        return DB::transaction(function () use ($policy) {
            if ($policy->status === 'cancelled') {
                return $policy;
            }

            $previousStatus = $policy->status;
            $policy->status = 'cancelled';
            $policy->save();

            // If it was active, we need to subtract its amount from the achievement
            if ($previousStatus === 'active') {
                $this->updateProductionAchievement($policy, -$policy->amount);
            }

            return $policy;
        });
    }

    /**
     * Called right after a new policy is stored.
     * Adds the amount to the branch plan achievement if the initial status counts.
     */
    public function handlePolicyCreated(Policy $policy): void
    {
        if ($this->isCounted($policy->category, $policy->status)) {
            $this->updateProductionAchievement($policy, $policy->amount);
        }
    }

    /**
     * Called before a policy is permanently deleted.
     * Reverses the achievement if the policy was counted.
     */
    public function handlePolicyDeleted(Policy $policy): void
    {
        if ($this->isCounted($policy->category, $policy->status)) {
            $this->updateProductionAchievement($policy, -$policy->amount);
        }
    }

    /**
     * Called after amount or status is changed on an existing policy.
     * Reverses old contribution and applies new one atomically.
     */
    public function handlePolicyUpdated(Policy $policy, float $oldAmount, string $oldStatus): void
    {
        $wasCounted = $this->isCounted($policy->category, $oldStatus);
        $isCounted  = $this->isCounted($policy->category, $policy->status);

        DB::transaction(function () use ($policy, $oldAmount, $wasCounted, $isCounted) {
            if ($wasCounted) {
                $this->updateProductionAchievement($policy, -$oldAmount);
            }
            if ($isCounted) {
                $this->updateProductionAchievement($policy, $policy->amount);
            }
        });
    }

    /**
     * Whether a policy with the given category + status counts toward plan achievement.
     */
    private function isCounted(string $category, string $status): bool
    {
        if ($category === 'life') {
            return in_array($status, ['acceptance', 'active']);
        }
        return $status === 'active';
    }

    /**
     * Update the branch production target achievement based on the policy category.
     */
    public function updateProductionAchievement(Policy $policy, $amount)
    {
        $planCategory = Policy::PLAN_CATEGORY_MAP[$policy->category] ?? 'general_property';
        $year = $policy->issue_date->year;

        $target = BranchProductionTarget::where('branch_id', $policy->branch_id)
            ->where('category', $planCategory)
            ->whereHas('plan', function ($query) use ($year) {
                $query->where('year', $year);
            })
            ->first();

        if ($target) {
            $target->achieved_amount += $amount;
            $target->save();
        }
    }

    /**
     * Check if a Fire/Theft policy can be issued (needs approved inspection).
     */
    public function canIssuePolicy(Policy $policy): bool
    {
        if ($policy->category === 'fire_theft') {
            $hasApprovedReport = $policy->inspectionReports()
                ->where('status', 'approved')
                ->exists();
            
            return $hasApprovedReport;
        }

        return true;
    }
}
