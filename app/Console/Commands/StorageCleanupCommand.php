<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Image;
use App\Models\News;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageCleanupCommand extends Command
{
    protected $signature = 'storage:cleanup
        {--dry-run : Показать файлы без удаления}
        {--dir= : Конкретная директория (products, news, services, categories, banners, content)}';

    protected $description = 'Удалить осиротевшие файлы из storage, которые не привязаны ни к одной записи в БД';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dryRun = (bool) $this->option('dry-run');
        $targetDir = $this->option('dir');

        $directories = $targetDir
            ? [(string) $targetDir]
            : ['products', 'news', 'services', 'categories', 'banners', 'content'];

        $knownPaths = Image::pluck('path')
            ->filter(fn ($p) => is_string($p) && ! str_starts_with($p, 'http'))
            ->map(fn ($p) => ltrim($p, '/'))
            ->flip()
            ->all();

        $contentPaths = $this->collectContentImagePaths();

        $orphanCount = 0;
        $freedBytes = 0;

        foreach ($directories as $dir) {
            if (! $disk->exists($dir)) {
                continue;
            }

            $this->info("Сканирую: {$dir}/");
            $files = $disk->allFiles($dir);

            foreach ($files as $file) {
                if ($dir === 'content') {
                    if (isset($contentPaths[$file])) {
                        continue;
                    }
                } else {
                    if (isset($knownPaths[$file])) {
                        continue;
                    }
                }

                $size = $disk->size($file);
                $orphanCount++;
                $freedBytes += $size;

                if ($dryRun) {
                    $this->line("  <comment>[orphan]</comment> {$file} (" . $this->formatSize($size) . ')');
                } else {
                    $disk->delete($file);
                    $this->line("  <info>[deleted]</info> {$file} (" . $this->formatSize($size) . ')');
                }
            }
        }

        $this->newLine();

        if ($orphanCount === 0) {
            $this->info('Осиротевших файлов не найдено.');
            return self::SUCCESS;
        }

        $label = $dryRun ? 'Найдено' : 'Удалено';
        $this->info("{$label}: {$orphanCount} файл(ов), освобождено: " . $this->formatSize($freedBytes));

        if ($dryRun) {
            $this->comment('Запустите без --dry-run, чтобы удалить.');
        }

        return self::SUCCESS;
    }

    /**
     * Сканирует markdown-контент News и Service, извлекает пути картинок из content/.
     *
     * @return array<string, true>
     */
    private function collectContentImagePaths(): array
    {
        $paths = [];
        $pattern = '#/storage/(content/[^\s\)\]"\']+)#';

        $models = [
            News::class,
            Service::class,
        ];

        foreach ($models as $model) {
            $model::whereNotNull('content')
                ->where('content', '!=', '')
                ->select('content')
                ->chunkById(200, function ($rows) use ($pattern, &$paths) {
                    foreach ($rows as $row) {
                        if (preg_match_all($pattern, $row->content, $matches)) {
                            foreach ($matches[1] as $match) {
                                $paths[$match] = true;
                            }
                        }
                    }
                });
        }

        return $paths;
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
