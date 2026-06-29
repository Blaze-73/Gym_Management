<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalService
{
    protected string $baseUrl;

    public function __construct()
    {
        $mode = strtolower(config('services.paypal.mode', 'sandbox'));
        $this->baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function isMockMode(): bool
    {
        return filter_var(config('services.paypal.mock', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function hasCredentials(): bool
    {
        return $this->clientId() !== '' && $this->secret() !== '';
    }

    public function clientId(): string
    {
        return trim((string) config('services.paypal.client_id', ''));
    }

    public function secret(): string
    {
        return trim((string) config('services.paypal.secret', ''));
    }

    public function mode(): string
    {
        return strtolower(config('services.paypal.mode', 'sandbox'));
    }

    public function clearTokenCache(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    protected function tokenCacheKey(): string
    {
        return 'paypal_access_token_' . md5($this->clientId() . '|' . $this->mode());
    }

    public function verifyConnection(): array
    {
        if ($this->isMockMode()) {
            return [
                'ok' => false,
                'message' => 'PAYPAL_MOCK is enabled. Set PAYPAL_MOCK=false in .env to use real PayPal Sandbox.',
            ];
        }

        if (!$this->hasCredentials()) {
            return [
                'ok' => false,
                'message' => 'PAYPAL_CLIENT_ID and PAYPAL_SECRET are missing in .env',
            ];
        }

        try {
            $this->clearTokenCache();
            $token = $this->getAccessToken();
            return [
                'ok' => true,
                'message' => 'Connected to PayPal ' . $this->mode() . ' API successfully.',
                'api_base' => $this->baseUrl,
                'client_id_prefix' => substr($this->clientId(), 0, 8) . '...',
                'token_received' => !empty($token),
            ];
        } catch (RuntimeException $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'api_base' => $this->baseUrl,
            ];
        }
    }

    public function getAccessToken(): string
    {
        if ($this->isMockMode()) {
            throw new RuntimeException(
                'PayPal mock mode is on. Set PAYPAL_MOCK=false in .env and add Sandbox credentials from developer.paypal.com'
            );
        }

        if (!$this->hasCredentials()) {
            throw new RuntimeException(
                'PayPal credentials missing. In .env set PAYPAL_CLIENT_ID and PAYPAL_SECRET from ' .
                'developer.paypal.com → Apps & Credentials → Sandbox → your app. Then run: php artisan paypal:verify'
            );
        }

        return Cache::remember($this->tokenCacheKey(), 3000, function () {
            $response = Http::timeout(30)
                ->asForm()
                ->withBasicAuth($this->clientId(), $this->secret())
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                $detail = $body['error_description'] ?? $body['error'] ?? $response->body();
                throw new RuntimeException(
                    "PayPal authentication failed ({$this->mode()}). " .
                    "Use Sandbox Client ID + Secret when PAYPAL_MODE=sandbox. Details: {$detail}"
                );
            }

            return $response->json('access_token');
        });
    }

    public function createCheckoutOrder(
        float $amount,
        string $description,
        string $returnUrl,
        string $cancelUrl,
        string $referenceId
    ): array {
        if ($this->isMockMode()) {
            throw new RuntimeException('Mock mode disabled for real checkout. Set PAYPAL_MOCK=false.');
        }

        $currency = config('services.paypal.currency', 'USD');
        $token = $this->getAccessToken();

        $response = Http::timeout(30)
            ->withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $referenceId,
                        'description' => mb_substr($description, 0, 127),
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'brand_name' => config('app.name', 'Gym Management'),
                    'landing_page' => 'NO_PREFERENCE',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ],
            ]);

        if (!$response->successful()) {
            $detail = $response->json('message')
                ?? collect($response->json('details') ?? [])->pluck('description')->implode('; ')
                ?? $response->body();
            throw new RuntimeException('PayPal order creation failed: ' . $detail);
        }

        $data = $response->json();
        $approvalUrl = collect($data['links'] ?? [])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$approvalUrl) {
            throw new RuntimeException('PayPal approval URL not found in API response.');
        }

        return [
            'paypal_order_id' => $data['id'],
            'approval_url' => $approvalUrl,
            'status' => $data['status'] ?? null,
        ];
    }

    public function captureOrder(string $paypalOrderId): array
    {
        if (str_starts_with($paypalOrderId, 'MOCK-')) {
            throw new RuntimeException('Cannot capture a mock order. Use real PayPal Sandbox checkout.');
        }

        $token = $this->getAccessToken();

        $response = Http::timeout(30)
            ->withToken($token)
            ->acceptJson()
            ->withHeaders(['Prefer' => 'return=representation'])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture", new \stdClass());

        if (!$response->successful()) {
            $detail = $response->json('message')
                ?? collect($response->json('details') ?? [])->pluck('description')->implode('; ')
                ?? $response->body();
            throw new RuntimeException('PayPal capture failed: ' . $detail);
        }

        return $response->json();
    }
}
