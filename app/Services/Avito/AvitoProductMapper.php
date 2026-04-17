<?php

declare(strict_types=1);

namespace App\Services\Avito;

use App\Models\Category;
use Illuminate\Support\Str;

final class AvitoProductMapper
{
    /**
     * @param array<string,int> $categoriesBySlug
     */
    public function __construct(
        private readonly array $categoriesBySlug,
        private readonly int $fallbackCategoryId,
    ) {}

    /**
     * @param array<string,mixed> $listing
     * @return array{title:string, description:?string, price:float, category_id:int, slug:string, in_stock:bool, stock:int}
     */
    public function mapListingToProductData(array $listing): array
    {
        $listingId = $this->extractListingId($listing);
        $title = $this->extractTitle($listing) ?: 'Avito товар';
        $description = $this->extractDescription($listing);
        $price = $this->extractPrice($listing);
        $stock = $this->extractStock($listing);
        $inStock = $stock > 0;

        $categoryId = $this->resolveCategoryId($title, (string) $description);

        // slug уникальный, чтобы updateOrCreate не ломался при повторных прогонах после migrate:fresh
        $baseSlug = (string) Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'avito-item';
        $slug = $baseSlug . '-' . $listingId;

        return [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'category_id' => $categoryId,
            'slug' => $slug,
            'in_stock' => $inStock,
            'stock' => max(0, (int) $stock),
        ];
    }

    /**
     * @param array<string,mixed> $listing
     */
    private function extractListingId(array $listing): string
    {
        foreach (['id', 'item_id', 'listing_id', 'autoload_item_id'] as $key) {
            $v = $listing[$key] ?? null;
            if (is_int($v) || is_string($v)) {
                return (string) $v;
            }
        }

        // fallback, чтобы slug всё равно был детерминирован
        return (string) (crc32(json_encode($listing, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    }

    /**
     * @param array<string,mixed> $listing
     */
    private function extractTitle(array $listing): ?string
    {
        foreach (['title', 'name'] as $key) {
            $v = $listing[$key] ?? null;
            if (is_string($v) && trim($v) !== '') {
                return trim($v);
            }
        }

        return null;
    }

    /**
     * @param array<string,mixed> $listing
     */
    private function extractDescription(array $listing): ?string
    {
        $v = $listing['description'] ?? null;
        if (is_string($v)) {
            $v = strip_tags($v);
            $t = trim(preg_replace('/\s+/u', ' ', $v) ?? '');
            return $t !== '' ? $t : null;
        }

        return null;
    }

    /**
     * @param array<string,mixed> $listing
     */
    private function extractPrice(array $listing): float
    {
        $v = $listing['price'] ?? null;

        // обычно: price: { value: number, currency: 'RUB' }
        if (is_array($v)) {
            $value = $v['value'] ?? null;
            if (is_numeric($value)) {
                return (float) $value;
            }

            if (is_numeric($v['amount'] ?? null)) {
                return (float) $v['amount'];
            }
        }

        if (is_numeric($v)) {
            return (float) $v;
        }

        return 0.0;
    }

    /**
     * @param array<string,mixed> $listing
     */
    private function extractStock(array $listing): int
    {
        foreach (['stock', 'quantity', 'available_quantity'] as $key) {
            $v = $listing[$key] ?? null;
            if (is_numeric($v)) {
                return (int) $v;
            }
        }

        // если нет quantity — считаем 1
        return 1;
    }

    private function resolveCategoryId(string $title, string $description): int
    {
        $text = mb_strtolower($title . ' ' . $description, 'UTF-8');

        $map = [
            'playstation-5' => ['ps5', 'playstation 5'],
            'playstation-4' => ['ps4', 'playstation 4'],
            'playstation-3' => ['ps3', 'playstation 3'],
            'playstation-2' => ['ps2', 'playstation 2'],
            'psp' => ['psp'],
            'ps-vita' => ['vita', 'ps vita'],

            'xbox-series-x' => ['xbox series x', 'series x'],
            'xbox-series-s' => ['xbox series s', 'series s'],
            'xbox-one' => ['xbox one'],
            'xbox-360' => ['xbox 360'],
            'xbox-original' => ['original xbox', 'xbox original'],

            'nintendo-switch-2' => ['switch 2', 'nintendo switch 2'],
            'nintendo-switch' => ['switch', 'nintendo switch'],
            'wii-u' => ['wii u', 'wii-u'],
            'wii' => ['wii '],
            'gamecube' => ['gamecube'],
            'nintendo-64' => ['nintendo 64', 'n64'],
            'snes' => ['snes', 'super nintendo'],
            'nes' => ['nes', 'nintendo nes'],
            'nintendo-3ds' => ['3ds', '2ds'],
            'nintendo-ds' => ['nintendo ds', 'ds '],
            'game-boy-advance' => ['game boy advance'],
            'game-boy' => ['game boy ', 'gameboy'],

            'dendy-famiclones' => ['dendy', 'famiclone'],
            'sega-mega-drive' => ['sega mega drive', 'mega drive', 'genesis'],
            'steam-deck' => ['steam deck', 'rog ally', 'legion go', 'handheld pc'],
            'handheld-pc' => ['rog ally', 'legion go', 'handheld pc'],
            'retro-other' => ['atari', '3do', 'wonder swan', 'retro'],
        ];

        foreach ($map as $slug => $needles) {
            if ($this->containsAny($text, $needles)) {
                return $this->categoriesBySlug[$slug] ?? $this->fallbackCategoryId;
            }
        }

        // аксессуары/игры можно тоже маппить на корневые категории:
        // но у нас валидация в форме ограничивает только дочерние — тут создаём напрямую, так что будет работать.
        if (str_contains($text, 'геймпад') || str_contains($text, 'джойстик') || str_contains($text, 'контроллер')) {
            return $this->categoriesBySlug['accessories'] ?? $this->fallbackCategoryId;
        }
        if (str_contains($text, 'игра') || str_contains($text, 'диск') || str_contains($text, 'картридж')) {
            return $this->categoriesBySlug['games'] ?? $this->fallbackCategoryId;
        }

        return $this->fallbackCategoryId;
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            $needle = mb_strtolower($needle, 'UTF-8');
            if ($needle === '') {
                continue;
            }
            if (mb_strpos($haystack, $needle, 0, 'UTF-8') !== false) {
                return true;
            }
        }
        return false;
    }
}

