<?php

declare(strict_types=1);

namespace App\Services;

class VideoEmbedService
{
    /**
     * Преобразует URL YouTube или Rutube в embed-URL для iframe.
     * Поддерживает:
     * - YouTube: youtube.com/watch?v=ID, youtu.be/ID
     * - Rutube: rutube.ru/video/ID/
     */
    public function toEmbedUrl(?string $url): ?string
    {
        if (empty($url) || ! str_starts_with($url, 'http')) {
            return null;
        }

        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            $videoId = $this->extractYoutubeId($url);
            return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
        }

        if (str_contains($host, 'rutube.ru')) {
            $videoId = $this->extractRutubeId($url);
            return $videoId ? "https://rutube.ru/play/embed/{$videoId}/" : null;
        }

        return null;
    }

    private function extractYoutubeId(string $url): ?string
    {
        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractRutubeId(string $url): ?string
    {
        if (preg_match('#rutube\.ru/video/([a-f0-9]{32})#', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public function isValidUrl(?string $url): bool
    {
        return $this->toEmbedUrl($url) !== null;
    }
}
