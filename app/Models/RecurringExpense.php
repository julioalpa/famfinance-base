<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringExpense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'user_id',
        'account_id',
        'category_id',
        'description',
        'amount',
        'currency',
        'day_of_month',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'day_of_month' => 'integer',
            'is_active'    => 'boolean',
        ];
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RecurringExpenseLog::class);
    }

    public function logForMonth(int $month, int $year): ?RecurringExpenseLog
    {
        return $this->logs->first(fn($l) => $l->month === $month && $l->year === $year);
    }

    /** Days until execution this month (negative = already passed). */
    public function daysUntilThisMonth(): int
    {
        return $this->day_of_month - now()->day;
    }

    /** Execution date for the current month (clamped to last day of month). */
    public function executionDateThisMonth(): \Illuminate\Support\Carbon
    {
        $lastDay = now()->daysInMonth;
        $day     = min($this->day_of_month, $lastDay);

        return now()->startOfMonth()->addDays($day - 1);
    }
}
