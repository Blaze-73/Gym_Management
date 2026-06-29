<?php

namespace App\Http\Controllers;

use App\Services\PayPalService;
use Illuminate\Http\Request;

class PayPalConfigController extends Controller
{
    public function status(PayPalService $paypal)
    {
        if (!app()->environment('local')) {
            return response()->json(['configured' => false], 403);
        }

        if (!$paypal->hasCredentials()) {
            return response()->json(['configured' => false, 'verified' => false]);
        }

        $result = $paypal->verifyConnection();

        return response()->json([
            'configured' => true,
            'verified' => $result['ok'],
            'message' => $result['message'] ?? null,
        ]);
    }

    public function store(Request $request, PayPalService $paypal)
    {
        if (!app()->environment('local')) {
            return response()->json([
                'message' => 'PayPal setup via API is only allowed in local environment.',
            ], 403);
        }

        $validated = $request->validate([
            'client_id' => 'required|string|min:20|max:500',
            'secret' => 'required|string|min:20|max:500',
        ]);

        $clientId = trim($validated['client_id']);
        $secret = trim($validated['secret']);

        if (!str_starts_with($clientId, 'A') && !str_starts_with($clientId, 'sb-')) {
            return response()->json([
                'message' => 'Client ID does not look like a PayPal Sandbox ID. Use Sandbox tab at developer.paypal.com (not Live).',
            ], 422);
        }

        $this->writeEnv('PAYPAL_CLIENT_ID', $clientId);
        $this->writeEnv('PAYPAL_SECRET', $secret);
        $this->writeEnv('PAYPAL_MODE', 'sandbox');
        $this->writeEnv('PAYPAL_MOCK', 'false');
        $this->writeEnv('PAYPAL_CURRENCY', 'USD');
        $this->writeEnv('FRONTEND_URL', config('services.paypal.frontend_url') ?: 'http://localhost:5173');

        \Illuminate\Support\Facades\Artisan::call('config:clear');

        config([
            'services.paypal.client_id' => $clientId,
            'services.paypal.secret' => $secret,
            'services.paypal.mode' => 'sandbox',
            'services.paypal.mock' => false,
        ]);

        $paypal->clearTokenCache();
        $result = app(PayPalService::class)->verifyConnection();

        if (!$result['ok']) {
            return response()->json([
                'message' => $result['message'],
                'configured' => false,
            ], 422);
        }

        return response()->json([
            'message' => 'PayPal Sandbox connected successfully. You can now use Pay with PayPal.',
            'configured' => true,
        ]);
    }

    protected function writeEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        if (!is_writable($path)) {
            throw new \RuntimeException('.env file is not writable. Check file permissions.');
        }

        $content = file_get_contents($path);
        $escaped = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        $line = "{$key}={$escaped}";
        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content);
        } else {
            $content = rtrim($content) . "\n{$line}\n";
        }

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException('Failed to write to .env file.');
        }
    }
}
