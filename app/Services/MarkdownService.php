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
        $html = Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = $this->addBlankTargetToLinks($html);
        $html = $this->processColorSyntax($html);

        return $html;
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
