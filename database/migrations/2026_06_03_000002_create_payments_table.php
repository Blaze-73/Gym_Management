<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments')) {
            return;
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['plan', 'store']);
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->string('paypal_order_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('transaction_id');
            $table->unique('paypal_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
