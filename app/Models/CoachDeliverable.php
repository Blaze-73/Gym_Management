<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachDeliverable extends Model
{
    protected $fillable = [
        'coach_id',
        'client_user_id',
        'sender_user_id',
        'type',
        'title',
        'body',
        'program_details',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'program_details' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
