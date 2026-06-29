<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'duration',
        'tag',
        'period',
        'popular',
        'features',
        'entitlements',
        'savings',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'popular' => 'boolean',
        'features' => 'array',
        'entitlements' => 'array',
    ];

public function membership(){
    return $this->hasMany(Membership::class);
}

public function memberships(){
    return $this->hasMany(Membership::class);
}
}
