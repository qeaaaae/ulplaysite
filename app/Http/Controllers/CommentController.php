<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\CommentHelpfulVote;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private const COMMENT_COOLDOWN_SECONDS = 30;

    public function store(StoreNewsCommentRequest $request, News $news): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $lastComment = Comment::where('news_id', $news->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if ($lastComment && $lastComment->created_at->diffInSeconds(now()) < self::COMMENT_COOLDOWN_SECONDS) {
            $waitSeconds = (int) ceil(self::COMMENT_COOLDOWN_SECONDS - $lastComment->created_at->diffInSeconds(now()));
            $message = "Подождите {$waitSeconds} сек. перед следующим комментарием.";
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => ['body' => [$message]],
                    'wait_seconds' => $waitSeconds,
                ], 422);
            }

            return redirect()->back()->withErrors(['body' => $message]);
        }

        Comment::create([
            'news_id' => $news->id,
            'user_id' => $user->id,
            'body' => $request->validated('body'),
        ]);

        if ($request->wantsJson()) {
            $news->load(['comments.user', 'comments.helpfulVotes']);

            return response()->json([
                'result' => true,
                'message' => 'Комментарий добавлен.',
                'html' => view('components.comments-block', [
                    'news' => $news,
                    'comments' => $news->comments,
                    'canComment' => true,
                ])->render(),
            ]);
        }

        return redirect()
            ->route('news.show', $news)
            ->with('message', 'Комментарий добавлен.');
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $user = $request->user();
        if (! $comment->isEditableBy($user)) {
            return response()->json(['result' => false, 'message' => 'Нет прав на редактирование.'], 403);
        }

        $comment->update([
            'body' => $request->validated('body'),
            'edited_at' => now(),
        ]);

        return response()->json([
            'result' => true,
            'message' => 'Комментарий обновлён.',
            'body' => $comment->body,
            'edited_at' => $comment->edited_at?->format(config('app.datetime_format')),
        ]);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        if (! $comment->isDeletableBy($user)) {
            if ($request->wantsJson()) {
                return response()->json(['result' => false, 'message' => 'Нет прав на удаление.'], 403);
            }

            return redirect()->back()->withErrors(['comment' => 'Нет прав на удаление.']);
        }

        $news = $comment->news;
        $comment->delete();

        if ($request->wantsJson()) {
            $news->load(['comments.user', 'comments.helpfulVotes']);

            return response()->json([
                'result' => true,
                'message' => 'Комментарий удалён.',
                'html' => view('components.comments-block', [
                    'news' => $news,
                    'comments' => $news->comments,
                    'canComment' => true,
                ])->render(),
            ]);
        }

        return redirect()->route('news.show', $news)->with('message', 'Комментарий удалён.');
    }

    public function helpful(Request $request, Comment $comment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $vote = CommentHelpfulVote::firstOrCreate([
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);

        $count = CommentHelpfulVote::where('comment_id', $comment->id)->count();

        return response()->json([
            'result' => true,
            'message' => $vote->wasRecentlyCreated ? 'Спасибо!' : 'Вы уже отметили комментарий как полезный.',
            'count' => $count,
        ]);
    }
}
