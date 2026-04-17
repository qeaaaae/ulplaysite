<?php

declare(strict_types=1);

namespace App\Services\Gamemag;

use App\Models\News;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

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
            $http = Http::timeout(15)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                'Accept-Language' => 'ru-RU,ru;q=0.9',
            ]);

            if (! app()->isProduction()) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get($imageUrl);
            if (! $response->ok()) {
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
        } catch (\Throwable) {
            // ignore cover import errors
        }
    }
}
