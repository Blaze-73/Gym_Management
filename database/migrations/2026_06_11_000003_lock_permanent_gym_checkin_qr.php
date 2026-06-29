<?php

use App\Models\GymCheckinSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_checkin_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('gym_checkin_settings', 'qr_url')) {
                $table->string('qr_url', 512)->nullable()->after('qr_token');
            }
        });

        $setting = GymCheckinSetting::query()->first();
        $base = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');

        if (!$setting) {
            $envToken = env('GYM_CHECKIN_TOKEN');
            $token = (is_string($envToken) && preg_match('/^[a-f0-9]{32}$/i', $envToken))
                ? strtolower($envToken)
                : bin2hex(random_bytes(16));

            GymCheckinSetting::create([
                'qr_token' => $token,
                'qr_url' => $base . '/gym-checkin?t=' . $token,
            ]);

            return;
        }

        if (!$setting->qr_url && $setting->qr_token) {
            $setting->update([
                'qr_url' => $base . '/gym-checkin?t=' . $setting->qr_token,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('gym_checkin_settings', function (Blueprint $table) {
            if (Schema::hasColumn('gym_checkin_settings', 'qr_url')) {
                $table->dropColumn('qr_url');
            }
        });
    }
};
