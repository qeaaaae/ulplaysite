<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Avito\AvitoClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FetchAvitoActiveItemsCommand extends Command
{
    protected $signature = 'avito:fetch-active-items
        {--file= : Куда сохранить JSON (по умолчанию storage/app/private/avito/active-items.json)}
        {--force : Перезагрузить с Avito даже если файл уже есть}
        {--user-id= : override AVITO_USER_ID}';

    protected $description = 'Забрать все активные объявления Avito аккаунта и сохранить в файл (минимум запросов к Avito)';

    public function handle(AvitoClient $client): int
    {
        $userId = (string) ($this->option('user-id') ?: config('avito.user_id'));
        if ($userId === '') {
            $this->info('AVITO_USER_ID не задан — получаю через GET /core/v1/accounts/self...');
            try {
                $userId = (string) $client->fetchAuthenticatedUserId();
            } catch (\Throwable $e) {
                $this->error('Не удалось получить user_id: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        $file = (string) ($this->option('file') ?: storage_path('app/private/avito/active-items.json'));
        $force = (bool) $this->option('force');

        if (! $force && file_exists($file)) {
            $this->info('Файл уже существует, загрузку Avito пропускаем: ' . $file);
            return self::SUCCESS;
        }

        $dir = dirname($file);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->info('Забираю активные объявления аккаунта Avito user_id=' . $userId . '...');

        $data = $client->fetchAccountActiveItems($userId);

        $payload = [
            'fetched_at' => now()->toAtomString(),
            'user_id' => $userId,
            'raw' => $data,
        ];

        file_put_contents($file, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $count = 0;
        if (is_array($data)) {
            $items = $this->extractItemsArray($data);
            $count = count($items);
        }

        $this->info('Сохранено: ' . $file);
        $this->info('Найдено элементов (approx): ' . $count);

        return self::SUCCESS;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<int,mixed>
     */
    private function extractItemsArray(array $data): array
    {
        foreach (['items', 'resources', 'list', 'results', 'data'] as $key) {
            $v = $data[$key] ?? null;
            if (is_array($v)) {
                return $v;
            }
        }

        // иногда ответ может быть просто массивом элементов
        $maybe = array_values($data);
        return array_filter($maybe, fn ($x) => is_array($x));
    }
}

