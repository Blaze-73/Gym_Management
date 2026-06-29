<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscriptions')) {
            return;
        }

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'payment_status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
