<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status', 20)->default('pending')->after('status');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('subscriptions', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('subscriptions', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('subscriptions', 'billing_address')) {
                $table->text('billing_address')->nullable()->after('customer_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name', 'customer_email', 'customer_phone',
                'shipping_address', 'payment_status',
            ]);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name', 'customer_email', 'customer_phone', 'billing_address',
            ]);
        });
    }
};
