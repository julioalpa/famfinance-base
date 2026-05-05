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
        'is_direct_debit',
        'amount',
        'notes',
        'is_dispensable',
        'is_retiring',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'is_direct_debit' => 'boolean',
            'is_dispensable'  => 'boolean',
            'is_retiring'     => 'boolean',
            'day_of_month'    => 'integer',
            'amount'          => 'decimal:2',
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

    /** Último monto pagado (no descartado) antes del mes indicado. */
    public function lastPaidAmount(int $month, int $year): ?string
    {
        return $this->monthlyPayments()
            ->where('is_paid', true)
            ->where('is_dismissed', false)
            ->where(function ($q) use ($year, $month) {
                $q->where('year', '<', $year)
                  ->orWhere(fn ($q2) => $q2->where('year', $year)->where('month', '<', $month));
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->value('amount');
    }
}
