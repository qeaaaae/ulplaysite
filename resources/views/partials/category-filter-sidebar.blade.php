@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $categoryTree */
    $countKey = $type === 'products' ? 'products_count' : 'services_count';
    $allLabel = $type === 'products' ? 'Все категории' : 'Все услуги';
    $dataAjax = $type === 'products' ? 'data-ajax-products' : 'data-ajax-services';

    $makeUrl = function (?string $slug) use ($routeName, $queryParams, $type) {
        // Для товаров и услуг URL строим только с категорией; q/sort подмешивает JS (как у каталога).
        if ($type === 'products' || $type === 'services') {
            $params = [];
            if ($slug !== null && $slug !== '') {
                $params['category'] = $slug;
            }

            return route($routeName, $params);
        }

        $params = array_merge($queryParams, ['category' => $slug]);

        return route($routeName, array_filter($params, fn ($v) => $v !== null && $v !== ''));
    };
@endphp

@if($categoryTree->isNotEmpty())
    <nav
        class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm lg:sticky lg:top-24 lg:h-full lg:w-64 lg:shrink-0"
        aria-label="Фильтр по категориям"
    >
        <p class="shrink-0 border-b border-stone-100 px-3 py-2.5 text-xs font-semibold uppercase tracking-wide text-stone-500">
            Категории
        </p>
        <div class="flex flex-1 flex-col px-2 py-2">
            <ul class="space-y-1">
                <li>
                    <a
                        href="{{ $makeUrl(null) }}"
                        {{ $dataAjax }}
                        data-category-slug=""
                        class="flex items-center rounded-lg border px-3 py-2 text-sm font-medium transition-colors"
                        :class="!activeCategorySlug ? 'border-sky-200 bg-sky-50 text-sky-900' : 'border-transparent text-stone-700 hover:bg-stone-50'"
                    >
                        {{ $allLabel }}
                    </a>
                </li>
                @foreach($categoryTree as $root)
                    @if($root->children->isEmpty())
                        <li>
                            <a
                                href="{{ $makeUrl($root->slug) }}"
                                {{ $dataAjax }}
                                data-category-slug="{{ $root->slug }}"
                                class="flex items-center justify-between gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-colors"
                                :class="activeCategorySlug === @js($root->slug) ? 'border-sky-200 bg-sky-50 text-sky-900' : 'border-transparent text-stone-700 hover:bg-stone-50'"
                            >
                                <span class="min-w-0 truncate">{{ $root->name }}</span>
                                @if(($root->{$countKey} ?? 0) > 0)
                                    <span class="shrink-0 text-xs tabular-nums text-stone-400">{{ $root->{$countKey} }}</span>
                                @endif
                            </a>
                        </li>
                    @else
                        <li class="overflow-hidden rounded-lg">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-stone-800 hover:bg-stone-50"
                                @click="toggleParent({{ $root->id }})"
                                :aria-expanded="openParents[{{ $root->id }}] ? 'true' : 'false'"
                            >
                                <span class="min-w-0 truncate">{{ $root->name }}</span>
                                <svg
                                    class="h-4 w-4 shrink-0 text-stone-400 transition-transform duration-[450ms] ease-[cubic-bezier(0.4,0,0.2,1)] motion-reduce:transition-none motion-reduce:duration-0"
                                    :class="openParents[{{ $root->id }}] ? 'rotate-180' : ''"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div
                                x-show="openParents[{{ $root->id }}]"
                                x-collapse.duration.450ms
                                class="overflow-hidden"
                                x-bind:inert="!openParents[{{ $root->id }}]"
                            >
                                <ul class="mt-0.5 space-y-0.5 border-l border-stone-200 py-1 pl-2 ml-2">
                                    @foreach($root->children as $child)
                                        <li>
                                            <a
                                                href="{{ $makeUrl($child->slug) }}"
                                                {{ $dataAjax }}
                                                data-category-slug="{{ $child->slug }}"
                                                class="flex items-center justify-between gap-2 rounded-lg border px-2 py-1.5 text-sm transition-colors duration-150"
                                                :class="activeCategorySlug === @js($child->slug) ? 'border-sky-200 bg-sky-50 text-sky-900' : 'border-transparent text-stone-600 hover:bg-stone-50'"
                                            >
                                                <span class="min-w-0 truncate">{{ $child->name }}</span>
                                                <span class="shrink-0 text-xs tabular-nums text-stone-400">{{ $child->{$countKey} }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </nav>
@endif
