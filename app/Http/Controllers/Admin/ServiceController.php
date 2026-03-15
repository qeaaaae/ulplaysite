<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q', '');
        $services = Service::when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%")))
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
        $validated['image_path'] = $request->hasFile('image')
            ? $request->file('image')->store('services', 'public')
            : ($request->input('image_path') ?: null);
        unset($validated['image']);
        Service::create($validated);
        return redirect()->route('admin.services.index')->with('message', 'Услуга создана');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', ['service' => $service]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('services', 'public');
        } elseif ($request->filled('image_path')) {
            $validated['image_path'] = $request->input('image_path');
        } else {
            unset($validated['image_path']);
        }
        unset($validated['image']);
        $service->update($validated);
        return redirect()->route('admin.services.index')->with('message', 'Услуга обновлена');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('message', 'Услуга удалена');
    }
}
