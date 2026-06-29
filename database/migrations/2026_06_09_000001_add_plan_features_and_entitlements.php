<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'tag')) {
                $table->string('tag', 50)->nullable()->after('duration');
            }
            if (!Schema::hasColumn('plans', 'period')) {
                $table->string('period', 20)->default('month')->after('tag');
            }
            if (!Schema::hasColumn('plans', 'popular')) {
                $table->boolean('popular')->default(false)->after('period');
            }
            if (!Schema::hasColumn('plans', 'features')) {
                $table->json('features')->nullable()->after('popular');
            }
            if (!Schema::hasColumn('plans', 'entitlements')) {
                $table->json('entitlements')->nullable()->after('features');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            foreach (['tag', 'period', 'popular', 'features', 'entitlements'] as $col) {
                if (Schema::hasColumn('plans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
