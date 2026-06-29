<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gym_checkin_settings')) {
            return;
        }

        Schema::create('gym_checkin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('qr_token', 64)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_checkin_settings');
    }
};
