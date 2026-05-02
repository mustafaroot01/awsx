<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    protected function logActivity($action)
    {
        if (!Auth::check()) {
            return;
        }

        $recordName = $this->getRecordName();

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'subject_type' => get_class($this),
            'subject_id'   => $this->id,
            'description'  => "تم " . $this->getActionName($action) . " " . $this->getModelName() . ($recordName ? " ($recordName)" : ""),
            'properties'   => $this->getLogProperties($action),
            'ip_address'   => request()->ip(),
        ]);
    }

    protected function getRecordName()
    {
        // Try various common fields for identification
        return $this->name ?? $this->full_name ?? $this->title ?? $this->policy_no ?? $this->id;
    }

    protected function getLogProperties($action)
    {
        if ($action === 'updated') {
            return [
                'old' => array_intersect_key($this->getOriginal(), $this->getDirty()),
                'new' => $this->getDirty(),
            ];
        }

        return $this->getAttributes();
    }

    protected function getActionName($action)
    {
        return match ($action) {
            'created' => 'إضافة',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            default   => $action,
        };
    }

    protected function getModelName()
    {
        $class = class_basename($this);
        return match ($class) {
            'User'             => 'مستخدم',
            'Employee'         => 'موظف',
            'Branch'           => 'فرع',
            'Policy'           => 'وثيقة تأمين',
            'Evaluation'       => 'تقييم',
            'EvaluationPeriod' => 'فترة تقييم',
            'ProductionPlan'   => 'خطة إنتاجية',
            default            => $class,
        };
    }
}
