<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ManagesCoverImage
{
    protected function coverImageIdFromRequest(Request $request): ?int
    {
        $value = $request->input('cover_image_id');

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function applyCoverImage(Model $model, ?int $coverImageId): void
    {
        if (! method_exists($model, 'images')) {
            return;
        }

        $images = $model->images()->orderBy('position')->get();
        if ($images->isEmpty()) {
            return;
        }

        $targetId = ($coverImageId !== null && $images->contains('id', $coverImageId))
            ? $coverImageId
            : $images->first()->id;

        $model->images()->update(['is_cover' => false]);
        $model->images()->where('id', $targetId)->update(['is_cover' => true]);
    }
}
