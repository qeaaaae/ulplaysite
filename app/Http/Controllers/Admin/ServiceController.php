<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Support\StrHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->input('q', '');
        $like = '%' . StrHelper::escapeForLike($q) . '%';
        $services = Service::when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('title', 'like', $like)->orWhere('slug', 'like', $like)))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('admin.services.index', ['services' => $services, 'search' => $q]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service()]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        unset($validated['images']);

        /** @var Service $service */
        $service = Service::create($validated);

        if (! empty($images)) {
            $service->images()->delete();
            foreach (array_slice($images, 0, 5) as $index => $file) {
                $service->images()->create([
                    'path' => $file->store('services', 'public'),
                    'is_cover' => $index === 0,
                    'position' => $index,
                ]);
            }
        }
        return redirect()->route('admin.services.index')->with('message', 'Услуга создана');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', ['service' => $service]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        $deleteIds = $request->input('delete_images', []);
        unset($validated['images']);
        $service->update($validated);

        if (! empty($deleteIds)) {
            $service->images()->whereIn('id', $deleteIds)->delete();
        }

        if (! empty($images)) {
            $existing = $service->images()->count();
            if ($existing >= 5) {
                return redirect()
                    ->back()
                    ->withErrors(['images' => 'Максимум 5 изображений. Удалите лишние, чтобы добавить новые.'])
                    ->withInput();
            }
            $maxToAdd = max(0, 5 - $existing);
            if ($maxToAdd > 0) {
                $startPosition = (int) $service->images()->max('position') + 1;
                foreach (array_slice($images, 0, $maxToAdd) as $offset => $file) {
                    $service->images()->create([
                        'path' => $file->store('services', 'public'),
                        'is_cover' => $existing === 0 && $offset === 0,
                        'position' => $startPosition + $offset,
                    ]);
                }
            }
        }
        return redirect()->back()->with('message', 'Услуга обновлена');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('message', 'Услуга удалена');
    }
}
