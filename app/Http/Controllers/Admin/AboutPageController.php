<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAboutPageRequest;
use App\Models\AboutPageSetting;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function edit(): View
    {
        $about = AboutPageSetting::current();
        $about->load('images');

        return view('admin.about.edit', [
            'metaTitle' => 'О нас',
            'about' => $about,
        ]);
    }

    public function update(UpdateAboutPageRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        $deleteIds = $request->input('delete_images', []);

        $about = AboutPageSetting::current();
        $about->address = $validated['address'];
        $about->save();

        if (! empty($deleteIds)) {
            $about->images()->whereIn('id', $deleteIds)->delete();
        }

        if (! empty($images)) {
            $existing = $about->images()->count();
            if ($existing >= 5) {
                return redirect()
                    ->back()
                    ->withErrors(['images' => 'Максимум 5 изображений. Удалите лишние, чтобы добавить новые.'])
                    ->withInput();
            }

            $maxToAdd = max(0, 5 - $existing);
            if ($maxToAdd > 0) {
                $startPosition = (int) $about->images()->max('position') + 1;
                $hasCover = $about->images()->where('is_cover', true)->exists();

                foreach (array_slice($images, 0, $maxToAdd) as $offset => $file) {
                    $about->images()->create([
                        'path' => $this->imageService->store($file, 'about'),
                        'is_cover' => ! $hasCover && $offset === 0,
                        'position' => $startPosition + $offset,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.about.edit')
            ->with('message', 'Страница «О нас» обновлена');
    }
}
