@props([
    'news',
    'comments' => collect(),
    'canComment' => false,
])

<div id="comments" class="mt-8 pt-6 border-t border-stone-200">
    <h2 class="text-lg font-semibold text-stone-900 mb-4">Комментарии {{ $comments->count() ? '(' . $comments->count() . ')' : '' }}</h2>

    @if($canComment)
    <form action="{{ route('comments.store', $news) }}" method="POST" class="mb-6 p-4 bg-stone-50 rounded-xl border border-stone-200">
        @csrf
        <div class="flex flex-col sm:flex-row gap-3 max-w-2xl items-end">
            <div class="flex-1 w-full">
                <label for="comment-body" class="sr-only">Комментарий</label>
                <textarea name="body" id="comment-body" rows="2" maxlength="500" required class="w-full px-3 py-2 text-sm bg-white border border-stone-300 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 resize-y" placeholder="Ваш комментарий...">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <x-ui.button type="submit" variant="primary" class="shrink-0">Отправить</x-ui.button>
        </div>
    </form>
    @else
        <p class="mb-6 p-4 rounded-xl border border-stone-200 text-stone-600 text-sm">
            <a href="{{ route('login') }}" class="text-sky-600 hover:underline">Войдите</a>, чтобы оставить комментарий.
        </p>
    @endif

    @if($comments->isEmpty())
        <p class="text-stone-500 text-sm">Пока ничего нет.</p>
    @else
        <ul class="space-y-3">
            @foreach($comments as $comment)
                <li class="p-3 bg-white rounded-lg border border-stone-200">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-stone-700">{{ $comment->user->name ?? 'Гость' }}</span>
                        <span class="text-xs text-stone-400">{{ $comment->created_at->format(config('app.datetime_format')) }}</span>
                    </div>
                    <p class="text-stone-600 text-sm leading-snug line-clamp-4">{{ $comment->body }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</div>
