<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coach_reviews')) {
            return;
        }

        Schema::create('coach_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('coach_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'coach_id']);
            $table->index('coach_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_reviews');
    }
};
