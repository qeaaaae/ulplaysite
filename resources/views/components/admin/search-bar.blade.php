@props([
    'action' => '',
    'placeholder' => 'Поиск...',
    'value' => '',
])

<x-ui.search-form :action="$action" :placeholder="$placeholder" :value="$value" formClass="flex-1 min-w-0 h-11" />
