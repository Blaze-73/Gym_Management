<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymCheckinSetting extends Model
{
    protected $fillable = ['qr_token', 'qr_url'];
}
