<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Единый лимит загрузки изображений на сервер (см. config/uploads.php).
 */
final class UploadLimits
{
    public static function imageMaxKb(): int
    {
        return (int) config('uploads.image_max_kb', 102400);
    }
}
