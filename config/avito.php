<?php

declare(strict_types=1);

return [
    'client_id' => env('AVITO_CLIENT_ID', ''),
    'client_secret' => env('AVITO_CLIENT_SECRET', ''),

    // user_id личного кабинета Avito (нужен для /core/v1/accounts/{user_id}/items/).
    // Если не задан, можно получать через GET /core/v1/accounts/self.
    'user_id' => env('AVITO_USER_ID', ''),

    'token_url' => env('AVITO_TOKEN_URL', 'https://api.avito.ru/token'),
    'api_base' => env('AVITO_API_BASE', 'https://api.avito.ru'),

    'cache_dir' => env('AVITO_CACHE_DIR', 'avito'),

    // Локальная отладка: отключить проверку сертификатов TLS (curl error 60 на Windows)
    'ssl_verify' => env('AVITO_SSL_VERIFY', 'true') === 'true',

    // OAuth scopes for client_credentials (space-separated). Для списка объявлений обычно нужен items:info.
    'scopes' => env('AVITO_SCOPES', 'items:info user:read'),
];

