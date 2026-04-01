<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ImageService;
use App\Support\StrHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}
    public function index(Request $request): View
    {
        $q = (string) $request->input('q', '');
        $like = '%' . StrHelper::escapeForLike($q) . '%';
        $categories = Category::withCount('products')
            ->when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('name', 'like', $like)->orWhere('slug', 'like', $like)))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        return view('admin.categories.index', ['metaTitle' => 'Категории', 'categories' => $categories, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'metaTitle' => 'Новая категория',
            'category' => new Category(),
            'categories' => Category::getCachedRoots(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['image']);
        $category = Category::create($validated);

        if ($request->hasFile('image')) {
            $category->images()->create([
                'path' => $this->imageService->store($request->file('image'), 'categories'),
                'is_cover' => true,
                'position' => 0,
            ]);
        }

        return redirect()->route('admin.categories.index')->with('message', 'Категория создана');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'metaTitle' => $category->name,
            'category' => $category,
            'categories' => Category::getCachedRoots()->reject(fn ($c) => $c->id === $category->id)->values(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $category->images()->delete();
            $category->images()->create([
                'path' => $this->imageService->store($request->file('image'), 'categories'),
                'is_cover' => true,
                'position' => 0,
            ]);
        }
        unset($validated['image']);
        $category->update($validated);
        return redirect()->back()->with('message', 'Категория обновлена');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('message', 'Категория удалена');
    }
}
