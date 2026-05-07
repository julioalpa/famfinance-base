<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'payment_item_id',
        'name',
        'provider',
        'discount_type',
        'discount_value',
        'currency',
        'original_amount',
        'starts_at',
        'expires_at',
        'reminder_days_before',
        'alerted_at',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'discount_value'       => 'decimal:2',
            'original_amount'      => 'decimal:2',
            'starts_at'            => 'date',
            'expires_at'           => 'date',
            'alerted_at'           => 'datetime',
            'is_active'            => 'boolean',
            'reminder_days_before' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Promotion $p) {
            if ($p->isDirty('expires_at')) {
                $p->alerted_at = null;
            }
        });
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function paymentItem(): BelongsTo
    {
        return $this->belongsTo(PaymentItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expires_at, false);
    }

    public function isExpiringSoon(): bool
    {
        $days = $this->daysUntilExpiry();
        return $days >= 0 && $days <= $this->reminder_days_before;
    }

    public function needsAlert(): bool
    {
        return $this->is_active
            && ! $this->isExpired()
            && $this->isExpiringSoon()
            && $this->alerted_at === null;
    }

    public function discountLabel(): string
    {
        if ($this->discount_type === 'percentage') {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2, ',', '.'), '0'), ',') . '%';
        }

        return '$' . number_format((float) $this->discount_value, 2, ',', '.');
    }

    public function discountedAmount(): ?string
    {
        if ($this->original_amount === null) {
            return null;
        }

        if ($this->discount_type === 'percentage') {
            $amount = (float) $this->original_amount * (1 - (float) $this->discount_value / 100);
        } else {
            $amount = (float) $this->original_amount - (float) $this->discount_value;
        }

        return number_format(max(0, $amount), 2, ',', '.');
    }
}
