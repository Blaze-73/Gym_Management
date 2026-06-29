<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'birth_date',
        'fitness_goal',
        'measurement_unit',
        'height_cm',
        'weight_kg',
        'workout_reminders',
        'nutrition_alerts',
        'system_updates',
        'attendance_qr_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'attendance_qr_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'workout_reminders' => 'boolean',
            'nutrition_alerts' => 'boolean',
            'system_updates' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function userPrograms(): HasMany
    {
        return $this->hasMany(UserProgram::class);
    }

    public function userWorkouts(): HasMany
    {
        return $this->hasMany(UserWorkout::class);
    }

    public function nutritionLogs(): HasMany
    {
        return $this->hasMany(NutritionLog::class);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    public function coach(): HasOne
    {
        return $this->hasOne(Coach::class);
    }

    public function assignedCoach(): HasMany
    {
        return $this->hasMany(UserCoach::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isCoach(): bool
    {
        return $this->role === 'coach';
    }

    public function ensureAttendanceQrToken(): string
    {
        if ($this->attendance_qr_token) {
            return $this->attendance_qr_token;
        }

        do {
            $token = bin2hex(random_bytes(16));
        } while (self::where('attendance_qr_token', $token)->exists());

        $this->forceFill(['attendance_qr_token' => $token])->save();

        return $token;
    }
}
