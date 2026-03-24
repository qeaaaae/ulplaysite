<?php

declare(strict_types=1);

namespace App\Support;

final class StrHelper
{
    /**
     * Экранирует спецсимволы LIKE (% и _) для безопасного поиска.
     */
    public static function escapeForLike(string $value): string
    {
        return str_replace(['%', '_'], ['\%', '\_'], $value);
    }
}
