<?php

declare(strict_types=1);

return [
    /*
    | Загрузка изображений на сервер (Кб). Laravel rule `max` для файлов — в килобайтах.
    | image_max_kb — лимит на один файл (по умолчанию 100 МБ).
    | images_batch_max_kb — суммарный лимит для images[] в одном запросе (по умолчанию 500 МБ).
    */
    'image_max_kb' => (int) env('UPLOAD_IMAGE_MAX_KB', 102400),
    'images_batch_max_kb' => (int) env('UPLOAD_IMAGES_BATCH_MAX_KB', 512000),
];
