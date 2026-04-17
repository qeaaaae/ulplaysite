<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ImportGamemagArticleJob;
use App\Models\News;
use App\Services\Gamemag\GamemagArticleParser;
use Illuminate\Console\Command;

class GamemagImportNewsCommand extends Command
{
    protected $signature = 'gamemag:import-news
                            {--url= : URL ленты (по умолчанию из config/gamemag.php)}
                            {--sync : Выполнить импорт статей синхронно, без очереди}
                            {--author= : ID пользователя-автора (иначе первый админ)}
                            {--queue= : Имя очереди для job (по умолчанию из config/gamemag.php)}';

    protected $description = 'Собрать ссылки на новости с gamemag.ru и поставить в очередь импорт отсутствующих в БД';

    public function handle(GamemagArticleParser $parser): int
    {
        $listUrl = (string) ($this->option('url') ?: config('gamemag.list_url', 'https://gamemag.ru/'));
        $authorId = $this->resolveAuthorId();
        $queueName = $this->resolveQueueName();

        try {
            $html = $parser->fetchPageHtml($listUrl);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $urls = $parser->extractArticleUrlsFromListingHtml($html, $listUrl);
        $newCount = 0;

        foreach ($urls as $articleUrl) {
            $normalized = $this->normalizeSourceUrl($articleUrl);
            if (News::query()->where('source_url', $normalized)->exists()) {
                continue;
            }
            $newCount++;
            if ($this->option('sync')) {
                ImportGamemagArticleJob::dispatchSync($articleUrl, $authorId);
            } else {
                $pendingDispatch = ImportGamemagArticleJob::dispatch($articleUrl, $authorId);
                if ($queueName !== '') {
                    $pendingDispatch->onQueue($queueName);
                }
            }
        }

        $this->info('Ссылок на ленте: ' . count($urls) . ', новых задач в очереди: ' . $newCount . ($this->option('sync') ? ' (sync)' : '; очередь: ' . ($queueName !== '' ? $queueName : 'default')));

        return self::SUCCESS;
    }

    private function resolveAuthorId(): int
    {
        $opt = $this->option('author');
        if ($opt !== null && $opt !== '') {
            return (int) $opt;
        }

        return (int) config('gamemag.author_user_id', 1);
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

    private function resolveQueueName(): string
    {
        $queue = $this->option('queue');
        if (is_string($queue) && trim($queue) !== '') {
            return trim($queue);
        }

        $configQueue = config('gamemag.queue');

        return is_string($configQueue) ? trim($configQueue) : '';
    }
}
