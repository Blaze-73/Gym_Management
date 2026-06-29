<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'class_name',
        'day_of_week',
        'start_time',
        'end_time',
        'capacity',
        'room',
        'status',
        'coach_id',
        'week_start',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'coach_id' => 'integer',
        'week_start' => 'date',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }
}
