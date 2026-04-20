<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FamilyGroup extends Model
{
    protected $fillable = ['name', 'owner_id', 'invite_token'];

    protected static function booted(): void
    {
        static::creating(function (FamilyGroup $group) {
            $group->invite_token = Str::random(32);
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_group_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class)->orderByDesc('date');
    }

    public function latestExchangeRate(): ?ExchangeRate
    {
        return $this->exchangeRates()->first();
    }

    public function regenerateInviteToken(): void
    {
        $this->update(['invite_token' => Str::random(32)]);
    }
}
