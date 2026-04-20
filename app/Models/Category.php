<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'family_group_id',
        'name',
        'icon',
        'color',
        'type',
        'is_system',
    ];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Retorna las categorías disponibles para un grupo:
     * las del sistema + las propias del grupo.
     */
    public static function availableFor(int $familyGroupId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_system', true)
            ->orWhere('family_group_id', $familyGroupId)
            ->orderBy('name')
            ->get();
    }
}
