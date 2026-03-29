<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsRequest;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\News;
use App\Services\ImageService;
use App\Support\StrHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    use Concerns\CleansUpContentImages;

    public function __construct(
        private readonly ImageService $imageService,
    ) {}
    public function index(Request $request): View
    {
        $q = (string) $request->input('q', '');
        $like = '%' . StrHelper::escapeForLike($q) . '%';
        $news = News::with('author')
            ->when($q !== '', fn ($query) => $query->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('admin.news.index', ['news' => $news, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.news.form', ['news' => new News()]);
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['author_id'] = auth()->id();
        $images = $request->file('images', []);
        unset($validated['images']);

        /** @var News $news */
        $news = News::create($validated);

        $this->cleanupUnusedContentImages($request, $news->content);

        if (! empty($images)) {
            $news->images()->delete();
            foreach (array_slice($images, 0, 5) as $index => $file) {
                $news->images()->create([
                    'path' => $this->imageService->store($file, 'news'),
                    'is_cover' => $index === 0,
                    'position' => $index,
                ]);
            }
        }
        return redirect()->route('admin.news.index')->with('message', 'Новость создана');
    }

    public function edit(News $news): View
    {
        $news->load(['author', 'views.user']);

        /** @var Collection<int, \App\Models\NewsView> $views */
        $views = $news->views()->with('user')->orderByDesc('created_at')->get();

        return view('admin.news.form', [
            'news' => $news,
            'views' => $views,
        ]);
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        $deleteIds = $request->input('delete_images', []);
        unset($validated['images']);
        $news->update($validated);

        $this->cleanupUnusedContentImages($request, $news->content);

        if (! empty($deleteIds)) {
            $news->images()->whereIn('id', $deleteIds)->delete();
        }

        if (! empty($images)) {
            $existing = $news->images()->count();
            if ($existing >= 5) {
                return redirect()
                    ->back()
                    ->withErrors(['images' => 'Максимум 5 изображений. Удалите лишние, чтобы добавить новые.'])
                    ->withInput();
            }
            $maxToAdd = max(0, 5 - $existing);
            if ($maxToAdd > 0) {
                $startPosition = (int) $news->images()->max('position') + 1;
                foreach (array_slice($images, 0, $maxToAdd) as $offset => $file) {
                    $news->images()->create([
                        'path' => $this->imageService->store($file, 'news'),
                        'is_cover' => $existing === 0 && $offset === 0,
                        'position' => $startPosition + $offset,
                    ]);
                }
            }
        }
        return redirect()->back()->with('message', 'Новость обновлена');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();
        return redirect()->route('admin.news.index')->with('message', 'Новость удалена');
    }
}
