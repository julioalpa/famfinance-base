<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    protected $fillable = [
        'transaction_id',
        'account_id',
        'installment_number',
        'amount',
        'due_date',
        'is_paid',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount'   => 'decimal:2',
            'is_paid'  => 'boolean',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
