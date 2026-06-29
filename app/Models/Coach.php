<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'specialization',
        'bio',
        'certifications',
        'experience_years',
        'clients_count',
        'rating',
        'is_available',
        'hourly_rate',
        'avatar',
        'expertise_areas',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'expertise_areas' => 'array',
        'rating' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class, 'coach_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'coach_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(UserCoach::class, 'coach_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CoachReview::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
