<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class EvaluationPeriod extends Model
{
    use LogsActivity;
    protected $table = 'evaluation_periods';

    protected $fillable = [
        'year',
        'period_no',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'year'       => 'integer',
        'period_no'  => 'integer',
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
    ];

    public function branches(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'evaluation_period_branch', 'evaluation_period_id', 'branch_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'period_id');
    }

    public function getPeriodLabelAttribute(): string
    {
        $labels = [
            1 => 'يناير - فبراير',
            2 => 'مارس - أبريل',
            3 => 'مايو - يونيو',
            4 => 'يوليو - أغسطس',
            5 => 'سبتمبر - أكتوبر',
            6 => 'نوفمبر - ديسمبر',
        ];

        return $labels[$this->period_no] ?? "الفترة {$this->period_no}";
    }
}
