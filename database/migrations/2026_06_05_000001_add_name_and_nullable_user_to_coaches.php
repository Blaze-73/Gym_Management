<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coaches')) {
            return;
        }

        if (!Schema::hasColumn('coaches', 'name')) {
            Schema::table('coaches', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
