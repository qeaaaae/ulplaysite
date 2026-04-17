<?php

declare(strict_types=1);

return [
    /*
    | Загрузка изображений на сервер (Кб). Laravel rule `max` для файлов — в килобайтах.
    | Один файл и сумма всех файлов в поле `images[]` в одном запросе — не больше этого значения (по умолчанию 100 МБ).
    */
    'image_max_kb' => (int) env('UPLOAD_IMAGE_MAX_KB', 102400),
];
