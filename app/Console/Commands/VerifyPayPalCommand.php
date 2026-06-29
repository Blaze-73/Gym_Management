<?php

namespace App\Console\Commands;

use App\Services\PayPalService;
use Illuminate\Console\Command;

class VerifyPayPalCommand extends Command
{
    protected $signature = 'paypal:verify';

    protected $description = 'Test PayPal Sandbox/Live API credentials from .env';

    public function handle(PayPalService $paypal): int
    {
        $this->info('Checking PayPal configuration...');
        $this->line('  Mode: ' . $paypal->mode());
        $this->line('  Mock: ' . ($paypal->isMockMode() ? 'yes' : 'no'));
        $this->line('  Client ID set: ' . ($paypal->clientId() !== '' ? 'yes' : 'no'));
        $this->line('  Secret set: ' . ($paypal->secret() !== '' ? 'yes' : 'no'));
        $this->newLine();

        $result = $paypal->verifyConnection();

        if ($result['ok']) {
            $this->info('✅ ' . $result['message']);
            if (!empty($result['api_base'])) {
                $this->line('  API: ' . $result['api_base']);
            }
            return self::SUCCESS;
        }

        $this->error('❌ ' . $result['message']);
        $this->newLine();
        $this->line('Steps:');
        $this->line('  1. https://developer.paypal.com/dashboard/applications/sandbox');
        $this->line('  2. Create app → copy Client ID and Secret');
        $this->line('  3. Add to .env: PAYPAL_CLIENT_ID=...  PAYPAL_SECRET=...');
        $this->line('  4. Set PAYPAL_MODE=sandbox and PAYPAL_MOCK=false');
        $this->line('  5. php artisan config:clear && php artisan paypal:verify');

        return self::FAILURE;
    }
}
