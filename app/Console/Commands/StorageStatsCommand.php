<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageStatsCommand extends Command
{
    protected $signature = 'storage:stats';

    protected $description = 'Показать статистику использования дискового пространства в storage/app/public';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $directories = ['products', 'news', 'services', 'categories', 'banners'];

        $rows = [];
        $totalFiles = 0;
        $totalSize = 0;
        $totalWebp = 0;
        $totalOther = 0;

        foreach ($directories as $dir) {
            if (! $disk->exists($dir)) {
                $rows[] = [$dir, 0, '0 B', 0, 0];
                continue;
            }

            $files = $disk->allFiles($dir);
            $dirSize = 0;
            $webpCount = 0;
            $otherCount = 0;

            foreach ($files as $file) {
                $dirSize += $disk->size($file);
                if (str_ends_with(strtolower($file), '.webp')) {
                    $webpCount++;
                } else {
                    $otherCount++;
                }
            }

            $fileCount = count($files);
            $totalFiles += $fileCount;
            $totalSize += $dirSize;
            $totalWebp += $webpCount;
            $totalOther += $otherCount;

            $rows[] = [$dir, $fileCount, $this->formatSize($dirSize), $webpCount, $otherCount];
        }

        $this->table(
            ['Директория', 'Файлов', 'Размер', 'WebP', 'Другие'],
            $rows,
        );

        $this->newLine();
        $this->info("Всего: {$totalFiles} файлов, " . $this->formatSize($totalSize));
        $this->line("  WebP: {$totalWebp} | Другие форматы: {$totalOther}");

        $dbCount = Image::whereNotNull('path')
            ->where('path', 'not like', 'http%')
            ->count();

        $orphans = $totalFiles - $dbCount;

        $this->newLine();
        $this->line("Записей в БД (images): <info>{$dbCount}</info>");

        if ($orphans > 0) {
            $this->warn("Потенциально осиротевших файлов: ~{$orphans}");
            $this->comment('Используйте php artisan storage:cleanup --dry-run для деталей.');
        } elseif ($orphans < 0) {
            $absOrphans = abs($orphans);
            $this->comment("В БД {$absOrphans} записей ссылаются на внешние URL или удалённые файлы.");
        } else {
            $this->info('Осиротевших файлов не обнаружено.');
        }

        return self::SUCCESS;
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
