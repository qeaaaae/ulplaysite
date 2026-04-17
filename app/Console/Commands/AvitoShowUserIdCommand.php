<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Avito\AvitoClient;
use Illuminate\Console\Command;

class AvitoShowUserIdCommand extends Command
{
    protected $signature = 'avito:show-user-id';

    protected $description = 'Показать user_id Avito для текущих client_id/client_secret (через GET /core/v1/accounts/self)';

    public function handle(AvitoClient $client): int
    {
        $this->info('Получаю user_id Avito...');
        try {
            $id = $client->fetchAuthenticatedUserId();
        } catch (\Throwable $e) {
            $this->error('Ошибка: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->line('AVITO_USER_ID = ' . $id);

        return self::SUCCESS;
    }
}

