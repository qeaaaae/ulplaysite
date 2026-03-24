<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\StrHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->input('q', '');
        $like = '%' . StrHelper::escapeForLike($q) . '%';
        $products = Product::with('category')
            ->when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('title', 'like', $like)
                ->orWhere('slug', 'like', $like)
                ->orWhere('description', 'like', $like)))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('admin.products.index', ['products' => $products, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::getCachedAll(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        unset($validated['images']);

        /** @var Product $product */
        $product = Product::create($validated);

        if (! empty($images)) {
            $product->images()->delete();
            foreach (array_slice($images, 0, 5) as $index => $file) {
                $product->images()->create([
                    'path' => $file->store('products', 'public'),
                    'is_cover' => $index === 0,
                    'position' => $index,
                ]);
            }
        }
        return redirect()->route('admin.products.index')->with('message', 'Товар создан');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::getCachedAll(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        $deleteIds = $request->input('delete_images', []);
        unset($validated['images']);
        $product->update($validated);

        if (! empty($deleteIds)) {
            $product->images()->whereIn('id', $deleteIds)->delete();
        }

        if (! empty($images)) {
            $existing = $product->images()->count();
            if ($existing >= 5) {
                return redirect()
                    ->back()
                    ->withErrors(['images' => 'Максимум 5 изображений. Удалите лишние, чтобы добавить новые.'])
                    ->withInput();
            }
            $maxToAdd = max(0, 5 - $existing);
            if ($maxToAdd > 0) {
                $startPosition = (int) $product->images()->max('position') + 1;
                foreach (array_slice($images, 0, $maxToAdd) as $offset => $file) {
                    $product->images()->create([
                        'path' => $file->store('products', 'public'),
                        'is_cover' => $existing === 0 && $offset === 0,
                        'position' => $startPosition + $offset,
                    ]);
                }
            }
        }
        return redirect()->back()->with('message', 'Товар обновлён');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('message', 'Товар удалён');
    }

}
