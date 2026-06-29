<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'attendance_qr_token')) {
                $table->string('attendance_qr_token', 64)->nullable()->unique()->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'attendance_qr_token')) {
                $table->dropUnique(['attendance_qr_token']);
                $table->dropColumn('attendance_qr_token');
            }
        });
    }
};
