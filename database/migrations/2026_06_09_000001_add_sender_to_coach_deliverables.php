<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coach_deliverables')) {
            return;
        }

        if (!Schema::hasColumn('coach_deliverables', 'sender_user_id')) {
            Schema::table('coach_deliverables', function (Blueprint $table) {
                $table->unsignedBigInteger('sender_user_id')->nullable()->after('client_user_id');
                $table->index('sender_user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coach_deliverables', 'sender_user_id')) {
            Schema::table('coach_deliverables', function (Blueprint $table) {
                $table->dropIndex(['sender_user_id']);
                $table->dropColumn('sender_user_id');
            });
        }
    }
};
