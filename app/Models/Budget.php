<?php

namespace App\Models;

use App\Services\ExchangeRateResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Budget extends Model
{
    protected $fillable = [
        'family_group_id',
        'category_id',
        'amount',
        'currency',
        'period',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function isAnnual(): bool
    {
        return $this->period === 'annual';
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Monto del presupuesto convertido a ARS con la tasa vigente a la fecha
     * indicada (o la más reciente si no se pasa fecha).
     */
    public function amountInArs(ExchangeRateResolver $resolver, Carbon|string|null $date = null): float
    {
        return $resolver->convert((float) $this->amount, $this->currency, $date);
    }
}
