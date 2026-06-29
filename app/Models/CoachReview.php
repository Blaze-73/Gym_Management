<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachReview extends Model
{
    protected $fillable = [
        'user_id',
        'coach_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}
