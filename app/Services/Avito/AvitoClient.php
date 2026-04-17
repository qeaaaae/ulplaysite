<?php

declare(strict_types=1);

namespace App\Services\Avito;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class AvitoClient
{
    private const CACHE_KEY = 'avito.access_token';

    public function getAccessToken(): string
    {
        // Если scopes в env поменялись — сбрасываем кеш токена, чтобы не использовать старый access_token.
        $scopeFingerprint = sha1((string) config('avito.scopes', ''));
        $cacheKey = self::CACHE_KEY . '.' . $scopeFingerprint;

        return (string) Cache::remember($cacheKey, now()->addMinutes(55), function () {
            $clientId = (string) config('avito.client_id');
            $clientSecret = (string) config('avito.client_secret');

            if ($clientId === '' || $clientSecret === '') {
                throw new \RuntimeException('Не заполнены AVITO_CLIENT_ID / AVITO_CLIENT_SECRET в env.');
            }

            $tokenUrl = (string) config('avito.token_url', 'https://api.avito.ru/token');

            $http = Http::timeout(20);

            if (! app()->isProduction()) {
                $http = $http->withoutVerifying();
            }

            $payload = [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ];
            $scopes = trim((string) config('avito.scopes', ''));
            if ($scopes !== '') {
                $payload['scope'] = $scopes;
            }

            // 1) Попытка отправить как JSON (часто работает)
            $res = $http
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($tokenUrl, $payload);

            $data = $this->decodeJsonResponse($res->body());

            if (! $res->ok() || ! is_array($data) || empty($data['access_token'])) {
                // 2) Фоллбек: многие OAuth серверы ожидают form-urlencoded
                $res2 = $http->asForm()->post($tokenUrl, $payload);
                $data2 = $this->decodeJsonResponse($res2->body());

                $accessToken = is_array($data2) ? ($data2['access_token'] ?? null) : null;
                if (is_string($accessToken) && $accessToken !== '') {
                    return $accessToken;
                }

                // Дай подробности первой попытки + второй, чтобы понять формат/ошибку
                $firstBody = $res->body();
                $secondBody = $res2->body();
                $status1 = $res->status();
                $status2 = $res2->status();

                throw new \RuntimeException(
                    "Avito token error: access_token not found. HTTP {$status1} body: {$firstBody} | retry HTTP {$status2} body: {$secondBody}"
                );
            }

            $accessToken = $data['access_token'] ?? null;
            if (! is_string($accessToken) || $accessToken === '') {
                throw new \RuntimeException('Avito token response: access_token not found.');
            }

            return $accessToken;
        });
    }

    /**
     * @return mixed[]|null
     */
    private function decodeJsonResponse(string $body): ?array
    {
        $body = trim($body);
        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    public function authorizedClient(): PendingRequest
    {
        $http = Http::timeout(25);

        // В некоторых Windows окружениях CA chain может быть неполной.
        // Для локальной разработки отключаем валидацию.
        if (! app()->isProduction() || (bool) config('avito.ssl_verify', true) === false) {
            $http = $http->withoutVerifying();
        }

        return $http->withToken($this->getAccessToken());
    }

    public function fetchAccountActiveItems(string $userId): array
    {
        $apiBase = rtrim((string) config('avito.api_base', 'https://api.avito.ru'), '/');

        // В документации встречаются оба варианта: /items/ и /items (у некоторых API слэш критичен).
        $candidates = [
            $apiBase . '/core/v1/accounts/' . $userId . '/items',
            $apiBase . '/core/v1/accounts/' . $userId . '/items/',
        ];

        $debug = [];

        foreach ($candidates as $url) {
            $res = $this->authorizedClient()->get($url);
            $debug[] = [
                'url' => $url,
                'status' => $res->status(),
                'body' => $this->safeTrimBody($res->body()),
            ];
            if ($res->ok()) {
                $data = $res->json();
                if (is_array($data)) {
                    return array_merge($data, ['_debug' => $debug]);
                }
            }
        }

        // Фоллбек: общий endpoint со статусами и пагинацией (обычно возвращает объявления авторизованного аккаунта)
        // GET /core/v1/items?per_page=100&page=N&status=active
        $items = [];
        $perPage = 100;
        $page = 1;

        while (true) {
            $url = $apiBase . '/core/v1/items';
            $res = $this->authorizedClient()->get($url, [
                'per_page' => $perPage,
                'page' => $page,
                'status' => 'active',
            ]);

            $debug[] = [
                'url' => $url,
                'status' => $res->status(),
                'query' => ['per_page' => $perPage, 'page' => $page, 'status' => 'active'],
                'body' => $this->safeTrimBody($res->body()),
            ];

            if (! $res->ok()) {
                throw new \RuntimeException('Avito items error: ' . $res->body());
            }

            $data = $res->json();
            if (! is_array($data)) {
                throw new \RuntimeException('Avito items response is not JSON object.');
            }

            $pageItems = $data['items'] ?? $data['resources'] ?? null;
            if (! is_array($pageItems) || $pageItems === []) {
                break;
            }

            foreach ($pageItems as $it) {
                if (is_array($it)) {
                    $items[] = $it;
                }
            }

            if (count($pageItems) < $perPage) {
                break;
            }

            $page++;
            if ($page > 200) { // safety guard
                break;
            }
        }

        return [
            'items' => $items,
            '_debug' => $debug,
        ];
    }

    private function safeTrimBody(string $body, int $limit = 2000): string
    {
        $body = trim($body);
        if ($body === '') {
            return '';
        }
        if (mb_strlen($body, 'UTF-8') <= $limit) {
            return $body;
        }
        return mb_substr($body, 0, $limit, 'UTF-8') . '…';
    }

    public function fetchAuthenticatedUserId(): int
    {
        $apiBase = rtrim((string) config('avito.api_base', 'https://api.avito.ru'), '/');
        $url = $apiBase . '/core/v1/accounts/self';

        $res = $this->authorizedClient()->get($url);
        if (! $res->ok()) {
            throw new \RuntimeException('Avito accounts/self error: ' . $res->body());
        }

        $data = $res->json();
        $id = $data['id'] ?? null;
        if (! is_int($id)) {
            // на некоторых ответах могут прилетать строки
            if (is_numeric($id)) {
                return (int) $id;
            }
            throw new \RuntimeException('Avito accounts/self response: id not found.');
        }

        return $id;
    }

    /**
     * Детали объявления (часто содержат imagesUrls и др. полей, которых нет в списке GET /core/v1/items).
     *
     * @return array<string,mixed>|null
     */
    public function fetchAccountItemDetails(int|string $userId, int|string $itemId): ?array
    {
        $apiBase = rtrim((string) config('avito.api_base', 'https://api.avito.ru'), '/');
        $uid = rawurlencode((string) $userId);
        $iid = rawurlencode((string) $itemId);

        $merged = [];

        foreach ([
            $apiBase . '/core/v1/accounts/' . $uid . '/items/' . $iid . '/',
            $apiBase . '/core/v1/accounts/' . $uid . '/items/' . $iid,
            $apiBase . '/core/v1/items/' . $iid,
            $apiBase . '/core/v1/items/' . $iid . '/',
        ] as $url) {
            $res = $this->authorizedClient()->get($url);
            if (! $res->ok()) {
                continue;
            }
            $data = $res->json();
            if (is_array($data) && $data !== []) {
                $merged = array_merge($merged, $data);
            }
        }

        return $merged === [] ? null : $merged;
    }
}

