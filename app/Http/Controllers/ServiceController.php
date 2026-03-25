<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\CartService;
use App\Support\StrHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $tokens = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $servicesQuery = Service::with('images')->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('id');

        if (!empty($tokens)) {
            $servicesQuery->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $escaped = StrHelper::escapeForLike($token);
                    $builder->where(function ($q1) use ($escaped) {
                        $q1->where('title', 'like', "%{$escaped}%")
                            ->orWhere('description', 'like', "%{$escaped}%")
                            ->orWhere('type', 'like', "%{$escaped}%");
                    });
                }
            });
        }

        $services = $servicesQuery
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

    public function show(Service $service, CartService $cart): View
    {
        $service->load(['images', 'reviews' => fn ($q) => $q->with('user')->latest()->limit(50)]);
        $cartServiceIds = $cart->getItems()->pluck('service_id')->filter()->values()->all();
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
            'cartServiceIds' => $cartServiceIds,
        ]);
    }
}
