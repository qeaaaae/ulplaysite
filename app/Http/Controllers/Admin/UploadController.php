<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use App\Support\UploadLimits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:' . UploadLimits::imageMaxKb()],
        ]);

        $path = $this->imageService->store($request->file('image'), 'content');

        return response()->json([
            'data' => [
                'filePath' => asset('storage/' . $path),
            ],
        ]);
    }
}
