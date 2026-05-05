<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = ['family_group_id', 'name', 'color'];

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function transactions(): MorphToMany
    {
        return $this->morphedByMany(Transaction::class, 'taggable');
    }

    public function paymentItems(): MorphToMany
    {
        return $this->morphedByMany(PaymentItem::class, 'taggable');
    }
}
