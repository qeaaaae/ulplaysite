<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q', '');
        $categories = Category::withCount('products')
            ->when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%")))
            ->orderBy('sort_order')
            ->paginate(10)
            ->withQueryString();
        return view('admin.categories.index', ['categories' => $categories, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(),
            'categories' => Category::whereNull('parent_id')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['image_path'] = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'public')
            : null;
        unset($validated['image']);
        Category::create($validated);
        return redirect()->route('admin.categories.index')->with('message', 'Категория создана');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'categories' => Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('categories', 'public');
        } else {
            unset($validated['image_path']);
        }
        unset($validated['image']);
        $category->update($validated);
        return redirect()->route('admin.categories.index')->with('message', 'Категория обновлена');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('message', 'Категория удалена');
    }
}
