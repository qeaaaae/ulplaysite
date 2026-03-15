<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsCommentRequest;
use App\Models\Comment;
use App\Models\News;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(StoreNewsCommentRequest $request, News $news): RedirectResponse
    {
        $user = $request->user();
        if (Comment::where('news_id', $news->id)->where('user_id', $user->id)->exists()) {
            return redirect()->back()->withErrors(['body' => 'Вы уже оставили комментарий к этой новости.']);
        }

        Comment::create([
            'news_id' => $news->id,
            'user_id' => $user->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()
            ->route('news.show', $news)
            ->with('message', 'Комментарий добавлен.');
    }
}
