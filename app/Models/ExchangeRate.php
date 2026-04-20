<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $fillable = [
        'family_group_id',
        'user_id',
        'from_currency',
        'to_currency',
        'rate',
        'date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'date' => 'date',
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

    /**
     * Convierte un monto usando esta tasa.
     */
    public function convert(float $amount, string $from): float
    {
        if ($from === $this->from_currency) {
            return $amount * $this->rate;
        }
        if ($from === $this->to_currency) {
            return $amount / $this->rate;
        }
        return $amount;
    }
}
