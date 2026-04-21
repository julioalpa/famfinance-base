<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'account_id',
        'category_id',
        'description',
        'currency',
        'day_of_month',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'day_of_month' => 'integer',
        ];
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function monthlyPayments(): HasMany
    {
        return $this->hasMany(MonthlyPayment::class);
    }

    /** Último pago registrado antes del mes indicado (para pre-cargar el monto). */
    public function lastPaidAmount(int $month, int $year): ?string
    {
        $prevYear  = $month === 1 ? $year - 1 : $year;
        $prevMonth = $month === 1 ? 12 : $month - 1;

        $record = $this->monthlyPayments()
            ->where('is_paid', true)
            ->where(function ($q) use ($prevYear, $prevMonth) {
                $q->where('year', '<', $prevYear)
                  ->orWhere(fn ($q2) => $q2->where('year', $prevYear)->where('month', '<=', $prevMonth));
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        return $record?->amount;
    }
}
