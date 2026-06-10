<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportProductsXlsxJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;
    public int $timeout = 1800;

    public function __construct(
        public readonly string $xlsxPath,
    ) {}

    public function handle(ProductController $controller): void
    {
        @ini_set('memory_limit', '512M');

        Log::info('PRODUCT_XLSX_IMPORT_STARTED', [
            'path' => $this->xlsxPath,
            'file_exists' => is_file($this->xlsxPath),
            'memory_limit' => ini_get('memory_limit'),
            'queue_retry_after' => config('queue.connections.database.retry_after'),
        ]);

        try {
            $result = $controller->runXlsxImportFromPath($this->xlsxPath);
            Log::info('PRODUCT_XLSX_IMPORT_FINISHED', [
                'path' => $this->xlsxPath,
                'created' => $result['created'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'deactivated' => $result['deactivated'],
            ]);
        } catch (\Throwable $e) {
            Log::error('PRODUCT_XLSX_IMPORT_FAILED', [
                'path' => $this->xlsxPath,
                'error' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            throw $e;
        } finally {
            if (is_file($this->xlsxPath)) {
                @unlink($this->xlsxPath);
            }
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('PRODUCT_XLSX_IMPORT_JOB_FAILED', [
            'path' => $this->xlsxPath,
            'error' => $exception?->getMessage(),
            'exception' => $exception !== null ? $exception::class : null,
        ]);
    }
}

