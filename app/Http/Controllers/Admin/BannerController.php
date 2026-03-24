<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Support\StrHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->input('q', '');
        $like = '%' . StrHelper::escapeForLike($q) . '%';
        $banners = Banner::when($q !== '', fn ($query) => $query->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();
        return view('admin.banners.index', ['banners' => $banners, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.banners.form', ['banner' => new Banner()]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['image']);
        $banner = Banner::create($validated);

        if ($request->hasFile('image')) {
            $banner->images()->create([
                'path' => $request->file('image')->store('banners', 'public'),
                'is_cover' => true,
                'position' => 0,
            ]);
        }

        return redirect()->route('admin.banners.index')->with('message', 'Баннер создан');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.form', ['banner' => $banner]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $banner->images()->delete();
            $banner->images()->create([
                'path' => $request->file('image')->store('banners', 'public'),
                'is_cover' => true,
                'position' => 0,
            ]);
        }
        unset($validated['image']);
        $banner->update($validated);
        return redirect()->back()->with('message', 'Баннер обновлён');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('message', 'Баннер удалён');
    }
}
