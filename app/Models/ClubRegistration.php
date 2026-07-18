<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubRegistration extends Model
{
    protected $fillable = [
        'club_id',
        'last_name',
        'first_name',
        'email',
        'phone',
        'class_name',
        'status',
        'notes',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
