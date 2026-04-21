<?php

declare(strict_types=1);

namespace App\Services\Gamemag;

use App\Models\News;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Скачивание обложки по URL (как при ручном импорте из админки).
 */
final class GamemagNewsCoverImporter
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function attachIfPossible(News $news, string $imageUrl): void
    {
        if (! preg_match('~^https?://~i', $imageUrl)) {
            Log::warning('GAMEMAG_IMPORT_COVER_INVALID_URL', [
                'news_id' => $news->id,
                'source_url' => $news->source_url,
                'image_url' => $imageUrl,
            ]);
            return;
        }

        try {
            $sourceUrl = trim((string) $news->source_url);
            $response = $this->downloadImageResponse(
                imageUrl: $imageUrl,
                sourceUrl: $sourceUrl !== '' ? $sourceUrl : null,
            );
            if ($response === null || ! $response->ok()) {
                Log::warning('GAMEMAG_IMPORT_COVER_DOWNLOAD_FAILED', [
                    'news_id' => $news->id,
                    'source_url' => $news->source_url,
                    'image_url' => $imageUrl,
                ]);
                return;
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'news_cover_');
            if ($tmpPath === false) {
                return;
            }

            file_put_contents($tmpPath, $response->body());

            $uploaded = new UploadedFile(
                path: $tmpPath,
                originalName: basename((string) (parse_url($imageUrl, PHP_URL_PATH) ?: 'cover.jpg')),
                mimeType: $response->header('Content-Type', 'image/jpeg'),
                test: true,
            );

            $path = $this->imageService->store($uploaded, 'news');

            $news->images()->create([
                'path' => $path,
                'is_cover' => true,
                'position' => 0,
            ]);
            Log::info('GAMEMAG_IMPORT_COVER_SAVED', [
                'news_id' => $news->id,
                'source_url' => $news->source_url,
                'image_url' => $imageUrl,
                'stored_path' => $path,
            ]);
        } catch (\Throwable $e) {
            Log::warning('GAMEMAG_IMPORT_COVER_EXCEPTION', [
                'news_id' => $news->id,
                'source_url' => $news->source_url,
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function downloadImageResponse(string $imageUrl, ?string $sourceUrl = null): ?\Illuminate\Http\Client\Response
    {
        $host = (string) (parse_url($imageUrl, PHP_URL_HOST) ?: '');
        $referer = $sourceUrl ?: ($host !== '' ? ('https://' . $host . '/') : null);

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
            'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
        ];

        if ($referer !== null) {
            $headers['Referer'] = $referer;
            $originScheme = (string) (parse_url($referer, PHP_URL_SCHEME) ?: '');
            $originHost = (string) (parse_url($referer, PHP_URL_HOST) ?: '');
            if ($originScheme !== '' && $originHost !== '') {
                $headers['Origin'] = $originScheme . '://' . $originHost;
            }
        }

        try {
            $response = Http::timeout(20)->withHeaders($headers)->get($imageUrl);
            if ($response->ok()) {
                return $response;
            }
            Log::notice('GAMEMAG_IMPORT_COVER_HTTP_NOT_OK', [
                'image_url' => $imageUrl,
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'referer' => $referer,
            ]);
        } catch (\Throwable $e) {
            Log::notice('GAMEMAG_IMPORT_COVER_HTTP_EXCEPTION', [
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
                'referer' => $referer,
            ]);
        }

        try {
            $fallbackResponse = Http::timeout(20)->withHeaders($headers)->withoutVerifying()->get($imageUrl);
            if ($fallbackResponse->ok()) {
                Log::notice('GAMEMAG_IMPORT_COVER_FALLBACK_OK', [
                    'image_url' => $imageUrl,
                    'referer' => $referer,
                ]);
            } else {
                Log::warning('GAMEMAG_IMPORT_COVER_FALLBACK_NOT_OK', [
                    'image_url' => $imageUrl,
                    'status' => $fallbackResponse->status(),
                    'content_type' => $fallbackResponse->header('Content-Type'),
                    'referer' => $referer,
                ]);
            }

            return $fallbackResponse;
        } catch (\Throwable $e) {
            Log::warning('GAMEMAG_IMPORT_COVER_FALLBACK_EXCEPTION', [
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
                'referer' => $referer,
            ]);

            return null;
        }
    }
}
