<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Minishlink\WebPush\VAPID;
use Illuminate\Console\Command;

class GenerateVapidKeysCommand extends Command
{
    protected $signature = 'webpush:vapid';

    protected $description = 'Generate VAPID keys for Web Push notifications. Add them to .env as VAPID_PUBLIC_KEY and VAPID_PRIVATE_KEY.';

    public function handle(): int
    {
        try {
            $keys = VAPID::createVapidKeys();
        } catch (\RuntimeException $e) {
            $this->warn('PHP OpenSSL could not create EC keys (common on Windows). Trying Node.js...');
            $keys = $this->generateViaNode();
        }

        if ($keys === null) {
            $this->error('Could not generate keys. Options:');
            $this->line('1. Run: npx web-push generate-vapid-keys');
            $this->line('2. Or generate at https://vapidkeys.com and add to .env');
            return self::FAILURE;
        }

        $this->line('Add these to your .env file:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->newLine();
        $this->line('Optional: VAPID_SUBJECT=mailto:your@email.com');

        return self::SUCCESS;
    }

    /** @return array{publicKey: string, privateKey: string}|null */
    private function generateViaNode(): ?array
    {
        $output = [];
        $ret = 0;
        exec('npx web-push generate-vapid-keys 2>&1', $output, $ret);
        $text = implode("\n", $output);
        if ($ret !== 0) {
            return null;
        }
        if (preg_match('/Public Key:\s*\n([A-Za-z0-9_-]+)/', $text, $pub) && preg_match('/Private Key:\s*\n([A-Za-z0-9_-]+)/', $text, $priv)) {
            return [
                'publicKey' => trim($pub[1]),
                'privateKey' => trim($priv[1]),
            ];
        }
        return null;
    }
}
