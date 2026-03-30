<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ConvertImagesToWebpCommand extends Command
{
    private const MAX_DIMENSION = 1920;
    private const WEBP_QUALITY = 82;

    protected $signature = 'images:convert-webp
        {--dry-run : Показать файлы без конвертации}
        {--dir= : Конкретная директория (products, news, services, categories, banners, content, reviews, support-tickets)}
        {--delete-originals : Удалить оригиналы после конвертации}';

    protected $description = 'Конвертировать существующие изображения (PNG, JPG, JPEG) в WebP с ресайзом';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleteOriginals = (bool) $this->option('delete-originals');
        $targetDir = $this->option('dir');

        $query = Image::whereNotNull('path')
            ->where('path', 'not like', 'http%')
            ->where('path', 'not like', '%.webp');

        if ($targetDir) {
            $query->where('path', 'like', ltrim((string) $targetDir, '/') . '/%');
        }

        $images = $query->get();

        if ($images->isEmpty()) {
            $this->info('Все изображения уже в формате WebP.');
            return self::SUCCESS;
        }

        $this->info("Найдено {$images->count()} изображений для конвертации.");
        $this->newLine();

        $disk = Storage::disk('public');
        $manager = new ImageManager(new Driver());
        $converted = 0;
        $savedBytes = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        foreach ($images as $imageRecord) {
            $oldPath = ltrim($imageRecord->path, '/');

            if (! $disk->exists($oldPath)) {
                $bar->advance();
                continue;
            }

            $oldSize = $disk->size($oldPath);

            if ($dryRun) {
                $this->newLine();
                $this->line("  <comment>[convert]</comment> {$oldPath} (" . $this->formatSize($oldSize) . ')');
                $converted++;
                $bar->advance();
                continue;
            }

            try {
                $fullPath = $disk->path($oldPath);
                $img = $manager->read($fullPath);

                $width = $img->width();
                $height = $img->height();

                if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
                    $img->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);
                }

                $encoded = $img->toWebp(quality: self::WEBP_QUALITY);

                $newPath = preg_replace('/\.[a-zA-Z]+$/', '.webp', $oldPath);
                if ($newPath === $oldPath) {
                    $newPath = $oldPath . '.webp';
                }

                $newFullPath = $disk->path($newPath);
                file_put_contents($newFullPath, (string) $encoded);

                $newSize = filesize($newFullPath);
                $savedBytes += $oldSize - $newSize;

                $imageRecord->update(['path' => $newPath]);

                if ($deleteOriginals && $newPath !== $oldPath) {
                    $disk->delete($oldPath);
                }

                $converted++;
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("  Ошибка: {$oldPath} — {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $label = $dryRun ? 'К конвертации' : 'Сконвертировано';
        $this->info("{$label}: {$converted} файлов");

        if (! $dryRun && $savedBytes > 0) {
            $this->info('Сэкономлено: ' . $this->formatSize($savedBytes));
        }

        if ($errors > 0) {
            $this->warn("Ошибок: {$errors}");
        }

        if ($dryRun) {
            $this->comment('Запустите без --dry-run, чтобы выполнить конвертацию.');
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
