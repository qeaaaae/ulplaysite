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
        try {
            $result = $controller->runXlsxImportFromPath($this->xlsxPath);
            Log::info('PRODUCT_XLSX_IMPORT_FINISHED', [
                'path' => $this->xlsxPath,
                'created' => $result['created'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
            ]);
        } catch (\Throwable $e) {
            Log::error('PRODUCT_XLSX_IMPORT_FAILED', [
                'path' => $this->xlsxPath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            if (is_file($this->xlsxPath)) {
                @unlink($this->xlsxPath);
            }
        }
    }
}

