<?php

declare(strict_types=1);

namespace App\Services\Gamemag;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Http;

/**
 * Парсинг статей gamemag.ru (HTML → markdown) и ссылок с ленты.
 * Логика перенесена из {@see \App\Http\Controllers\Admin\NewsController::parseUrl}.
 */
final class GamemagArticleParser
{
    public function fetchPageHtml(string $url): string
    {
        try {
            $http = Http::timeout(15)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                'Accept-Language' => 'ru-RU,ru;q=0.9',
            ]);
            if (! app()->isProduction()) {
                $http = $http->withoutVerifying();
            }
            $response = $http->get($url);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Ошибка соединения: ' . $e->getMessage());
        }
        if (! $response->ok()) {
            throw new \RuntimeException('Сервер вернул ' . $response->status());
        }

        return $response->body();
    }

    /**
     * @return array{title: string, description: string, content: string, cover_url: string}
     */
    public function parseArticleFromUrl(string $url): array
    {
        $html = $this->fetchPageHtml($url);

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $title = '';
        foreach ($xpath->query('//meta[@property="og:title"]') as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $title = trim((string) $node->getAttribute('content'));
            if ($title !== '') {
                break;
            }
        }
        if ($title === '') {
            foreach ($xpath->query('//h1') as $node) {
                $title = trim($node->textContent);
                if ($title !== '') {
                    break;
                }
            }
        }
        if ($title === '') {
            foreach ($xpath->query('//title') as $node) {
                $title = preg_replace('/\s*[|–\-]\s*gamemag\.ru.*$/iu', '', $node->textContent);
                $title = trim((string) $title);
                break;
            }
        }

        $contentNode = null;
        foreach ($xpath->query('//*[contains(@class,"center-content")]//*[contains(@class,"content-text")]') as $node) {
            $contentNode = $node;
            break;
        }
        if ($contentNode === null) {
            foreach ($xpath->query('//*[contains(@class,"content-text")]') as $node) {
                $contentNode = $node;
                break;
            }
        }

        if ($contentNode === null) {
            throw new \RuntimeException('Блок с контентом не найден. Проверьте ссылку.');
        }

        $markdown = trim($this->nodeToMarkdown($contentNode));
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown) ?? $markdown;

        $description = '';
        if (preg_match('/^(.+?)(?:\n\n|$)/s', $markdown, $m)) {
            $description = trim(preg_replace('/[*_\[\]#`~>]/', '', $m[1]));
            $description = mb_substr(preg_replace('/\s+/', ' ', $description), 0, 300);
        }

        $coverUrl = $this->extractCoverUrl($xpath, $url);

        return [
            'title' => $title,
            'description' => $description,
            'content' => $markdown,
            'cover_url' => $coverUrl,
        ];
    }

    /**
     * Ссылки на материалы /news/{id}/... с главной (блок .news-item).
     *
     * @return list<string>
     */
    public function extractArticleUrlsFromListingHtml(string $html, string $baseUrl): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $scopeNode = null;
        foreach ($xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' news-item-wrap ')]") as $node) {
            if ($node instanceof DOMElement) {
                $scopeNode = $node;
                break;
            }
        }

        $seen = [];
        $query = ".//div[contains(@class,'news-item')]//a[contains(@class,'news-item__link') or contains(@class,'news-item__text')][@href]";
        $nodes = $scopeNode instanceof DOMElement
            ? $xpath->query($query, $scopeNode)
            : $xpath->query("//div[contains(@class,'news-item')]//a[contains(@class,'news-item__link') or contains(@class,'news-item__text')][@href]");

        foreach ($nodes as $a) {
            if (! $a instanceof \DOMElement) {
                continue;
            }
            $href = trim($a->getAttribute('href'));
            if ($href === '') {
                continue;
            }
            $href = preg_replace('/#.*/', '', $href) ?? $href;
            if (! preg_match('#^/news/\d+#', $href)) {
                continue;
            }
            $absolute = $this->absolutizeUrl($href, $baseUrl);
            $seen[$absolute] = true;
        }

        return array_keys($seen);
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
        $plainText = trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');

        // Рекламные вставки на gamemag обычно имеют вид:
        // "Реклама. ООО ... ИНН ..." и следующей строкой "erid: ..."
        // Нам нужно вырезать весь рекламный блок, чтобы в итоговой новости была только статья.
        if ($plainText !== '') {
            // 1) Всегда выкидываем строки с erid (в любом регистре и с разными разделителями)
            if (preg_match('/\berid\s*[:：]/ui', $plainText)) {
                return '';
            }

            // 2) Вырезаем абзацы, которые начинаются с "Реклама" (или похожих формулировок)
            // Ограничиваем "с начала строки", чтобы не выкидывать случайные упоминания внутри текста статьи.
            if (
                preg_match('/^(?:реклама|рекламная информация|информация о рекламе|рекламный блок)\b/ui', $plainText) ||
                preg_match('/^реклама[\.\s:,-]*(?:о о о)?\b/ui', $plainText)
            ) {
                return '';
            }

            // 3) Бывают варианты, где "Реклама" не в первом слове, но есть ИНН и короткий рекламный хвост
            // (оставляем это менее строгим, чтобы не ломать статьи)
            if (
                preg_match('/\bИНН\b\s*\d{10,14}/ui', $plainText)
                && preg_match('/\bреклама\b/ui', $plainText)
                && mb_strlen($plainText) < 220
            ) {
                return '';
            }
        }

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
                if ($liChild instanceof DOMElement) {
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


}
