<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'location',
        'excerpt',
        'description',
        'starts_at',
        'budget_allocated',
        'capacity',
        'participants_count',
        'cover_image_path',
        'registration_enabled',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'budget_allocated' => 'decimal:2',
            'registration_enabled' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('starts_at');
    }
}
