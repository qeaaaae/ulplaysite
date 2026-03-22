<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $services = Service::with('images')->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'result' => true,
                'html' => view('services._results', [
                    'services' => $services,
                ])->render(),
            ]);
        }

        return view('services.index', ['services' => $services]);
    }

    public function show(Service $service): View
    {
        $service->load(['images', 'reviews' => fn ($q) => $q->with('user')->latest()->limit(50)]);
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $canReview = $user
            && $user->hasPurchasedService($service)
            && ! $service->reviews->contains('user_id', $user->id);

        $similarServices = Service::with('images')->withAvg('reviews', 'rating')
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
