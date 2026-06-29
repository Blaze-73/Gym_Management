<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        // Convert enum to varchar so pending / shipped / delivered work
        DB::statement("ALTER TABLE `orders` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'pending'");

        DB::table('orders')->where('status', 'processing')->update(['status' => 'shipped']);
        DB::table('orders')->where('status', 'completed')->update(['status' => 'delivered']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        DB::table('orders')->where('status', 'shipped')->update(['status' => 'processing']);
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'completed']);

        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
