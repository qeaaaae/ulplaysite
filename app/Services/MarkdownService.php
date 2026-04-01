<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class MarkdownService
{
    /**
     * Allowed color names and hex pattern for the {color:...}...{/color} syntax.
     */
    private const COLOR_PATTERN = '/\{color:(#[0-9a-fA-F]{3,6}|[a-zA-Z]{3,20})\}(.*?)\{\/color\}/s';

    public function render(string $content): string
    {
        // Вырезаем видео-маркеры до рендера Markdown, чтобы commonmark не трогал их.
        // Маркеры вида @youtube[ID], @rutube[ID], @vkvideo[OID_ID] — ставятся импортёром.
        [$content, $videoPlaceholders] = $this->extractVideoMarkers($content);

        $html = Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // Вставляем iframes обратно
        $html = strtr($html, $videoPlaceholders);

        $html = $this->addBlankTargetToLinks($html);
        $html = $this->processColorSyntax($html);

        return $html;
    }

    /**
     * Заменяем @youtube[ID], @rutube[ID], @vkvideo[OID_ID] уникальными плейсхолдерами,
     * возвращаем контент без маркеров и карту «плейсхолдер → iframe HTML».
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private function extractVideoMarkers(string $content): array
    {
        $placeholders = [];
        $i = 0;

        $content = preg_replace_callback(
            '/@(youtube|rutube|vkvideo)\[([A-Za-z0-9_\-]+)\]/u',
            function (array $m) use (&$placeholders, &$i): string {
                $key = '%%VIDEO_' . ($i++) . '%%';
                $placeholders[$key] = $this->buildVideoEmbed($m[1], $m[2]);
                return $key;
            },
            $content,
        ) ?? $content;

        return [$content, $placeholders];
    }

    private function buildVideoEmbed(string $type, string $id): string
    {
        // YouTube: фасад — показываем превью, iframe грузим только по клику.
        // Это убирает ~800 КБ JS и десятки запросов к Google при загрузке страницы.
        if ($type === 'youtube') {
            $safeId   = htmlspecialchars($id, ENT_QUOTES);
            $embedSrc = htmlspecialchars('https://www.youtube.com/embed/' . $id . '?autoplay=1', ENT_QUOTES);
            $thumbSrc = htmlspecialchars('https://img.youtube.com/vi/' . $id . '/maxresdefault.jpg', ENT_QUOTES);

            return <<<HTML
<div class="ulplay-video-embed aspect-video my-4 w-full overflow-hidden rounded-xl ring-1 ring-stone-200/50 bg-stone-900"
     x-data="{ playing: false }">
    <template x-if="!playing">
        <button type="button"
                @click="playing = true"
                class="relative w-full h-full flex items-center justify-center group cursor-pointer"
                aria-label="Воспроизвести видео">
            <img src="{$thumbSrc}"
                 class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-200"
                 loading="lazy" alt="" onerror="this.style.display='none'">
            <svg class="relative z-10 drop-shadow-xl group-hover:scale-110 transition-transform duration-200"
                 width="68" height="48" viewBox="0 0 68 48" aria-hidden="true">
                <path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55c-2.93.78-4.63 3.26-5.42 6.19C.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="#FF0000"/>
                <path d="M45 24 27 14v20z" fill="#fff"/>
            </svg>
        </button>
    </template>
    <template x-if="playing">
        <iframe src="{$embedSrc}"
                class="w-full h-full"
                frameborder="0" allowfullscreen
                allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
        </iframe>
    </template>
</div>
HTML;
        }

        // Rutube и VK — просто lazy iframe (превью недоступны без API)
        $src = match ($type) {
            'rutube'  => 'https://rutube.ru/play/embed/' . $id,
            'vkvideo' => (function () use ($id): string {
                [$oid, $vid] = explode('_', $id, 2) + ['', ''];
                return 'https://vk.com/video_ext.php?oid=' . $oid . '&id=' . $vid . '&autoplay=0';
            })(),
            default   => '',
        };

        if (!$src) {
            return '';
        }

        return '<div class="ulplay-video-embed aspect-video my-4 w-full overflow-hidden rounded-xl ring-1 ring-stone-200/50">'
            . '<iframe src="' . htmlspecialchars($src, ENT_QUOTES) . '" '
            . 'class="w-full h-full" frameborder="0" loading="lazy" allowfullscreen '
            . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">'
            . '</iframe></div>';
    }

    private function addBlankTargetToLinks(string $html): string
    {
        return (string) preg_replace(
            '/<a\s+(href="[^"]*")/i',
            '<a target="_blank" rel="noopener noreferrer" $1',
            $html,
        );
    }

    private function processColorSyntax(string $html): string
    {
        return (string) preg_replace_callback(self::COLOR_PATTERN, function (array $m) {
            $color = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
            return '<span style="color:' . $color . '">' . $m[2] . '</span>';
        }, $html);
    }
}
