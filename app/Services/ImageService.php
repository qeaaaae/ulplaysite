<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Log;

class ImageService
{
    private const MAX_DIMENSION = 1920;
    private const WEBP_QUALITY = 82;

    public function store(UploadedFile $file, string $directory): string
    {
        if (! extension_loaded('gd')) {
            Log::warning('IMAGE_SERVICE_GD_MISSING_FALLBACK', [
                'directory' => $directory,
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
            ]);

            return $this->storeOriginal(file: $file, directory: $directory);
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getPathname());

        $width = $image->width();
        $height = $image->height();

        if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);
        }

        $encoded = $image->toWebp(quality: self::WEBP_QUALITY);

        $filename = Str::random(40) . '.webp';
        $relativePath = trim($directory, '/') . '/' . $filename;

        $storagePath = storage_path('app/public/' . $relativePath);
        $storageDir = dirname($storagePath);

        if (! is_dir($storageDir)) {
            mkdir(directory: $storageDir, permissions: 0755, recursive: true);
        }

        file_put_contents($storagePath, (string) $encoded);

        return $relativePath;
    }

    private function storeOriginal(UploadedFile $file, string $directory): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === '') {
            $extension = strtolower((string) $file->guessExtension());
        }
        if ($extension === '') {
            $extension = 'jpg';
        }

        $filename = Str::random(40) . '.' . $extension;
        $relativePath = trim($directory, '/') . '/' . $filename;

        $storagePath = storage_path('app/public/' . $relativePath);
        $storageDir = dirname($storagePath);

        if (! is_dir($storageDir)) {
            mkdir(directory: $storageDir, permissions: 0755, recursive: true);
        }

        copy($file->getPathname(), $storagePath);

        return $relativePath;
    }
}
