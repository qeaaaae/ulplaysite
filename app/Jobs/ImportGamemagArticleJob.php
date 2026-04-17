<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\News;
use App\Services\Gamemag\GamemagArticleParser;
use App\Services\Gamemag\GamemagNewsCoverImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportGamemagArticleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly string $articleUrl,
        public readonly int $authorUserId,
    ) {}

    public function handle(
        GamemagArticleParser $parser,
        GamemagNewsCoverImporter $coverImporter,
    ): void {
        $normalized = $this->normalizeSourceUrl($this->articleUrl);

        if (News::query()->where('source_url', $normalized)->exists()) {
            return;
        }

        $data = $parser->parseArticleFromUrl($normalized);

        $slug = $this->uniqueSlugFromGamemagUrl($normalized);

        $news = News::query()->create([
            'title' => $data['title'] ?: 'Без заголовка',
            'slug' => $slug,
            'source_url' => $normalized,
            'description' => $data['description'] ?: null,
            'content' => $data['content'] ?: null,
            'author_id' => $this->authorUserId,
            'published_at' => now(),
        ]);

        if ($data['cover_url'] !== '') {
            $coverImporter->attachIfPossible($news, $data['cover_url']);
        }
    }

    private function normalizeSourceUrl(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return $url;
        }
        $path = $parts['path'] ?? '/';
        $path = preg_replace('/#.*/', '', $path) ?? $path;

        return strtolower($parts['scheme']) . '://' . $parts['host'] . $path;
    }

    private function uniqueSlugFromGamemagUrl(string $url): string
    {
        if (preg_match('~\/news\/(\d+)\/([^\/\?#]+)~', $url, $m)) {
            $base = 'gamemag-' . $m[1] . '-' . Str::slug($m[2]);
        } else {
            $base = 'gamemag-' . Str::random(10);
        }

        $slug = $base;
        $i = 2;
        while (News::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
