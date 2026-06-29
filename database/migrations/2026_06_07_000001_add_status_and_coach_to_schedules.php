<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'status')) {
                $table->string('status', 20)->default('active')->after('room');
            }
            if (!Schema::hasColumn('schedules', 'coach_id')) {
                $table->unsignedBigInteger('coach_id')->nullable()->after('status');
                $table->index('coach_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'coach_id')) {
                $table->dropIndex(['coach_id']);
                $table->dropColumn('coach_id');
            }
            if (Schema::hasColumn('schedules', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
