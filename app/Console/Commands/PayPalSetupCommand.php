<?php

namespace App\Console\Commands;

use App\Services\PayPalService;
use Illuminate\Console\Command;

class PayPalSetupCommand extends Command
{
    protected $signature = 'paypal:setup
                            {--client-id= : Sandbox Client ID}
                            {--secret= : Sandbox Secret}';

    protected $description = 'Save PayPal Sandbox credentials to .env and verify the connection';

    public function handle(PayPalService $paypal): int
    {
        $this->info('PayPal Sandbox setup');
        $this->line('Get credentials: https://developer.paypal.com/dashboard/applications/sandbox');
        $this->newLine();

        $clientId = $this->option('client-id') ?: $this->ask('Sandbox Client ID');
        $secret = $this->option('secret') ?: $this->secret('Sandbox Secret');

        $clientId = trim((string) $clientId);
        $secret = trim((string) $secret);

        if ($clientId === '' || $secret === '') {
            $this->error('Client ID and Secret are required.');
            return self::FAILURE;
        }

        $this->writeEnv('PAYPAL_CLIENT_ID', $clientId);
        $this->writeEnv('PAYPAL_SECRET', $secret);
        $this->writeEnv('PAYPAL_MODE', 'sandbox');
        $this->writeEnv('PAYPAL_MOCK', 'false');
        $this->writeEnv('PAYPAL_CURRENCY', 'USD');
        $this->writeEnv('FRONTEND_URL', 'http://localhost:5173');

        $this->info('Updated .env with PayPal Sandbox credentials.');
        $this->call('config:clear');

        $result = $paypal->verifyConnection();
        if ($result['ok']) {
            $this->info('✅ ' . $result['message']);
            $this->newLine();
            $this->line('Restart Laravel: php artisan serve');
            $this->line('Then test: Plans → Pay with PayPal');
            return self::SUCCESS;
        }

        $this->error('❌ ' . $result['message']);
        return self::FAILURE;
    }

    protected function writeEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            throw new \RuntimeException('.env file not found.');
        }

        $content = file_get_contents($path);
        $escaped = $value;
        if (preg_match('/\s|#/', $value)) {
            $escaped = '"' . str_replace('"', '\\"', $value) . '"';
        }

        $line = "{$key}={$escaped}";
        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content);
        } else {
            $content = rtrim($content) . "\n{$line}\n";
        }

        file_put_contents($path, $content);
    }
}
