<?php

declare(strict_types=1);

/**
 * Роутер встроенного PHP-сервера для локальной разработки.
 * Запуск: composer serve  (флаги -d передаются в процесс, который принимает HTTP)
 * Не использовать artisan serve — он поднимает дочерний php -S без лимитов загрузки.
 */
$publicPath = __DIR__ . '/public';

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '');

if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$remoteAddress = ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . ':' . ($_SERVER['REMOTE_PORT'] ?? '0');

file_put_contents('php://stdout', "[{$formattedDateTime}] {$remoteAddress} [{$requestMethod}] URI: {$uri}\n");

require_once $publicPath . '/index.php';
