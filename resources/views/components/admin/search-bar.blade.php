@props([
    'action' => '',
    'placeholder' => 'Поиск...',
    'value' => '',
    'hiddens' => [],
])

<x-ui.search-form :action="$action" :placeholder="$placeholder" :value="$value" :hiddens="$hiddens" formClass="flex-1 min-w-0 h-11" />
