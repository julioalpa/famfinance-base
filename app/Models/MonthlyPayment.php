<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyPayment extends Model
{
    protected $fillable = [
        'payment_item_id',
        'family_group_id',
        'month',
        'year',
        'amount',
        'is_paid',
        'is_dismissed',
        'paid_at',
        'transaction_id',
        'transaction_was_created_here',
    ];

    protected function casts(): array
    {
        return [
            'amount'                       => 'decimal:2',
            'is_paid'                      => 'boolean',
            'is_dismissed'                 => 'boolean',
            'paid_at'                      => 'datetime',
            'transaction_was_created_here' => 'boolean',
        ];
    }

    public function paymentItem(): BelongsTo
    {
        return $this->belongsTo(PaymentItem::class);
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
