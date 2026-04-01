<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Support\StrHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $categorySlug = trim((string) $request->input('category', ''));
        $tokens = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $servicesQuery = Service::with(['images', 'category'])
            ->orderBy('id');

        if ($categorySlug !== '') {
            $servicesQuery->whereHas('category', fn ($b) => $b->where('slug', $categorySlug));
        }

        if ($tokens !== []) {
            $servicesQuery->where(function ($builder) use ($tokens) {
                foreach ($tokens as $token) {
                    $escaped = StrHelper::escapeForLike($token);
                    $builder->where(function ($q1) use ($escaped) {
                        $q1->where('title', 'like', "%{$escaped}%")
                            ->orWhere('description', 'like', "%{$escaped}%")
                            ->orWhere('content', 'like', "%{$escaped}%")
                            ->orWhereHas('category', function ($q2) use ($escaped) {
                                $q2->where('name', 'like', "%{$escaped}%");
                            });
                    });
                }
            });
        }

        $services = $servicesQuery
            ->paginate(12)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'result' => true,
                'html' => view('services._results', [
                    'services' => $services,
                ])->render(),
            ]);
        }

        $categoryTree = Category::getTreeForServiceFilter();

        $currentCategory = $categorySlug !== ''
            ? Category::where('slug', $categorySlug)->first()
            : null;

        $expandParentIds = [];
        if ($currentCategory !== null) {
            if ($currentCategory->parent_id !== null) {
                $expandParentIds[] = $currentCategory->parent_id;
            } else {
                $root = $categoryTree->firstWhere('id', $currentCategory->id);
                if ($root && $root->children->isNotEmpty()) {
                    $expandParentIds[] = $currentCategory->id;
                }
            }
        }

        return view('services.index', [
            'metaTitle' => $currentCategory ? $currentCategory->name : 'Услуги',
            'services' => $services,
            'categoryTree' => $categoryTree,
            'expandParentIds' => $expandParentIds,
            'currentCategory' => $currentCategory,
        ]);
    }

    public function show(Service $service): View
    {
        $service->load(['images', 'category']);

        $similarServices = Service::with(['images', 'category'])
            ->where('id', '!=', $service->id)
            ->when($service->category_id, fn ($q) => $q->where('category_id', $service->category_id))
            ->orderBy('id')
            ->limit(4)
            ->get();

        return view('services.show', [
            'metaTitle' => $service->name,
            'service' => $service,
            'similarServices' => $similarServices,
        ]);
    }
}
