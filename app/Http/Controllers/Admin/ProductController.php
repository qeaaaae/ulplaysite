<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q', '');
        $products = Product::with('category')
            ->when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('title', 'like', "%{$q}%")
                ->orWhere('slug', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('admin.products.index', ['products' => $products, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['image_path'] = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : ($request->input('image_path') ?: null);
        unset($validated['image']);
        Product::create($validated);
        return redirect()->route('admin.products.index')->with('message', 'Товар создан');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_path')) {
            $validated['image_path'] = $request->input('image_path');
        } else {
            unset($validated['image_path']);
        }
        unset($validated['image']);
        $product->update($validated);
        return redirect()->route('admin.products.index')->with('message', 'Товар обновлён');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('message', 'Товар удалён');
    }

}
