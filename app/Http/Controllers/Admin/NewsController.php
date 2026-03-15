<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsRequest;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q', '');
        $news = News::with('author')
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"))
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
        $validated['image_path'] = $request->hasFile('image')
            ? $request->file('image')->store('news', 'public')
            : ($request->input('image_path') ?: null);
        unset($validated['image']);
        News::create($validated);
        return redirect()->route('admin.news.index')->with('message', 'Новость создана');
    }

    public function edit(News $news): View
    {
        return view('admin.news.form', ['news' => $news]);
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('news', 'public');
        } elseif ($request->filled('image_path')) {
            $validated['image_path'] = $request->input('image_path');
        } else {
            unset($validated['image_path']);
        }
        unset($validated['image']);
        $news->update($validated);
        return redirect()->route('admin.news.index')->with('message', 'Новость обновлена');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();
        return redirect()->route('admin.news.index')->with('message', 'Новость удалена');
    }
}
