<?php

declare(strict_types=1);

namespace App\Services\Avito;

final class AvitoCachedListingUrlLookup
{
    /**
     * @var array<string,string>|null
     */
    private ?array $urlByItemId = null;

    /**
     * Сырые элементы из active-items.json / API (id объявления → payload).
     *
     * @var array<string,array<string,mixed>>|null
     */
    private ?array $itemsByItemId = null;

    private bool $apiMapLoaded = false;

    private ?string $resolvedAccountUserId = null;

    /**
     * @var array<string, ?array<string,mixed>>
     */
    private array $accountItemDetailsCache = [];

    public function __construct(
        private readonly AvitoClient $client,
    ) {}

    /**
     * Payload для импорта фото: кэш списка + детальный GET …/accounts/{user}/items/{id}/ (там бывают imagesUrls).
     *
     * @return array<string,mixed>|null
     */
    public function getItemPayloadMergedWithAccountDetails(string $avitoItemId): ?array
    {
        $itemId = $this->normalizeAvitoItemId($avitoItemId);
        if ($itemId === '') {
            return null;
        }

        $base = $this->getActiveItemPayloadById($itemId);
        $userId = $this->resolveAccountUserId();
        if ($userId === '') {
            return $base;
        }

        if (array_key_exists($itemId, $this->accountItemDetailsCache)) {
            $details = $this->accountItemDetailsCache[$itemId];
        } else {
            try {
                $details = $this->client->fetchAccountItemDetails($userId, $itemId);
            } catch (\Throwable) {
                $details = null;
            }
            $this->accountItemDetailsCache[$itemId] = $details;
        }

        if (! is_array($details) || $details === []) {
            return $base;
        }

        if ($base === null) {
            return $details;
        }

        return array_merge($base, $details);
    }

    private function resolveAccountUserId(): string
    {
        if ($this->resolvedAccountUserId !== null) {
            return $this->resolvedAccountUserId;
        }

        $fromConfig = trim((string) config('avito.user_id', ''));
        if ($fromConfig !== '') {
            return $this->resolvedAccountUserId = $fromConfig;
        }

        $filePayload = $this->readActiveItemsFilePayload();
        if (is_array($filePayload) && isset($filePayload['user_id'])) {
            $u = trim((string) $filePayload['user_id']);
            if ($u !== '') {
                return $this->resolvedAccountUserId = $u;
            }
        }

        try {
            return $this->resolvedAccountUserId = (string) $this->client->fetchAuthenticatedUserId();
        } catch (\Throwable) {
            return $this->resolvedAccountUserId = '';
        }
    }

    public function resolveByItemId(string $avitoItemId): ?string
    {
        $itemId = $this->normalizeAvitoItemId($avitoItemId);
        if ($itemId === '') {
            return null;
        }

        $map = $this->getUrlMap();
        if (isset($map[$itemId])) {
            return $map[$itemId];
        }

        $this->loadUrlMapFromApi();
        $map = $this->getUrlMap();

        return $map[$itemId] ?? null;
    }

    public function resolveByItemIdFromCacheOnly(string $avitoItemId): ?string
    {
        $itemId = $this->normalizeAvitoItemId($avitoItemId);
        if ($itemId === '') {
            return null;
        }

        $map = $this->getUrlMap();
        return $map[$itemId] ?? null;
    }

    /**
     * Элемент из кэша/API по номеру объявления на сайте (для imagesUrls и т.п.).
     * При отсутствии в файле один раз пробует подтянуть список через API.
     *
     * @return array<string,mixed>|null
     */
    public function getActiveItemPayloadById(string $avitoItemId): ?array
    {
        $itemId = $this->normalizeAvitoItemId($avitoItemId);
        if ($itemId === '') {
            return null;
        }

        $this->ensureMapsLoaded();
        if (isset($this->itemsByItemId[$itemId])) {
            return $this->itemsByItemId[$itemId];
        }

        $this->loadUrlMapFromApi();

        return $this->itemsByItemId[$itemId] ?? null;
    }

    /**
     * @return array<string,string>
     */
    private function getUrlMap(): array
    {
        $this->ensureMapsLoaded();

        return $this->urlByItemId;
    }

    private function ensureMapsLoaded(): void
    {
        if ($this->urlByItemId !== null) {
            return;
        }

        $this->urlByItemId = [];
        $this->itemsByItemId = [];
        $this->mergeItemsIntoMap($this->extractItemsFromPayload($this->readActiveItemsFilePayload()));
    }

    private function loadUrlMapFromApi(): void
    {
        if ($this->apiMapLoaded) {
            return;
        }
        $this->apiMapLoaded = true;

        $userId = trim((string) config('avito.user_id', ''));
        if ($userId === '') {
            try {
                $userId = (string) $this->client->fetchAuthenticatedUserId();
            } catch (\Throwable) {
                return;
            }
        }

        try {
            $payload = $this->client->fetchAccountActiveItems($userId);
        } catch (\Throwable) {
            return;
        }

        $items = $this->extractItemsFromPayload($payload);
        $this->mergeItemsIntoMap($items);

        $this->storeActiveItemsFilePayload([
            'fetched_at' => now()->toAtomString(),
            'user_id' => $userId,
            'raw' => $payload,
        ]);
    }

    /**
     * @param array<int,array<string,mixed>> $items
     */
    private function mergeItemsIntoMap(array $items): void
    {
        if ($this->urlByItemId === null) {
            $this->urlByItemId = [];
        }
        if ($this->itemsByItemId === null) {
            $this->itemsByItemId = [];
        }

        foreach ($items as $item) {
            $idRaw = isset($item['id']) ? (string) $item['id'] : '';
            $itemId = $this->normalizeAvitoItemId($idRaw);
            if ($itemId === '') {
                continue;
            }

            $this->itemsByItemId[$itemId] = $item;

            $url = $this->extractListingUrl($item);
            if ($url === null) {
                continue;
            }

            $this->urlByItemId[$itemId] = $url;
        }
    }

    /**
     * @param array<string,mixed>|null $payload
     * @return array<int,array<string,mixed>>
     */
    private function extractItemsFromPayload(?array $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $itemsPayload = $payload['raw'] ?? $payload;
        if (! is_array($itemsPayload)) {
            return [];
        }

        foreach (['items', 'resources', 'list', 'results', 'data'] as $key) {
            $value = $itemsPayload[$key] ?? null;
            if (is_array($value)) {
                return array_values(array_filter($value, static fn ($v) => is_array($v)));
            }
        }

        return array_values(array_filter(array_values($itemsPayload), static fn ($v) => is_array($v)));
    }

    /**
     * @param array<string,mixed> $item
     */
    private function extractListingUrl(array $item): ?string
    {
        foreach (['url', 'itemUrl', 'item_url', 'uri', 'link'] as $key) {
            $raw = $item[$key] ?? null;
            if (! is_string($raw)) {
                continue;
            }

            $normalized = $this->normalizeAvitoUrl($raw);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    private function normalizeAvitoUrl(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, 'http://')) {
            $raw = 'https://' . substr($raw, 7);
        } elseif (str_starts_with($raw, '//')) {
            $raw = 'https:' . $raw;
        } elseif (str_starts_with($raw, '/')) {
            $raw = 'https://www.avito.ru' . $raw;
        } elseif (str_starts_with($raw, 'www.')) {
            $raw = 'https://' . $raw;
        } elseif (! str_starts_with($raw, 'https://') && ! str_starts_with($raw, 'http://')) {
            if (str_starts_with($raw, 'avito.ru/')) {
                $raw = 'https://www.' . $raw;
            } else {
                return null;
            }
        }

        if (! str_contains(mb_strtolower($raw, 'UTF-8'), 'avito.ru')) {
            return null;
        }

        return filter_var($raw, FILTER_VALIDATE_URL) ? $raw : null;
    }

    private function normalizeAvitoItemId(string $raw): string
    {
        $raw = trim(preg_replace('/\s+/u', '', $raw) ?? '');
        if ($raw === '') {
            return '';
        }

        if (is_numeric($raw)) {
            return (string) (int) (float) $raw;
        }

        return preg_replace('/\D+/u', '', $raw) ?? '';
    }

    /**
     * @return array<string,mixed>|null
     */
    private function readActiveItemsFilePayload(): ?array
    {
        $path = storage_path('app/private/avito/active-items.json');
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function storeActiveItemsFilePayload(array $payload): void
    {
        $path = storage_path('app/private/avito/active-items.json');
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}
