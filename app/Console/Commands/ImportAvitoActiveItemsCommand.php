<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\Avito\AvitoProductMapper;
use App\Services\ImageService;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class ImportAvitoActiveItemsCommand extends Command
{
    private const DEFAULT_PRODUCT_IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    protected $signature = 'avito:import-active-items
        {--file= : Откуда читать JSON (по умолчанию storage/app/private/avito/active-items.json)}
        {--no-images : Не скачивать и не загружать картинки}
        {--max-images=5 : Макс. картинок на 1 товар}
        {--force-images : Перекачать картинки даже если у товара уже есть изображения}';

    protected $description = 'Импортировать активные объявления Avito в каталог UlPlay (Product + Images)';

    public function handle(ImageService $imageService): int
    {
        $file = (string) ($this->option('file') ?: storage_path('app/private/avito/active-items.json'));
        if (! file_exists($file)) {
            $this->error('Файл не найден: ' . $file . PHP_EOL . 'Сначала выполни: php artisan avito:fetch-active-items');
            return self::FAILURE;
        }

        $downloadImages = ! (bool) $this->option('no-images');
        $maxImages = max(0, (int) $this->option('max-images'));
        $forceImages = (bool) $this->option('force-images');

        $raw = json_decode((string) file_get_contents($file), true);
        if (! is_array($raw)) {
            $this->error('Не удалось прочитать JSON: ' . $file);
            return self::FAILURE;
        }

        $itemsPayload = $raw['raw'] ?? $raw;
        if (! is_array($itemsPayload)) {
            $this->error('Некорректная структура файла: ожидается массив в raw.');
            return self::FAILURE;
        }

        $items = $this->extractItemsArray($itemsPayload);
        if ($items === []) {
            $this->error('В файле не найдено элементов (items).');
            return self::FAILURE;
        }

        $categories = Category::query()->get(['id', 'slug', 'name', 'parent_id']);
        if ($categories->isEmpty()) {
            $this->error('В БД нет категорий. Запусти seeder категорий перед импортом.');
            return self::FAILURE;
        }

        $categoriesBySlug = $categories->pluck('id', 'slug')->all();
        $fallbackCategoryId = $categoriesBySlug['accessories'] ?? (int) ($categories->first()->id ?? 0);

        if ($fallbackCategoryId <= 0) {
            $this->error('Не удалось определить fallback category_id.');
            return self::FAILURE;
        }

        $mapper = new AvitoProductMapper($categoriesBySlug, $fallbackCategoryId);

        $this->info('Импорт элементов: ' . count($items));
        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        $apiHttp = Http::timeout(25)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
        ]);
        if (! app()->isProduction()) {
            $apiHttp = $apiHttp->withoutVerifying();
        }

        $ok = 0;
        $skipped = 0;

        foreach ($items as $listing) {
            if (! is_array($listing)) {
                $bar->advance();
                continue;
            }

            try {
                $status = $listing['status'] ?? null;
                if (is_string($status) && mb_strtolower($status, 'UTF-8') !== 'active') {
                    $bar->advance();
                    continue;
                }

                $productData = $mapper->mapListingToProductData($listing);

                if (empty($productData['title'])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                /** @var Product $product */
                $listingVideoUrl = $this->extractVideoUrl($listing);
                $product = Product::updateOrCreate(
                    ['slug' => $productData['slug']],
                    [
                        'title' => $productData['title'],
                        'slug' => $productData['slug'],
                        'avito_item_id' => $this->extractListingId($listing),
                        'avito_url' => $this->normalizeAvitoListingUrl((string) ($listing['url'] ?? '')),
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'category_id' => $productData['category_id'],
                        'in_stock' => $productData['in_stock'],
                        'stock' => $productData['stock'],
                        'discount_percent' => null,
                        'is_new' => false,
                        'is_recommended' => false,
                        'video_url' => $listingVideoUrl,
                    ],
                );

                if ($downloadImages && $maxImages > 0) {
                    $hasImages = $product->images()->count() > 0;
                    if ($forceImages || ! $hasImages) {
                        $product->images()->delete();

                        $urls = $this->extractImageUrls($listing);
                        if ($urls === [] && is_string($listing['url'] ?? null)) {
                            $media = $this->fetchListingMedia(
                                listingId: (string) ($listing['id'] ?? ''),
                                listingUrl: (string) $listing['url'],
                            );
                            $urls = $media['images'] ?? [];

                            if (($listingVideoUrl === null || $listingVideoUrl === '') && !empty($media['video_url'])) {
                                $product->video_url = (string) $media['video_url'];
                                $product->save();
                            }
                        }

                        $urls = array_values(array_filter($urls, fn ($u) => is_string($u) && trim($u) !== ''));
                        $urls = array_slice($urls, 0, $maxImages);

                        foreach ($urls as $position => $url) {
                            $url = $this->absolutizeUrl($url);
                            $storedPath = $this->storeImageFromUrl($apiHttp, $imageService, $url, 'products');
                            if ($storedPath === null) {
                                continue;
                            }

                            $product->images()->create([
                                'path' => $storedPath,
                                'is_cover' => $position === 0,
                                'position' => $position,
                            ]);
                        }
                    }
                }

                $this->ensureDefaultCoverImage($product);

                $ok++;
            } catch (\Throwable) {
                // Импортируем дальше, чтобы не стопорить всё из-за одного объявления
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Готово. Успешно: ' . $ok . ', пропущено: ' . $skipped);

        return self::SUCCESS;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<int,array<string,mixed>>
     */
    private function extractItemsArray(array $data): array
    {
        foreach (['items', 'resources', 'list', 'results', 'data'] as $key) {
            $v = $data[$key] ?? null;
            if (is_array($v)) {
                return array_values(array_filter($v, fn ($x) => is_array($x)));
            }
        }

        $maybe = array_values($data);
        return array_values(array_filter($maybe, fn ($x) => is_array($x)));
    }

    /**
     * @param array<string,mixed> $listing
     * @return array<int,string>
     */
    private function extractImageUrls(array $listing): array
    {
        // пример из доков: imagesUrls: { list: [...], listing: '...' }
        $urls = [];

        $v = $listing['imagesUrls']['list'] ?? null;
        if (is_array($v)) {
            $urls = array_merge($urls, $v);
        }

        $listingUrl = $listing['imagesUrls']['listing'] ?? null;
        if (is_string($listingUrl) && $listingUrl !== '') {
            $urls[] = $listingUrl;
        }

        // иногда приходит под другим ключом
        if (is_array($listing['images'] ?? null)) {
            foreach ($listing['images'] as $img) {
                if (is_string($img)) {
                    $urls[] = $img;
                    continue;
                }
                if (is_array($img) && isset($img['url']) && is_string($img['url'])) {
                    $urls[] = $img['url'];
                }
            }
        }

        return $urls;
    }

    private function absolutizeUrl(string $url): string
    {
        $url = trim($url);
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        return $url;
    }

    /**
     * @param array<string,mixed> $listing
     */
    private function extractVideoUrl(array $listing): ?string
    {
        foreach (['video_url', 'videoUrl', 'video'] as $key) {
            $v = $listing[$key] ?? null;
            if (is_string($v) && trim($v) !== '') {
                return trim($v);
            }
        }

        return null;
    }

    /**
     * @return array{images:array<int,string>, video_url:?string}
     */
    private function fetchListingMedia(string $listingId, string $listingUrl): array
    {
        $cacheDir = storage_path('app/private/avito/media-cache');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $safeId = $listingId !== '' ? $listingId : (string) crc32($listingUrl);
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $safeId . '.json';

        if (is_file($cacheFile)) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return [
                    'images' => is_array($cached['images'] ?? null) ? $cached['images'] : [],
                    'video_url' => is_string($cached['video_url'] ?? null) ? $cached['video_url'] : null,
                ];
            }
        }

        $http = Http::timeout(20)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
        ]);
        if (! app()->isProduction()) {
            $http = $http->withoutVerifying();
        }

        $images = [];
        $videoUrl = null;

        try {
            $res = $http->get($listingUrl);
            if ($res->ok()) {
                $html = $res->body();

                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
                libxml_clear_errors();
                $xpath = new DOMXPath($dom);

                foreach ($xpath->query('//meta[@property="og:image"]') as $node) {
                    if (!($node instanceof \DOMElement)) {
                        continue;
                    }
                    $src = trim((string) $node->getAttribute('content'));
                    if ($src !== '') {
                        $images[] = $this->absolutizeUrl($src);
                        break;
                    }
                }

                foreach (['//meta[@property="og:video:url"]', '//meta[@property="og:video"]'] as $query) {
                    foreach ($xpath->query($query) as $node) {
                        if (!($node instanceof \DOMElement)) {
                            continue;
                        }
                        $src = trim((string) $node->getAttribute('content'));
                        if ($src !== '') {
                            $videoUrl = $src;
                            break 2;
                        }
                    }
                }

                // fallback: ищем YouTube/Rutube/VK ссылки прямо в HTML
                if ($videoUrl === null) {
                    if (preg_match('~https?://(?:www\.)?youtube\.com/watch\?v=[A-Za-z0-9_\-]+~i', $html, $m)) {
                        $videoUrl = $m[0];
                    } elseif (preg_match('~https?://(?:www\.)?youtu\.be/[A-Za-z0-9_\-]+~i', $html, $m)) {
                        $videoUrl = $m[0];
                    } elseif (preg_match('~https?://(?:www\.)?rutube\.ru/video/[A-Za-z0-9_\-]+~i', $html, $m)) {
                        $videoUrl = $m[0];
                    } elseif (preg_match('~https?://(?:www\.)?vk\.com/video[-0-9_]+~i', $html, $m)) {
                        $videoUrl = $m[0];
                    }
                }
            }
        } catch (\Throwable) {
            // игнорируем — вернём пустые медиа
        }

        $payload = [
            'fetched_at' => now()->toAtomString(),
            'url' => $listingUrl,
            'images' => array_values(array_unique(array_filter($images))),
            'video_url' => $videoUrl,
        ];
        file_put_contents($cacheFile, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        return [
            'images' => $payload['images'],
            'video_url' => $payload['video_url'],
        ];
    }

    private function storeImageFromUrl($http, ImageService $imageService, string $url, string $directory): ?string
    {
        try {
            if ($url === '') {
                return null;
            }

            $res = $http->get($url);
            if (! $res->ok()) {
                return null;
            }

            $contentType = (string) ($res->header('Content-Type') ?: 'image/jpeg');

            $tmpPath = tempnam(sys_get_temp_dir(), 'avito_img_');
            if ($tmpPath === false) {
                return null;
            }

            file_put_contents($tmpPath, $res->body());

            $originalName = basename(parse_url($url, PHP_URL_PATH) ?: 'image.jpg');
            $mimeType = str_contains($contentType, '/') ? $contentType : 'image/jpeg';

            $uploaded = new UploadedFile(
                path: $tmpPath,
                originalName: $originalName,
                mimeType: $mimeType,
                test: true,
            );

            return $imageService->store($uploaded, $directory);
        } catch (\Throwable) {
            return null;
        }
    }

    private function ensureDefaultCoverImage(Product $product): void
    {
        if ($product->images()->exists()) {
            if (! $product->images()->where('is_cover', true)->exists()) {
                $firstImage = $product->images()->orderBy('position')->first();
                if ($firstImage !== null) {
                    $firstImage->update([
                        'is_cover' => true,
                        'position' => 0,
                    ]);
                }
            }

            return;
        }

        $product->images()->create([
            'path' => self::DEFAULT_PRODUCT_IMAGE,
            'is_cover' => true,
            'position' => 0,
        ]);
    }

    /**
     * @param array<string,mixed> $listing
     */
    private function extractListingId(array $listing): ?string
    {
        foreach (['id', 'item_id', 'listing_id', 'autoload_item_id'] as $key) {
            $value = $listing[$key] ?? null;
            if ($value === null) {
                continue;
            }

            $id = trim((string) $value);
            if ($id !== '') {
                return $id;
            }
        }

        return null;
    }

    private function normalizeAvitoListingUrl(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, 'http://')) {
            $raw = 'https://' . substr($raw, 7);
        } elseif (str_starts_with($raw, 'www.')) {
            $raw = 'https://' . $raw;
        } elseif (preg_match('~^avito\.ru/~i', $raw) === 1) {
            $raw = 'https://www.' . $raw;
        }

        if (! str_contains(mb_strtolower($raw, 'UTF-8'), 'avito.ru')) {
            return null;
        }

        return filter_var($raw, FILTER_VALIDATE_URL) ? $raw : null;
    }
}

