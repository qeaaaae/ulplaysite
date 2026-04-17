<?php

declare(strict_types=1);

namespace App\Services\Avito;

/**
 * Прямые URL картинок из ответа Avito API (если когда-либо приходят в active-items / items).
 */
final class AvitoItemPayloadImageUrls
{
    /**
     * @param array<string,mixed> $item
     * @return array<int,string>
     */
    public static function fromItemArray(array $item): array
    {
        $urls = [];

        $imagesUrls = $item['imagesUrls'] ?? $item['images_urls'] ?? null;
        if (is_array($imagesUrls)) {
            $listing = $imagesUrls['listing'] ?? null;
            if (is_string($listing) && trim($listing) !== '') {
                $urls[] = self::normalize($listing);
            }
            $list = $imagesUrls['list'] ?? null;
            if (is_array($list)) {
                foreach ($list as $u) {
                    if (is_string($u) && trim($u) !== '') {
                        $urls[] = self::normalize($u);
                    }
                }
            }
        }

        if (isset($item['images']) && is_array($item['images'])) {
            foreach ($item['images'] as $img) {
                if (is_string($img) && trim($img) !== '') {
                    $urls[] = self::normalize($img);
                } elseif (is_array($img) && isset($img['url']) && is_string($img['url'])) {
                    $urls[] = self::normalize($img['url']);
                }
            }
        }

        self::collectUrlsFromNestedStrings($item, $urls);

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * @param array<string,mixed> $item
     * @param array<int,string> $urls
     */
    private static function collectUrlsFromNestedStrings(array $item, array &$urls): void
    {
        $walker = static function (mixed $node) use (&$walker, &$urls): void {
            if (is_string($node)) {
                if (str_contains($node, 'img.avito.st') || str_contains($node, 'static.avito.ru')) {
                    if (preg_match_all('~//[0-9]{1,4}\.img\.avito\.st/[^\s\[\]"\',<>]+~u', $node, $m)) {
                        foreach ($m[0] as $u) {
                            $urls[] = self::normalize($u);
                        }
                    }
                    if (preg_match_all('~https?://[0-9]{1,4}\.img\.avito\.st/[^\s\[\]"\',<>]+~u', $node, $m2)) {
                        foreach ($m2[0] as $u) {
                            $urls[] = self::normalize($u);
                        }
                    }
                }

                return;
            }
            if (! is_array($node)) {
                return;
            }
            foreach ($node as $v) {
                $walker($v);
            }
        };

        $walker($item);
    }

    private static function normalize(string $u): string
    {
        $u = trim($u);
        if ($u === '') {
            return '';
        }
        if (str_starts_with($u, '//')) {
            return 'https:' . $u;
        }
        if (str_starts_with($u, 'http://')) {
            return 'https://' . substr($u, 7);
        }

        return $u;
    }
}
