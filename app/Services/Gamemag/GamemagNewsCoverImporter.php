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
            return;
        }

        try {
            $response = $this->downloadImageResponse($imageUrl);
            if ($response === null || ! $response->ok()) {
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
        } catch (\Throwable $e) {
            Log::warning('Не удалось импортировать обложку новости', [
                'news_id' => $news->id,
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function downloadImageResponse(string $imageUrl): ?\Illuminate\Http\Client\Response
    {
        $host = (string) (parse_url($imageUrl, PHP_URL_HOST) ?: '');
        $referer = $host !== '' ? ('https://' . $host . '/') : null;

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept-Language' => 'ru-RU,ru;q=0.9',
            'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
        ];

        if ($referer !== null) {
            $headers['Referer'] = $referer;
        }

        try {
            $response = Http::timeout(20)->withHeaders($headers)->get($imageUrl);
            if ($response->ok()) {
                return $response;
            }
        } catch (\Throwable $e) {
            Log::notice('Ошибка загрузки обложки с проверкой TLS', [
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $fallbackResponse = Http::timeout(20)->withHeaders($headers)->withoutVerifying()->get($imageUrl);
            if ($fallbackResponse->ok()) {
                Log::notice('Обложка загружена через fallback withoutVerifying', [
                    'image_url' => $imageUrl,
                ]);
            }

            return $fallbackResponse;
        } catch (\Throwable $e) {
            Log::warning('Ошибка fallback-загрузки обложки', [
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
