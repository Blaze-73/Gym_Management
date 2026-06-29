<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','client','coach') NOT NULL DEFAULT 'client'");
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'coach')->update(['role' => 'client']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','client') NOT NULL DEFAULT 'client'");
    }
};
