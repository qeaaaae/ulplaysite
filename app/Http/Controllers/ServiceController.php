<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('services.index', ['services' => $services]);
    }

    public function show(Service $service): View
    {
        $service->load(['reviews' => fn ($q) => $q->with('user')->latest()->limit(50)]);
        $user = auth()->user();
        $canReview = $user
            && $user->hasPurchasedService($service)
            && ! $service->reviews->contains('user_id', $user->id);

        $similarServices = Service::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('id', '!=', $service->id)
            ->when($service->type, fn ($q) => $q->where('type', $service->type))
            ->orderBy('id')
            ->limit(4)
            ->get();

        return view('services.show', [
            'service' => $service,
            'reviews' => $service->reviews,
            'canReview' => $canReview,
            'similarServices' => $similarServices,
        ]);
    }
}
