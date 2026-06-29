<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'week_start')) {
                $table->date('week_start')->nullable()->after('room');
                $table->index('week_start');
            }
        });

        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        DB::table('schedules')->whereNull('week_start')->update(['week_start' => $monday]);
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'week_start')) {
                $table->dropIndex(['week_start']);
                $table->dropColumn('week_start');
            }
        });
    }
};
