<?php

declare(strict_types=1);

namespace App\Services\Avito;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;

/**
 * Публичные URL картинок с карточки Avito (HTML), т.к. ссылки autoload/items-to-feed
 * из XLSX без OAuth не скачиваются.
 *
 * Примечание: с IP дата-центров Avito часто отдаёт антибот-страницу без og:image — тогда пусто,
 * пока не сработает детальный API (GET …/accounts/{user}/items/{id}/) в импорте.
 */
final class AvitoListingImagesFetcher
{
    /**
     * @return array<int,string>
     */
    public function fetchImageUrlsFromPublicListing(string $listingUrl): array
    {
        $listingUrl = trim($listingUrl);
        if ($listingUrl === '' || ! str_contains(mb_strtolower($listingUrl, 'UTF-8'), 'avito.ru')) {
            return [];
        }

        if (str_starts_with($listingUrl, 'http://')) {
            $listingUrl = 'https://' . substr($listingUrl, 7);
        }

        $urls = $this->extractFromHtml($listingUrl);
        if ($urls !== []) {
            return $urls;
        }

        $mobile = preg_replace('#^https?://(www\.)?avito\.ru#i', 'https://m.avito.ru', $listingUrl);
        if (is_string($mobile) && $mobile !== $listingUrl) {
            return $this->extractFromHtml($mobile);
        }

        return [];
    }

    /**
     * @return array<int,string>
     */
    private function extractFromHtml(string $listingUrl): array
    {
        $http = Http::connectTimeout(10)->timeout(25)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.5',
            'Referer' => 'https://www.avito.ru/',
        ]);
        if (! app()->isProduction()) {
            $http = $http->withoutVerifying();
        }

        try {
            $res = $http->get($listingUrl);
        } catch (\Throwable) {
            return [];
        }

        if (! $res->ok()) {
            return [];
        }

        $html = $res->body();
        if ($html === '') {
            return [];
        }

        // Антибот / капча — в разметке нет галереи
        if (str_contains($html, 'Доступ ограничен') && str_contains($html, 'проблема с IP')) {
            return [];
        }

        $urls = [];

        // Next.js / встроенный JSON часто содержит полные URL CDN, когда meta пустые.
        foreach ($this->extractUrlsFromEmbeddedJson($html) as $u) {
            if ($this->isPlausibleListingImage($u)) {
                $urls[] = $this->absolutizeUrl($u);
            }
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        foreach (['//meta[@property="og:image"]', '//meta[@property="og:image:secure_url"]', '//meta[@name="twitter:image"]'] as $query) {
            foreach ($xpath->query($query) as $node) {
                if (! ($node instanceof \DOMElement)) {
                    continue;
                }
                $src = trim((string) $node->getAttribute('content'));
                if ($src !== '' && $this->isPlausibleListingImage($src)) {
                    $urls[] = $this->absolutizeUrl($src);
                }
            }
        }

        // Полные URL CDN (в т.ч. static.avito.ru)
        if (preg_match_all('~https?://[0-9a-zA-Z.-]*(?:img|static|cdn)\.avito\.(?:st|ru)/[^"\'\s<>]+~u', $html, $m)) {
            foreach ($m[0] as $u) {
                $u = rtrim($u, '.,;)]}\\');
                if ($this->looksLikeAvitoCdnImage($u)) {
                    $urls[] = $this->absolutizeUrl($u);
                }
            }
        }

        if (preg_match_all('~//[0-9]{1,4}\.img\.avito\.st/[^"\'\s<>]+~u', $html, $m2)) {
            foreach ($m2[0] as $u) {
                $u = rtrim($u, '.,;)]}\\');
                if ($this->looksLikeAvitoCdnImage($u)) {
                    $urls[] = $this->absolutizeUrl($u);
                }
            }
        }

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * Парсит __NEXT_DATA__, application/ld+json и прочие script JSON — там часто лежат imagesUrls.
     *
     * @return array<int,string>
     */
    private function extractUrlsFromEmbeddedJson(string $html): array
    {
        $out = [];

        if (preg_match('~<script[^>]*id=["\']__NEXT_DATA__["\'][^>]*>(.*?)</script>~is', $html, $m)) {
            $decoded = json_decode(trim($m[1]), true);
            if (is_array($decoded)) {
                $out = array_merge($out, AvitoItemPayloadImageUrls::fromItemArray($decoded));
            }
        }

        if (preg_match_all('~<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>~is', $html, $blocks, PREG_SET_ORDER)) {
            foreach ($blocks as $block) {
                $decoded = json_decode(trim($block[1]), true);
                if (is_array($decoded)) {
                    $out = array_merge($out, AvitoItemPayloadImageUrls::fromItemArray($decoded));
                }
            }
        }

        // Любой крупный JSON в script (без строгого id) — ищем CDN по тексту
        if (preg_match_all('~<script[^>]*>(\{.*\})</script>~is', $html, $jsonScripts, PREG_SET_ORDER)) {
            foreach ($jsonScripts as $js) {
                if (mb_strlen($js[1], 'UTF-8') < 500 || mb_strlen($js[1], 'UTF-8') > 2_500_000) {
                    continue;
                }
                if (! str_contains($js[1], 'img.avito') && ! str_contains($js[1], 'avito.st')) {
                    continue;
                }
                $decoded = json_decode($js[1], true);
                if (is_array($decoded)) {
                    $out = array_merge($out, AvitoItemPayloadImageUrls::fromItemArray($decoded));
                }
            }
        }

        return array_values(array_unique(array_filter($out)));
    }

    private function isPlausibleListingImage(string $url): bool
    {
        $lower = mb_strtolower($url, 'UTF-8');
        if (str_contains($lower, '.svg')) {
            return false;
        }
        if (preg_match('~(logo|favicon|sprite|placeholder|1x1|pixel)~i', $lower)) {
            return false;
        }
        if (str_contains($lower, 'avito.st') || str_contains($lower, 'avito.ru')) {
            return true;
        }

        return (bool) preg_match('~\.(jpe?g|png|webp|avif|gif)(\?|#|$)~i', $url);
    }

    private function looksLikeAvitoCdnImage(string $url): bool
    {
        $lower = mb_strtolower($url, 'UTF-8');
        if (! str_contains($lower, 'avito.')) {
            return false;
        }
        if (str_contains($lower, '.svg')) {
            return false;
        }

        return true;
    }

    private function absolutizeUrl(string $url): string
    {
        $url = trim($url);
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        return $url;
    }
}
