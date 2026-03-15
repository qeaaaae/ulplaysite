<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::orderBy('id')->get();

        return view('services.index', ['services' => $services]);
    }

    public function show(Service $service): View
    {
        $service->load(['reviews' => fn ($q) => $q->with('user')->latest()->limit(50)]);
        $user = auth()->user();
        $canReview = $user
            && $user->hasPurchasedService($service)
            && ! $service->reviews->contains('user_id', $user->id);

        return view('services.show', [
            'service' => $service,
            'reviews' => $service->reviews,
            'canReview' => $canReview,
        ]);
    }
}
