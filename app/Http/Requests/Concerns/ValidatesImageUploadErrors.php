<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Support\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\UploadedFile;

trait ValidatesImageUploadErrors
{
    protected function validateSingleImageUpload(Validator $validator, string $field = 'image'): void
    {
        $validator->after(function (Validator $validator) use ($field): void {
            $file = $this->file($field);
            if (! $file instanceof UploadedFile) {
                return;
            }

            if ($file->isValid()) {
                return;
            }

            $validator->errors()->add($field, $this->imageUploadErrorMessage($file->getError()));
        });
    }

    protected function imageUploadErrorMessage(int $errorCode): string
    {
        $appMaxMb = (int) round(UploadLimits::imageMaxKb() / 1024);
        $phpMax = (string) ini_get('upload_max_filesize');
        $phpPostMax = (string) ini_get('post_max_size');

        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "Файл слишком большой. Лимит приложения: {$appMaxMb} МБ, лимит PHP: upload_max_filesize={$phpMax}, post_max_size={$phpPostMax}. Увеличьте лимиты на хостинге (public/.user.ini) или выберите файл меньше.",
            UPLOAD_ERR_PARTIAL => 'Файл загружен не полностью. Попробуйте ещё раз.',
            UPLOAD_ERR_NO_TMP_DIR => 'На сервере нет временной папки для загрузки. Обратитесь к хостингу.',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск сервера. Обратитесь к хостингу.',
            UPLOAD_ERR_EXTENSION => 'Загрузка заблокирована расширением PHP на сервере.',
            default => "Не удалось загрузить файл (код {$errorCode}). Проверьте лимиты PHP: upload_max_filesize={$phpMax}, post_max_size={$phpPostMax}.",
        };
    }
}
