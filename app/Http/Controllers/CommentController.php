<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsCommentRequest;
use App\Models\Comment;
use App\Models\CommentHelpfulVote;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(StoreNewsCommentRequest $request, News $news): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        if (Comment::where('news_id', $news->id)->where('user_id', $user->id)->exists()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'body' => ['Вы уже оставили комментарий к этой новости.'],
                    ],
                ], 422);
            }

            return redirect()->back()->withErrors(['body' => 'Вы уже оставили комментарий к этой новости.']);
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
