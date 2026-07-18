<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'lead_name',
        'summary',
        'description',
        'budget_allocated',
        'image_path',
        'status',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'budget_allocated' => 'decimal:2',
            'is_published' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ClubRegistration::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('name');
    }
}
