<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\Avito\AvitoCachedListingUrlLookup;
use App\Services\Avito\AvitoItemPayloadImageUrls;
use App\Services\Avito\AvitoListingImagesFetcher;
use App\Services\ImageService;
use App\Services\VideoEmbedService;
use App\Support\StrHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductController extends Controller
{
    private const DEFAULT_PRODUCT_IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    public function __construct(
        private readonly ImageService $imageService,
        private readonly AvitoCachedListingUrlLookup $avitoCachedListingUrlLookup,
        private readonly AvitoListingImagesFetcher $avitoListingImagesFetcher,
    ) {}
    /** Только дочерние категории (поколения / линейки) — для товаров. */
    private function productLeafCategories()
    {
        return Category::query()
            ->whereNotNull('parent_id')
            ->with('parent')
            ->orderBy('name')
            ->get();
    }

    public function index(Request $request): View
    {
        $q = (string) $request->input('q', '');
        $like = '%' . StrHelper::escapeForLike($q) . '%';
        $products = Product::with('category')
            ->when($q !== '', fn ($query) => $query->where(fn ($q2) => $q2->where('title', 'like', $like)
                ->orWhere('slug', 'like', $like)
                ->orWhere('description', 'like', $like)))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('admin.products.index', ['metaTitle' => 'Товары', 'products' => $products, 'search' => $q]);
    }

    public function importXlsx(Request $request): RedirectResponse|JsonResponse
    {
        // Импорт может занимать долго из-за загрузки изображений.
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $validated = $request->validate([
            'xlsx_file' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ], [
            'xlsx_file.required' => 'Выберите XLSX-файл для импорта.',
            'xlsx_file.mimes' => 'Поддерживается только формат XLSX.',
            'xlsx_file.max' => 'Максимальный размер файла: 20 МБ.',
        ]);

        $wantsJson = $request->expectsJson() || $request->ajax();

        try {
            $result = $this->runXlsxImport($validated['xlsx_file']);
        } catch (\Throwable $e) {
            $msg = 'Ошибка импорта: ' . $e->getMessage();
            if ($wantsJson) {
                return response()->json([
                    'message' => $msg,
                    'errors' => ['xlsx_file' => [$msg]],
                ], 422);
            }

            return redirect()
                ->route('admin.products.index')
                ->withErrors(['xlsx_file' => $msg]);
        }

        $flash = "Импорт завершён. Создано: {$result['created']}, обновлено: {$result['updated']}, пропущено: {$result['skipped']}.";

        if ($wantsJson) {
            $request->session()->flash('message', $flash);

            return response()->json([
                'message' => $flash,
                'redirect' => route('admin.products.index'),
            ]);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('message', $flash);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'metaTitle' => 'Новый товар',
            'product' => new Product(),
            'categories' => $this->productLeafCategories(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        unset($validated['images']);

        /** @var Product $product */
        $product = Product::create($validated);

        if (! empty($images)) {
            $product->images()->delete();
            foreach (array_slice($images, 0, 5) as $index => $file) {
                $product->images()->create([
                    'path' => $this->imageService->store($file, 'products'),
                    'is_cover' => $index === 0,
                    'position' => $index,
                ]);
            }
        }
        return redirect()->route('admin.products.index')->with('message', 'Товар создан');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'metaTitle' => $product->name,
            'product' => $product,
            'categories' => $this->productLeafCategories(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        $deleteIds = $request->input('delete_images', []);
        unset($validated['images']);
        $product->update($validated);

        if (! empty($deleteIds)) {
            $product->images()->whereIn('id', $deleteIds)->delete();
        }

        if (! empty($images)) {
            $existing = $product->images()->count();
            if ($existing >= 5) {
                return redirect()
                    ->back()
                    ->withErrors(['images' => 'Максимум 5 изображений. Удалите лишние, чтобы добавить новые.'])
                    ->withInput();
            }
            $maxToAdd = max(0, 5 - $existing);
            if ($maxToAdd > 0) {
                $startPosition = (int) $product->images()->max('position') + 1;
                foreach (array_slice($images, 0, $maxToAdd) as $offset => $file) {
                    $product->images()->create([
                        'path' => $this->imageService->store($file, 'products'),
                        'is_cover' => $existing === 0 && $offset === 0,
                        'position' => $startPosition + $offset,
                    ]);
                }
            }
        }
        return redirect()->back()->with('message', 'Товар обновлён');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('message', 'Товар удалён');
    }

    /**
     * @return array{created:int,updated:int,skipped:int}
     */
    private function runXlsxImport(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $leafCategories = Category::query()->whereNotNull('parent_id')->get(['id', 'name', 'slug']);
        $fallbackCategoryId = (int) ($leafCategories->firstWhere('slug', 'accessories')->id ?? $leafCategories->first()->id ?? 0);

        if ($fallbackCategoryId <= 0) {
            throw new \RuntimeException('В системе нет категорий товаров для импорта.');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if ($this->shouldSkipSheet($sheet->getTitle())) {
                continue;
            }

            $headers = $this->readHeaders($sheet);
            if ($headers === []) {
                continue;
            }

            $highestRow = max(5, (int) $sheet->getHighestDataRow());

            for ($row = 5; $row <= $highestRow; $row++) {
                $title = $this->getCell($sheet, $headers, ['название объявления', 'title'], $row);
                if ($title === '') {
                    $skipped++;
                    continue;
                }

                $status = mb_strtolower($this->getCell($sheet, $headers, ['avitostatus'], $row), 'UTF-8');
                if ($status !== '' && $status !== 'активно' && $status !== 'active') {
                    $skipped++;
                    continue;
                }

                $avitoId = $this->normalizeAvitoItemId($this->getCell($sheet, $headers, ['номер объявления на авито', 'avitoid', 'id'], $row));
                $avitoListingUrl = $this->resolveAvitoListingUrlFromSheet($sheet, $headers, $row, $avitoId);
                $description = $this->sanitizeDescription($this->getCell($sheet, $headers, ['описание объявления', 'description'], $row));
                $price = $this->parsePrice($this->getCell($sheet, $headers, ['цена', 'price'], $row));
                $videoUrl = $this->normalizeVideoUrl($this->getCell($sheet, $headers, ['ссылка на видео', 'videourl'], $row));
                $stock = max(0, (int) $this->parsePrice($this->getCell($sheet, $headers, ['кол-во', 'колво', 'stock'], $row), 1.0));
                $inStock = $stock > 0;
                $categoryHint = $this->getCell($sheet, $headers, ['тип товара', 'подвид товара', 'вид товара', 'servicetype', 'servicesubtype'], $row);
                $categoryId = $this->resolveCategoryId($leafCategories, $categoryHint, $fallbackCategoryId);
                $slugBase = Str::slug($title) ?: 'avito-item';
                $slug = $avitoId !== '' ? "{$slugBase}-{$avitoId}" : "{$slugBase}-" . Str::lower(Str::random(8));

                $payload = [
                    'title' => $title,
                    'slug' => $slug,
                    'avito_item_id' => $avitoId !== '' ? $avitoId : null,
                    'avito_url' => $avitoListingUrl,
                    'description' => $description !== '' ? $description : null,
                    'video_url' => $videoUrl,
                    'price' => $price,
                    'category_id' => $categoryId,
                    'in_stock' => $inStock,
                    'stock' => $stock,
                    'discount_percent' => null,
                    'is_new' => false,
                    'is_recommended' => false,
                ];

                $product = $this->findProductForXlsxImport($avitoId, $slug);
                if ($product !== null) {
                    $product->update($payload);
                    $updated++;
                } else {
                    $product = Product::query()->create($payload);
                    $created++;
                }

                $imageUrls = $this->collectImageUrlsFromSheet($sheet, $headers, $row);
                $this->syncProductImagesAfterXlsxRow(
                    $product,
                    $imageUrls,
                    $avitoListingUrl,
                    $avitoId,
                );
            }
        }

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    private function shouldSkipSheet(string $title): bool
    {
        $normalized = mb_strtolower(trim($title), 'UTF-8');
        return $normalized === 'инструкция' || str_starts_with($normalized, 'спр-');
    }

    /**
     * Несколько строк подписей (2–4) — в шаблоне Avito часто RU/EN в разных строках.
     *
     * @return array<string,int>
     */
    private function readHeaders(Worksheet $sheet): array
    {
        $headers = [];
        foreach ([2, 3, 4] as $headerRow) {
            $highestColumn = (string) $sheet->getHighestDataColumn($headerRow);
            if ($highestColumn === 'A' && $sheet->getCell("A{$headerRow}")->getValue() === null) {
                continue;
            }
            $row = $sheet->rangeToArray("A{$headerRow}:{$highestColumn}{$headerRow}", null, true, false)[0] ?? [];
            foreach ($row as $index => $value) {
                $key = $this->normalizeHeader((string) ($value ?? ''));
                if ($key !== '') {
                    $headers[$key] = $index + 1;
                }
            }
        }

        return $headers;
    }

    /**
     * @param array<string,int> $headers
     * @param array<int,string> $variants
     */
    private function getCell(Worksheet $sheet, array $headers, array $variants, int $row): string
    {
        foreach ($variants as $variant) {
            $key = $this->normalizeHeader($variant);
            if (! isset($headers[$key])) {
                continue;
            }
            $columnLetter = Coordinate::stringFromColumnIndex($headers[$key]);
            $value = $this->stringFromSpreadsheetCell($sheet->getCell("{$columnLetter}{$row}"));
            $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
            if ($value !== '') {
                return $value;
            }
        }
        return '';
    }

    private function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header), 'UTF-8');
        $header = preg_replace('/[\x{00A0}\s]+/u', ' ', $header) ?? '';
        $header = trim($header);
        $header = preg_replace('/[:：]+$/u', '', $header) ?? '';

        return trim($header);
    }

    private function stringFromSpreadsheetCell(Cell $cell): string
    {
        $hyperlink = $cell->getHyperlink();
        if ($hyperlink !== null) {
            $u = trim((string) $hyperlink->getUrl());
            if ($u !== '' && (str_starts_with($u, 'http://') || str_starts_with($u, 'https://'))) {
                return $u;
            }
        }

        $raw = $cell->getValue();
        if (is_string($raw)) {
            $t = trim($raw);
            if ($t !== '' && str_starts_with($t, '=')) {
                if (preg_match('/^=\s*HYPERLINK\s*\(\s*["\']([^"\']+)["\']/iu', $t, $m)) {
                    return $m[1];
                }
                if (preg_match('/^=\s*HYPERLINK\s*\(\s*["\']([^"\']+)["\']\s*;/u', $t, $m)) {
                    return $m[1];
                }
            }
        }

        $value = (string) $cell->getCalculatedValue();
        if ($value !== '') {
            return $value;
        }

        return (string) ($cell->getOldCalculatedValue() ?? '');
    }

    private function resolveAvitoListingUrlFromSheet(Worksheet $sheet, array $headers, int $row, string $avitoId): ?string
    {
        $raw = $this->getCell($sheet, $headers, [
            'ссылка на объявление',
            'публичная ссылка на объявление',
            'url объявления',
            'страница объявления',
            'адрес объявления',
            'adurl',
            'ad url',
            'itemurl',
            'item url',
            'avitourl',
            'publicurl',
            'public url',
            'weburl',
            'web url',
            'ссылка',
            'url',
            'link',
        ], $row);

        $fromSheet = $this->normalizeAvitoListingUrl($raw);
        if ($fromSheet !== null) {
            return $fromSheet;
        }

        if ($avitoId === '') {
            return null;
        }

        return $this->avitoCachedListingUrlLookup->resolveByItemId($avitoId);
    }

    private function sanitizeDescription(string $description): string
    {
        if ($description === '') {
            return '';
        }
        $plain = strip_tags($description);
        return trim(preg_replace('/\s+/u', ' ', $plain) ?? '');
    }

    private function parsePrice(string $price, float $default = 0.0): float
    {
        if ($price === '') {
            return $default;
        }
        $normalized = str_replace([',', ' '], ['.', ''], $price);
        $normalized = preg_replace('/[^\d.]/', '', $normalized) ?? '';
        return is_numeric($normalized) ? (float) $normalized : $default;
    }

    private function normalizeVideoUrl(string $videoUrl): ?string
    {
        if ($videoUrl === '') {
            return null;
        }
        if (! app(VideoEmbedService::class)->isValidUrl($videoUrl)) {
            return null;
        }
        return $videoUrl;
    }

    /**
     * @return array<int,string>
     */
    private function splitImageUrls(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $parts = preg_split('/[|\n\r,;]+/u', $value) ?: [];
        $urls = [];
        foreach ($parts as $part) {
            $url = trim($part);
            if ($url === '') {
                continue;
            }
            if (str_starts_with($url, 'http://')) {
                $url = 'https://' . substr($url, 7);
            }
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            $urls[] = $url;
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param array<string,int> $headers
     * @return array<int,string>
     */
    private function collectImageUrlsFromSheet(Worksheet $sheet, array $headers, int $row): array
    {
        $urls = [];

        $mainValue = $this->getCell($sheet, $headers, [
            'ссылки на фото',
            'ссылка на фото',
            'imageurls',
            'images',
            'url фото',
            'urls изображений',
        ], $row);
        if ($mainValue !== '') {
            $urls = array_merge($urls, $this->splitImageUrls($mainValue));
        }

        foreach ($headers as $header => $columnIndex) {
            $isImageColumn = str_contains($header, 'фото')
                || str_contains($header, 'изображ')
                || str_contains($header, 'image')
                || str_contains($header, 'photo')
                || str_contains($header, 'picture');
            if (! $isImageColumn || str_contains($header, 'видео') || str_contains($header, 'video')) {
                continue;
            }

            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $raw = trim($this->stringFromSpreadsheetCell($sheet->getCell("{$columnLetter}{$row}")));
            if ($raw === '') {
                continue;
            }

            $urls = array_merge($urls, $this->splitImageUrls($raw));
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param \Illuminate\Support\Collection<int,Category> $leafCategories
     */
    private function resolveCategoryId($leafCategories, string $hint, int $fallbackCategoryId): int
    {
        $hint = mb_strtolower(trim($hint), 'UTF-8');
        if ($hint !== '') {
            $exact = $leafCategories->first(function (Category $category) use ($hint): bool {
                return mb_strtolower($category->name, 'UTF-8') === $hint;
            });
            if ($exact !== null) {
                return (int) $exact->id;
            }
        }

        return $fallbackCategoryId;
    }

    /**
     * 1) Прямые URL из XLSX (не autoload).
     * 2) imagesUrls из ответа Avito (active-items.json / API), если есть.
     * 3) Парсинг публичной карточки по URL из файла или из кэша/API.
     *
     * @param array<int,string> $sheetImageUrls
     */
    private function syncProductImagesAfterXlsxRow(Product $product, array $sheetImageUrls, ?string $listingUrl, string $avitoItemId): void
    {
        $max = 5;
        /** @var array<int,string> $storedPaths */
        $storedPaths = [];

        $appendStored = function (?string $storedPath) use (&$storedPaths, $max): bool {
            if ($storedPath === null || $storedPath === '') {
                return false;
            }
            $storedPaths[] = $storedPath;

            return count($storedPaths) >= $max;
        };

        $directUrls = array_values(array_filter(
            $sheetImageUrls,
            fn (string $u): bool => ! $this->isAutoloadItemsFeedImageUrl($u),
        ));

        foreach (array_slice($directUrls, 0, $max * 2) as $url) {
            if ($appendStored($this->storeImageFromUrl($url))) {
                $this->replaceProductImagesFromStoredPaths($product, $storedPaths);

                return;
            }
        }

        if ($storedPaths !== []) {
            $this->replaceProductImagesFromStoredPaths($product, $storedPaths);

            return;
        }

        $listingForHtml = $listingUrl !== null && trim($listingUrl) !== '' ? trim($listingUrl) : null;
        if ($listingForHtml === null && $avitoItemId !== '') {
            $listingForHtml = $this->avitoCachedListingUrlLookup->resolveByItemId($avitoItemId);
        }

        if ($avitoItemId !== '') {
            $payload = $this->avitoCachedListingUrlLookup->getItemPayloadMergedWithAccountDetails($avitoItemId);
            if ($payload !== null) {
                foreach (array_slice(AvitoItemPayloadImageUrls::fromItemArray($payload), 0, $max * 2) as $url) {
                    if ($appendStored($this->storeImageFromUrl($url))) {
                        $this->replaceProductImagesFromStoredPaths($product, $storedPaths);

                        return;
                    }
                }
            }
        }

        if ($storedPaths !== []) {
            $this->replaceProductImagesFromStoredPaths($product, $storedPaths);

            return;
        }

        if ($listingForHtml === null || $listingForHtml === '') {
            $this->ensureDefaultProductImage($product);
            return;
        }

        $fetched = $this->avitoListingImagesFetcher->fetchImageUrlsFromPublicListing($listingForHtml);
        foreach (array_slice($fetched, 0, $max) as $url) {
            if ($appendStored($this->storeImageFromUrl($url))) {
                break;
            }
        }

        if ($storedPaths === []) {
            $this->ensureDefaultProductImage($product);
            return;
        }

        $this->replaceProductImagesFromStoredPaths($product, $storedPaths);
    }

    /**
     * @param array<int,string> $storedPaths
     */
    private function replaceProductImagesFromStoredPaths(Product $product, array $storedPaths): void
    {
        $product->images()->delete();
        foreach (array_values($storedPaths) as $i => $path) {
            $product->images()->create([
                'path' => $path,
                'is_cover' => $i === 0,
                'position' => $i,
            ]);
        }
    }

    private function ensureDefaultProductImage(Product $product): void
    {
        if ($product->images()->exists()) {
            return;
        }

        $product->images()->create([
            'path' => self::DEFAULT_PRODUCT_IMAGE,
            'is_cover' => true,
            'position' => 0,
        ]);
    }

    private function isAutoloadItemsFeedImageUrl(string $url): bool
    {
        $u = mb_strtolower($url, 'UTF-8');

        return str_contains($u, 'avito.ru/autoload')
            || (str_contains($u, '/autoload/') && str_contains($u, 'items-to-feed'));
    }

    private function storeImageFromUrl(string $url): ?string
    {
        try {
            $http = Http::connectTimeout(7)->timeout(15)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9',
                'Referer' => 'https://www.avito.ru/',
            ]);
            if (! app()->isProduction()) {
                $http = $http->withoutVerifying();
            }

            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $res = $http->get($url);
                if ($res->status() === 429 && $attempt < 2) {
                    usleep(200000 * $attempt);
                    continue;
                }
                if (! $res->ok()) {
                    return null;
                }

                $body = $res->body();
                $ct = mb_strtolower((string) ($res->header('Content-Type') ?? ''), 'UTF-8');
                if (str_contains($ct, 'text/html') || str_contains($ct, 'application/json')) {
                    return null;
                }
                if ($body !== '' && (str_starts_with(ltrim($body), '<') || str_contains(substr($body, 0, 120), '<!DOCTYPE'))) {
                    return null;
                }

                $tmpPath = tempnam(sys_get_temp_dir(), 'xlsx_img_');
                if ($tmpPath === false) {
                    return null;
                }

                file_put_contents($tmpPath, $body);
                $mime = $ct !== '' && str_contains($ct, 'image/')
                    ? explode(';', $ct)[0]
                    : 'image/jpeg';
                $name = basename((string) (parse_url($url, PHP_URL_PATH) ?: 'image.jpg'));
                if (! preg_match('/\.(jpe?g|png|gif|webp)$/i', $name)) {
                    $name .= '.jpg';
                }
                $uploaded = new UploadedFile(
                    path: $tmpPath,
                    originalName: $name,
                    mimeType: $mime,
                    test: true,
                );

                return $this->imageService->store($uploaded, 'products');
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function normalizeAvitoItemId(string $raw): string
    {
        $raw = trim(preg_replace('/\s+/u', '', $raw) ?? '');
        if ($raw === '') {
            return '';
        }
        if (is_numeric($raw)) {
            return (string) (int) (float) $raw;
        }

        return $raw;
    }

    private function normalizeAvitoListingUrl(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, 'http://')) {
            $raw = 'https://' . substr($raw, 7);
        } elseif (str_starts_with($raw, 'www.')) {
            $raw = 'https://' . $raw;
        } elseif (preg_match('~^avito\.ru/~i', $raw) === 1) {
            $raw = 'https://www.' . $raw;
        }

        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            return $raw;
        }

        if (preg_match('~https?://[^\s<>"\'\]]+~iu', $raw, $m)) {
            $candidate = rtrim($m[0], '.,;)]');
            if (filter_var($candidate, FILTER_VALIDATE_URL)) {
                return $candidate;
            }
        }

        return null;
    }

    private function findProductForXlsxImport(string $avitoId, string $slug): ?Product
    {
        if ($avitoId !== '') {
            $byAvito = Product::query()->where('avito_item_id', $avitoId)->first();
            if ($byAvito !== null) {
                return $byAvito;
            }
        }

        return Product::query()->where('slug', $slug)->first();
    }

}
