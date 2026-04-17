<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Avito\AvitoClient;
use Illuminate\Console\Command;

final class AvitoDebugItemCommand extends Command
{
    protected $signature = 'avito:debug-item
        {item_id : ID объявления Avito}
        {--user-id= : override AVITO_USER_ID}
        {--raw : Показать полные JSON-ответы}';

    protected $description = 'Диагностика Avito API по одному item_id: проверка endpoint-ов и полей изображений';

    public function handle(AvitoClient $client): int
    {
        $itemId = trim((string) $this->argument('item_id'));
        if ($itemId === '') {
            $this->error('Не передан item_id');
            return self::FAILURE;
        }

        $userId = trim((string) ($this->option('user-id') ?: config('avito.user_id', '')));
        if ($userId === '') {
            $this->line('AVITO_USER_ID не задан, пробую получить через /core/v1/accounts/self ...');
            try {
                $userId = (string) $client->fetchAuthenticatedUserId();
            } catch (\Throwable $e) {
                $this->warn('Не удалось получить user_id: ' . $e->getMessage());
                $userId = '';
            }
        }

        $this->info('Диагностика item_id=' . $itemId . ($userId !== '' ? ' user_id=' . $userId : ' user_id=<empty>'));

        $apiBase = rtrim((string) config('avito.api_base', 'https://api.avito.ru'), '/');
        $http = $client->authorizedClient();

        $urls = [];
        if ($userId !== '') {
            $urls[] = $apiBase . '/core/v1/accounts/' . rawurlencode($userId) . '/items/' . rawurlencode($itemId) . '/';
            $urls[] = $apiBase . '/core/v1/accounts/' . rawurlencode($userId) . '/items/' . rawurlencode($itemId);
        }
        $urls[] = $apiBase . '/core/v1/items/' . rawurlencode($itemId);
        $urls[] = $apiBase . '/core/v1/items/' . rawurlencode($itemId) . '/';

        $allImageUrls = [];

        foreach ($urls as $url) {
            $this->line('');
            $this->line('<comment>GET ' . $url . '</comment>');

            try {
                $res = $http->get($url);
            } catch (\Throwable $e) {
                $this->error('Request error: ' . $e->getMessage());
                continue;
            }

            $status = $res->status();
            $this->line('status: ' . $status);

            $json = $res->json();
            if (! is_array($json)) {
                $body = trim($res->body());
                $this->line('body: ' . ($body === '' ? '<empty>' : mb_substr($body, 0, 800, 'UTF-8')));
                continue;
            }

            $urlsFromPayload = $this->extractImageUrlsRecursively($json);
            $allImageUrls = array_merge($allImageUrls, $urlsFromPayload);

            $this->line('keys: ' . implode(', ', array_slice(array_keys($json), 0, 20)));
            $this->line('image urls found in this response: ' . count($urlsFromPayload));
            foreach (array_slice($urlsFromPayload, 0, 10) as $u) {
                $this->line('  - ' . $u);
            }

            if ((bool) $this->option('raw')) {
                $this->line(json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}');
            }
        }

        $allImageUrls = array_values(array_unique($allImageUrls));
        $this->line('');
        $this->info('Итого уникальных image URL: ' . count($allImageUrls));
        foreach (array_slice($allImageUrls, 0, 20) as $u) {
            $this->line('  * ' . $u);
        }

        if ($allImageUrls === []) {
            $this->warn('Фото URL не найдены ни в одном ответе API.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<int,string>
     */
    private function extractImageUrlsRecursively(array $payload): array
    {
        $urls = [];

        $walk = static function (mixed $node) use (&$walk, &$urls): void {
            if (is_string($node)) {
                if (preg_match_all('~(?:https?:)?//[0-9a-z.-]*(?:img|static|cdn)\.avito\.(?:st|ru)/[^\s"\'<>,]+~iu', $node, $m)) {
                    foreach ($m[0] as $u) {
                        $u = trim($u);
                        if (str_starts_with($u, '//')) {
                            $u = 'https:' . $u;
                        }
                        if (str_starts_with($u, 'http://')) {
                            $u = 'https://' . substr($u, 7);
                        }
                        $urls[] = rtrim($u, '.,;)]}');
                    }
                }
                return;
            }

            if (! is_array($node)) {
                return;
            }

            foreach ($node as $value) {
                $walk($value);
            }
        };

        $walk($payload);

        return array_values(array_unique(array_filter($urls)));
    }
}

