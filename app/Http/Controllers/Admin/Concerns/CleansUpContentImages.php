<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait CleansUpContentImages
{
    /**
     * Удаляет загруженные через markdown-редактор картинки,
     * которые не вошли в финальный контент (пользователь удалил их перед сохранением).
     */
    protected function cleanupUnusedContentImages(Request $request, ?string $content): void
    {
        $uploaded = $request->input('uploaded_content_images', []);

        if (empty($uploaded) || ! is_array($uploaded)) {
            return;
        }

        $disk = Storage::disk('public');
        $content = $content ?? '';

        foreach ($uploaded as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }

            if (str_contains($content, $url)) {
                continue;
            }

            if (preg_match('#/storage/(content/.+)$#', $url, $m)) {
                $relativePath = $m[1];
                if ($disk->exists($relativePath)) {
                    $disk->delete($relativePath);
                }
            }
        }
    }
}
