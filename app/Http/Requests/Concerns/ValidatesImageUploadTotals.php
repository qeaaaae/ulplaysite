<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Support\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\UploadedFile;

trait ValidatesImageUploadTotals
{
    /**
     * Проверка суммарного размера файлов в массиве (например images.*).
     * Тот же лимит, что и на один файл — см. UploadLimits::imageMaxKb().
     */
    protected function validateImagesArrayTotalSize(Validator $validator, string $field = 'images'): void
    {
        $validator->after(function (Validator $validator) use ($field): void {
            $files = $this->file($field);
            if (! is_array($files)) {
                return;
            }
            $totalBytes = 0;
            foreach ($files as $f) {
                if ($f instanceof UploadedFile && $f->isValid()) {
                    $totalBytes += $f->getSize();
                }
            }
            $maxKb = UploadLimits::imageMaxKb();
            if ($totalBytes <= $maxKb * 1024) {
                return;
            }
            $validator->errors()->add(
                $field,
                __('validation.upload_total_size', ['max' => $maxKb / 1024])
            );
        });
    }
}
