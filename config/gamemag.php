<?php

declare(strict_types=1);

return [
    'list_url' => 'https://gamemag.ru/',

    /** ID пользователя для автора импортированных новостей */
    'author_user_id' => 1,

    /** Очередь для фоновых задач импорта статей */
    'queue' => env('GAMEMAG_QUEUE', 'default'),

    /** Автозапуск парсинга ленты через scheduler (интервал в часах) */
    'schedule_every_hours' => (int) env('GAMEMAG_SCHEDULE_EVERY_HOURS', 1),
];
