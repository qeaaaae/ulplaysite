<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsRequest;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\News;
use App\Services\ImageService;
use App\Support\StrHelper;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class NewsController extends Controller
{
    use Concerns\CleansUpContentImages;

    public function __construct(
        private readonly ImageService $imageService,
    ) {}
    public function index(Request $request): View
    {
        $q = (string) $request->input('q', '');
        $like = '%' . StrHelper::escapeForLike($q) . '%';
        $news = News::with('author')
            ->when($q !== '', fn ($query) => $query->where('title', 'like', $like)->orWhere('description', 'like', $like))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return view('admin.news.index', ['metaTitle' => 'Новости', 'news' => $news, 'search' => $q]);
    }

    public function create(): View
    {
        $import = session()->pull('news_import', []);

        return view('admin.news.form', [
            'metaTitle' => 'Новая новость',
            'news' => new News([
                'title'       => $import['title'] ?? '',
                'description' => $import['description'] ?? '',
                'content'     => $import['content'] ?? '',
            ]),
            'importCoverUrl' => $import['cover_url'] ?? '',
        ]);
    }

    public function parseUrl(Request $request): JsonResponse
    {
        $request->validate(['url' => ['required', 'url', 'max:1000']]);

        $url = $request->input('url');

        try {
            $http = Http::timeout(15)->withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                'Accept-Language' => 'ru-RU,ru;q=0.9',
            ]);

            // На локальном окружении CA-бандл может отсутствовать
            if (!app()->isProduction()) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get($url);
        } catch (\Throwable $e) {
            return response()->json(['result' => false, 'error' => 'Ошибка соединения: ' . $e->getMessage()], 422);
        }

        if (!$response->ok()) {
            return response()->json(['result' => false, 'error' => 'Сервер вернул ' . $response->status()], 422);
        }

        $html = $response->body();

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Title: og:title → h1 → <title>
        $title = '';
        foreach ($xpath->query('//meta[@property="og:title"]') as $node) {
            $title = trim((string) $node->getAttribute('content'));
            if ($title) break;
        }
        if (!$title) {
            foreach ($xpath->query('//h1') as $node) {
                $title = trim($node->textContent);
                if ($title) break;
            }
        }
        if (!$title) {
            foreach ($xpath->query('//title') as $node) {
                $title = preg_replace('/\s*[|–\-]\s*gamemag\.ru.*$/iu', '', $node->textContent);
                $title = trim((string) $title);
                break;
            }
        }

        // Content from .content-text inside .center-content
        $contentNode = null;
        foreach ($xpath->query('//*[contains(@class,"center-content")]//*[contains(@class,"content-text")]') as $node) {
            $contentNode = $node;
            break;
        }
        if (!$contentNode) {
            foreach ($xpath->query('//*[contains(@class,"content-text")]') as $node) {
                $contentNode = $node;
                break;
            }
        }

        if (!$contentNode) {
            return response()->json(['result' => false, 'error' => 'Блок с контентом не найден. Проверьте ссылку.'], 422);
        }

        $markdown = trim($this->nodeToMarkdown($contentNode));
        // Collapse 3+ blank lines to 2
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown);

        // First paragraph as description (strip Markdown tokens)
        $description = '';
        if (preg_match('/^(.+?)(?:\n\n|$)/s', $markdown, $m)) {
            $description = trim(preg_replace('/[*_\[\]#`~>]/', '', $m[1]));
            $description = mb_substr(preg_replace('/\s+/', ' ', $description), 0, 300);
        }

        $coverUrl = $this->extractCoverUrl($xpath, $url);

        session()->put('news_import', [
            'title'       => $title,
            'description' => $description,
            'content'     => $markdown,
            'cover_url'   => $coverUrl,
        ]);

        return response()->json([
            'result'   => true,
            'redirect' => route('admin.news.create'),
            'title'    => $title,
        ]);
    }

    /** Tags whose entire subtree should be silently dropped */
    private const SKIP_TAGS = [
        'script', 'style', 'noscript', 'svg', 'button', 'input',
        'select', 'textarea', 'figure', 'picture',
    ];

    /** CSS-class substrings that mark gamemag.ru UI widgets to skip */
    private const SKIP_CLASS_PATTERNS = [
        'vote-toggle', 'vote-btn', 'gm-btn', 'icon-wrap',
        'about-game', 'left-content', 'right-content',
        'support-item', 'platforms', 'categories', 'tags', 'genres',
    ];

    private function shouldSkip(DOMElement $el): bool
    {
        $tag = strtolower($el->tagName);
        if (in_array($tag, self::SKIP_TAGS, true)) {
            return true;
        }
        $class = $el->getAttribute('class');
        if ($class) {
            foreach (self::SKIP_CLASS_PATTERNS as $pattern) {
                if (str_contains($class, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function nodeToMarkdown(DOMNode $node, int $listDepth = 0): string
    {
        $result = '';

        foreach ($node->childNodes as $child) {
            // Plain text: collapse whitespace
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = preg_replace('/[\r\n\t]+/', ' ', $child->textContent);
                $result .= (string) $text;
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            /** @var DOMElement $child */
            if ($this->shouldSkip($child)) {
                continue;
            }

            $tag = strtolower($child->tagName);

            // Iframe handled before the empty-content guard (has no text children)
            if ($tag === 'iframe') {
                $result .= $this->iframeToMarkdown($child);
                continue;
            }

            $inner = $this->nodeToMarkdown($child, $listDepth);
            $t     = trim($inner);

            // Skip nodes that produce no visible text
            if ($t === '' && !in_array($tag, ['br', 'hr', 'img'], true)) {
                continue;
            }

            $result .= match ($tag) {
                // Block
                'p'                   => $this->paragraphToMarkdown($child, $t),
                'h1'                  => "\n\n# " . $t . "\n\n",
                'h2'                  => "\n\n## " . $t . "\n\n",
                'h3'                  => "\n\n### " . $t . "\n\n",
                'h4'                  => "\n\n#### " . $t . "\n\n",
                'h5'                  => "\n\n##### " . $t . "\n\n",
                'h6'                  => "\n\n###### " . $t . "\n\n",
                'hr'                  => "\n\n---\n\n",
                'ul'                  => "\n\n" . $this->listToMarkdown($child, false, $listDepth) . "\n\n",
                'ol'                  => "\n\n" . $this->listToMarkdown($child, true, $listDepth) . "\n\n",
                'blockquote'          => $this->blockquoteToMarkdown($t),
                'pre'                 => "\n\n```\n" . rtrim($child->textContent) . "\n```\n\n",
                // Inline — пробелы выносим наружу маркеров, чтобы не терять пробел
                // между словом и тегом: `слово<strong> текст` → `слово **текст**`
                'strong', 'b'         => $this->wrapInline('**', $inner),
                'em', 'i'             => $this->wrapInline('*', $inner),
                'del', 's', 'strike'  => $this->wrapInline('~~', $inner),
                'code'                => '`' . trim($child->textContent) . '`',
                'br'                  => "  \n",
                'a'                   => $this->linkNodeToMarkdown($child, $inner),
                // Discard media
                'img', 'video', 'audio', 'source' => '',
                // Pass-through containers
                default               => $inner,
            };
        }

        return $result;
    }

    /**
     * Оборачивает инлайн-текст в маркеры Markdown, сохраняя крайние пробелы снаружи.
     * Без этого `слово<strong> текст</strong>` → `слово** текст**` (пробел внутри).
     */
    private function wrapInline(string $marker, string $inner): string
    {
        $trimmed = trim($inner);
        if ($trimmed === '') {
            return $inner; // сохраняем как есть — пустой или только пробел
        }

        $leading  = ($inner !== '' && $inner[0] === ' ') ? ' ' : '';
        $trailing = ($inner !== '' && $inner[-1] === ' ') ? ' ' : '';

        return $leading . $marker . $trimmed . $marker . $trailing;
    }

    private function blockquoteToMarkdown(string $innerMarkdown): string
    {
        $lines  = explode("\n", trim($innerMarkdown));
        $quoted = array_map(static fn (string $l) => '> ' . $l, $lines);

        return "\n\n" . implode("\n", $quoted) . "\n\n";
    }

    /**
     * Пропускаем параграфы типа «Читайте также: ...» —
     * это редакционные перелинковки gamemag.ru, не часть статьи.
     */
    private function paragraphToMarkdown(DOMElement $node, string $innerMarkdown): string
    {
        $plainText = trim($node->textContent);

        if (
            str_starts_with($plainText, 'Читайте также') ||
            str_starts_with($plainText, 'Читайте также:') ||
            preg_match('/^Читайте также/ui', $plainText)
        ) {
            return '';
        }

        return "\n\n" . $innerMarkdown . "\n\n";
    }

    /**
     * Конвертируем <iframe> с видео в специальный маркер вида @youtube[ID].
     * MarkdownService затем заменяет маркеры на готовые <iframe> после рендера Markdown,
     * обходя ограничение html_input: strip.
     */
    private function iframeToMarkdown(DOMElement $node): string
    {
        $src = trim($node->getAttribute('src'));

        if (!$src) {
            return '';
        }

        // YouTube: https://www.youtube.com/embed/VIDEO_ID
        if (preg_match('~youtube\.com/embed/([A-Za-z0-9_\-]+)~', $src, $m)) {
            return "\n\n@youtube[{$m[1]}]\n\n";
        }

        // Rutube: https://rutube.ru/play/embed/VIDEO_ID или /embed/VIDEO_ID
        if (preg_match('~rutube\.ru/(?:play/embed|embed)/([A-Za-z0-9]+)~', $src, $m)) {
            return "\n\n@rutube[{$m[1]}]\n\n";
        }

        // VK: https://vk.com/video_ext.php?oid=X&id=Y
        if (preg_match('~vk\.com/video_ext\.php\?oid=(-?\d+)&(?:amp;)?id=(\d+)~', $src, $m)) {
            return "\n\n@vkvideo[{$m[1]}_{$m[2]}]\n\n";
        }

        return '';
    }

    private function linkNodeToMarkdown(DOMElement $node, string $inner): string
    {
        $href = trim($node->getAttribute('href'));
        $text = trim($inner);

        if (!$text) {
            return '';
        }
        if (!$href || $href === '#' || str_starts_with($href, 'javascript:')) {
            return $text;
        }
        if (str_starts_with($href, '/')) {
            $href = 'https://gamemag.ru' . $href;
        }

        // Не делаем ссылку на gamemag.ru — просто текст
        if (str_contains($href, 'gamemag.ru')) {
            return $text;
        }

        return '[' . $text . '](' . $href . ')';
    }

    private function listToMarkdown(DOMNode $node, bool $ordered, int $depth = 0): string
    {
        $items  = [];
        $i      = 1;
        $indent = str_repeat('  ', $depth);

        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            /** @var DOMElement $child */
            if (strtolower($child->tagName) !== 'li') {
                continue;
            }

            // Separate nested lists from inline text
            $textParts  = '';
            $nestedPart = '';

            foreach ($child->childNodes as $liChild) {
                if ($liChild->nodeType === XML_ELEMENT_NODE) {
                    $liTag = strtolower($liChild->tagName);
                    if ($liTag === 'ul') {
                        $nestedPart .= "\n" . $this->listToMarkdown($liChild, false, $depth + 1);
                        continue;
                    }
                    if ($liTag === 'ol') {
                        $nestedPart .= "\n" . $this->listToMarkdown($liChild, true, $depth + 1);
                        continue;
                    }
                }
                $textParts .= $this->nodeToMarkdown($liChild, $depth);
            }

            $prefix  = $ordered ? ($i++) . '.' : '-';
            $items[] = $indent . $prefix . ' ' . trim($textParts) . $nestedPart;
        }

        return implode("\n", $items);
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['author_id'] = auth()->id();
        $images = $request->file('images', []);
        $importCoverUrl = (string) $request->input('import_cover_url', '');
        unset($validated['images']);

        /** @var News $news */
        $news = News::create($validated);

        $this->cleanupUnusedContentImages($request, $news->content);

        if (! empty($images)) {
            $news->images()->delete();
            foreach (array_slice($images, 0, 5) as $index => $file) {
                $news->images()->create([
                    'path' => $this->imageService->store($file, 'news'),
                    'is_cover' => $index === 0,
                    'position' => $index,
                ]);
            }
        } elseif ($importCoverUrl !== '') {
            $this->attachImportedCover(news: $news, imageUrl: $importCoverUrl);
        }
        return redirect()->route('admin.news.edit', $news)->with('message', 'Новость создана');
    }

    public function edit(News $news): View
    {
        $news->load(['author', 'views.user']);

        /** @var Collection<int, \App\Models\NewsView> $views */
        $views = $news->views()->with('user')->orderByDesc('created_at')->get();

        return view('admin.news.form', [
            'metaTitle' => $news->title,
            'news' => $news,
            'views' => $views,
        ]);
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $validated = $request->validated();
        $images = $request->file('images', []);
        $deleteIds = $request->input('delete_images', []);
        unset($validated['images']);
        $news->update($validated);

        $this->cleanupUnusedContentImages($request, $news->content);

        if (! empty($deleteIds)) {
            $news->images()->whereIn('id', $deleteIds)->delete();
        }

        if (! empty($images)) {
            $existing = $news->images()->count();
            if ($existing >= 5) {
                return redirect()
                    ->back()
                    ->withErrors(['images' => 'Максимум 5 изображений. Удалите лишние, чтобы добавить новые.'])
                    ->withInput();
            }
            $maxToAdd = max(0, 5 - $existing);
            if ($maxToAdd > 0) {
                $startPosition = (int) $news->images()->max('position') + 1;
                foreach (array_slice($images, 0, $maxToAdd) as $offset => $file) {
                    $news->images()->create([
                        'path' => $this->imageService->store($file, 'news'),
                        'is_cover' => $existing === 0 && $offset === 0,
                        'position' => $startPosition + $offset,
                    ]);
                }
            }
        }
        return redirect()->back()->with('message', 'Новость обновлена');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();
        return redirect()->route('admin.news.index')->with('message', 'Новость удалена');
    }

    private function extractCoverUrl(DOMXPath $xpath, string $baseUrl): string
    {
        foreach ($xpath->query('//*[contains(@class,"overview")]//img[contains(@class,"overview__img")]') as $node) {
            /** @var DOMElement $node */
            $src = trim((string) $node->getAttribute('src'));
            if ($src !== '') {
                return $this->absolutizeUrl($src, $baseUrl);
            }
        }

        foreach ($xpath->query('//meta[@property="og:image"]') as $node) {
            /** @var DOMElement $node */
            $src = trim((string) $node->getAttribute('content'));
            if ($src !== '') {
                return $this->absolutizeUrl($src, $baseUrl);
            }
        }

        return '';
    }

    private function absolutizeUrl(string $url, string $baseUrl): string
    {
        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }

        $parts = parse_url($baseUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? 'gamemag.ru';

        if (str_starts_with($url, '//')) {
            return $scheme . ':' . $url;
        }

        if (str_starts_with($url, '/')) {
            return $scheme . '://' . $host . $url;
        }

        return $scheme . '://' . $host . '/' . ltrim($url, '/');
    }

    private function attachImportedCover(News $news, string $imageUrl): void
    {
        if (!preg_match('~^https?://~i', $imageUrl)) {
            return;
        }

        try {
            $http = Http::timeout(15)->withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                'Accept-Language' => 'ru-RU,ru;q=0.9',
            ]);

            if (!app()->isProduction()) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get($imageUrl);
            if (!$response->ok()) {
                return;
            }

            $tmpPath = tempnam(sys_get_temp_dir(), 'news_cover_');
            if (!$tmpPath) {
                return;
            }

            file_put_contents($tmpPath, $response->body());

            $uploaded = new UploadedFile(
                path: $tmpPath,
                originalName: basename(parse_url($imageUrl, PHP_URL_PATH) ?: 'cover.jpg'),
                mimeType: $response->header('Content-Type', 'image/jpeg'),
                test: true,
            );

            $path = $this->imageService->store($uploaded, 'news');

            $news->images()->create([
                'path' => $path,
                'is_cover' => true,
                'position' => 0,
            ]);
        } catch (\Throwable) {
            // ignore cover import errors
        }
    }
}
