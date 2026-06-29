<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_deliverables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('client_user_id');
            $table->enum('type', ['message', 'program']);
            $table->string('title')->nullable();
            $table->text('body');
            $table->json('program_details')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('coach_id');
            $table->index('client_user_id');
            $table->index(['client_user_id', 'created_at']);
            $table->index(['coach_id', 'client_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_deliverables');
    }
};
