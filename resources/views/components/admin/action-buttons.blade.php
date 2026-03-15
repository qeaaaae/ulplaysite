@props([
    'editHref' => null,
    'editTitle' => 'Изменить',
    'deleteAction' => null,
    'deleteTitle' => 'Удалить',
    'deleteConfirm' => 'Удалить?',
    'blockAction' => null,
    'blockConfirm' => 'Заблокировать?',
    'unblockAction' => null,
    'restoreAction' => null,
    'restoreTitle' => 'Восстановить',
])

<div class="flex items-center gap-1">
    @if($editHref)
        <a href="{{ $editHref }}" title="{{ $editTitle }}" class="p-2 rounded-md text-stone-500 hover:bg-sky-50 hover:text-sky-600 transition-colors cursor-pointer">
            @svg('heroicon-o-pencil-square', 'w-4 h-4')
        </a>
    @endif
    @if($blockAction)
        <form method="POST" action="{{ $blockAction }}" class="inline" onsubmit="var f=this; event.preventDefault(); if(typeof ulplayConfirm==='function'){ ulplayConfirm({{ json_encode($blockConfirm) }}, function(ok){ if(ok) f.submit(); }); } else { if(confirm({{ json_encode($blockConfirm) }})) f.submit(); } return false;">
            @csrf
            <button type="submit" title="Заблокировать" class="p-2 rounded-md text-stone-500 hover:bg-amber-50 hover:text-amber-600 transition-colors cursor-pointer">
                @svg('heroicon-o-lock-closed', 'w-4 h-4')
            </button>
        </form>
    @endif
    @if($unblockAction)
        <form method="POST" action="{{ $unblockAction }}" class="inline">
            @csrf
            <button type="submit" title="Разблокировать" class="p-2 rounded-md text-stone-500 hover:bg-emerald-50 hover:text-emerald-600 transition-colors cursor-pointer">
                @svg('heroicon-o-lock-open', 'w-4 h-4')
            </button>
        </form>
    @endif
    @if($restoreAction)
        <form method="POST" action="{{ $restoreAction }}" class="inline">
            @csrf
            <button type="submit" title="{{ $restoreTitle }}" class="p-2 rounded-md text-stone-500 hover:bg-emerald-50 hover:text-emerald-600 transition-colors cursor-pointer">
                @svg('heroicon-o-arrow-path', 'w-4 h-4')
            </button>
        </form>
    @endif
    @if($deleteAction)
        <form method="POST" action="{{ $deleteAction }}" class="inline" onsubmit="var f=this; event.preventDefault(); if(typeof ulplayConfirm==='function'){ ulplayConfirm({{ json_encode($deleteConfirm) }}, function(ok){ if(ok) f.submit(); }); } else { if(confirm({{ json_encode($deleteConfirm) }})) f.submit(); } return false;">
            @csrf
            @method('DELETE')
            <button type="submit" title="{{ $deleteTitle }}" class="p-2 rounded-md text-stone-500 hover:bg-rose-50 hover:text-rose-600 transition-colors cursor-pointer">
                @svg('heroicon-o-trash', 'w-4 h-4')
            </button>
        </form>
    @endif
</div>
