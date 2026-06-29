<?php

namespace App\Services;

use App\Models\GymCheckinSetting;

class GymCheckinService
{
    /**
     * One permanent gym QR for the whole gym — stored in the database.
     * Does not change when admins log in/out. Reprint only if you run a manual DB reset.
     */
    public function setting(): GymCheckinSetting
    {
        $setting = GymCheckinSetting::query()->first();

        if ($setting) {
            return $setting;
        }

        $token = $this->resolveInitialToken();
        $url = $this->buildCheckinUrl($token);

        return GymCheckinSetting::create([
            'qr_token' => $token,
            'qr_url' => $url,
        ]);
    }

    public function token(): string
    {
        return $this->setting()->qr_token;
    }

    public function isValid(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        return hash_equals($this->token(), strtolower(trim($token)));
    }

    /** Permanent URL encoded in the printed QR (frozen at first setup). */
    public function checkinUrl(): string
    {
        $setting = $this->setting();

        if ($setting->qr_url) {
            return $setting->qr_url;
        }

        $url = $this->buildCheckinUrl($setting->qr_token);
        $setting->update(['qr_url' => $url]);

        return $url;
    }

    public function qrPayload(): string
    {
        return $this->checkinUrl();
    }

    public function qrPayloadShort(): string
    {
        return 'ALIENGYM:GYM:' . $this->token();
    }

    private function resolveInitialToken(): string
    {
        $envToken = env('GYM_CHECKIN_TOKEN');
        if (is_string($envToken) && preg_match('/^[a-f0-9]{32}$/i', $envToken)) {
            return strtolower($envToken);
        }

        do {
            $token = bin2hex(random_bytes(16));
        } while (GymCheckinSetting::where('qr_token', $token)->exists());

        return $token;
    }

    private function buildCheckinUrl(string $token): string
    {
        $base = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');

        return $base . '/gym-checkin?t=' . $token;
    }
}
