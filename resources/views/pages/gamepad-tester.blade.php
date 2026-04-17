@extends('layouts.app')

@section('content')
<div class="py-4 pb-10 sm:pb-12">
    <div
        class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6"
        x-data="gamepadTester()"
        x-init="init()"
    >
        <x-ui.section-heading tag="h1" icon="heroicon-o-cpu-chip" class="mb-0">
            Тестер геймпада
        </x-ui.section-heading>

        {{-- Не поддерживается --}}
        <template x-if="!supported">
            <div class="flex flex-col items-center justify-center py-16 text-center bg-white rounded-2xl border border-stone-200 shadow-sm">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 flex items-center justify-center mb-4">
                    @svg('heroicon-o-exclamation-triangle', 'w-7 h-7 text-rose-500')
                </div>
                <h2 class="text-lg font-semibold text-stone-900 mb-2">Браузер не поддерживает Gamepad API</h2>
                <p class="text-sm text-stone-500 max-w-sm">Используйте Chrome, Edge, Firefox или Safari 16.4+</p>
            </div>
        </template>

        {{-- Ожидание подключения --}}
        <template x-if="supported && gamepads.length === 0">
            <div class="flex flex-col items-center justify-center py-16 text-center bg-white rounded-2xl border border-stone-200 shadow-sm">
                <div class="w-14 h-14 rounded-2xl bg-sky-100 flex items-center justify-center mb-4">
                    @svg('heroicon-o-cpu-chip', 'w-7 h-7 text-sky-600')
                </div>
                <h2 class="text-lg font-semibold text-stone-900 mb-2">Геймпад не подключён</h2>
                <p class="text-sm text-stone-500 max-w-sm">Подключите геймпад через USB или Bluetooth и нажмите любую кнопку на нём</p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs text-stone-500 bg-stone-50 border border-stone-200 rounded-lg px-3 py-1.5">
                        @svg('heroicon-o-check-circle', 'w-3.5 h-3.5 text-emerald-500')
                        Xbox, PlayStation, Nintendo Switch Pro
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs text-stone-500 bg-stone-50 border border-stone-200 rounded-lg px-3 py-1.5">
                        @svg('heroicon-o-check-circle', 'w-3.5 h-3.5 text-emerald-500')
                        До 4 геймпадов одновременно
                    </span>
                </div>
            </div>
        </template>

        {{-- Карточки геймпадов --}}
        <template x-for="(gp, idx) in gamepads" :key="gp.index">
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">

                {{-- Шапка --}}
                <div class="flex items-center gap-3 px-5 py-3.5 border-b border-stone-100 bg-stone-50/60">
                    <div class="w-8 h-8 rounded-lg bg-sky-500 flex items-center justify-center shrink-0">
                        <span class="text-white font-bold text-sm" x-text="gp.index + 1"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-stone-900 text-sm truncate" x-text="gp.id"></p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-stone-400" x-text="gp.mapping === 'standard' ? 'Стандартная раскладка' : 'Нестандартная раскладка'"></span>
                            <span class="text-xs px-1.5 py-0.5 rounded font-medium"
                                  :class="{
                                      'bg-blue-100 text-blue-700':       gp.controllerType === 'playstation',
                                      'bg-red-100 text-red-700':         gp.controllerType === 'nintendo',
                                      'bg-emerald-100 text-emerald-700': gp.controllerType === 'xbox',
                                      'bg-stone-100 text-stone-500':     gp.controllerType === 'generic',
                                  }"
                                  x-text="{ playstation:'PlayStation', nintendo:'Nintendo', xbox:'Xbox', generic:'Generic' }[gp.controllerType]">
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-1 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Подключён
                    </div>
                </div>

                <div class="p-4 sm:p-5 flex flex-col gap-4 sm:gap-5">

                    {{-- ══════════════════════════════════════════════════
                         SVG-ПИКТОГРАММА КОНТРОЛЛЕРА
                    ══════════════════════════════════════════════════ --}}
                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-3 sm:gap-4 items-center">
                    <div class="xl:col-span-8 flex justify-center self-center">
                        <svg viewBox="0 0 441 385" xmlns="http://www.w3.org/2000/svg" class="w-full select-none" style="max-width:560px; max-height:280px">

                            <defs>
                                {{-- Маски для D-pad --}}
                                <mask :id="'gi'+gp.index+'m1'" fill="white"><path d="M177.669 222.335C180.793 219.21 180.816 213.997 176.868 212.014C176.327 211.743 175.776 211.491 175.215 211.258C172.182 210.002 168.931 209.355 165.648 209.355C162.365 209.355 159.114 210.002 156.081 211.258C155.521 211.491 154.969 211.743 154.429 212.014C150.48 213.997 150.503 219.21 153.627 222.335L159.991 228.698C163.116 231.823 168.181 231.823 171.305 228.698L177.669 222.335Z"/></mask>
                                <mask :id="'gi'+gp.index+'m2'" fill="white"><path d="M181.447 249.669C184.571 252.793 189.785 252.816 191.768 248.868C192.039 248.327 192.291 247.776 192.523 247.215C193.78 244.182 194.426 240.931 194.426 237.648C194.426 234.365 193.78 231.114 192.523 228.081C192.291 227.521 192.039 226.969 191.768 226.429C189.785 222.48 184.571 222.503 181.447 225.627L175.083 231.991C171.959 235.116 171.959 240.181 175.083 243.305L181.447 249.669Z"/></mask>
                                <mask :id="'gi'+gp.index+'m3'" fill="white"><path d="M154.113 253.447C150.989 256.571 150.966 261.785 154.914 263.767C155.455 264.039 156.006 264.291 156.566 264.523C159.6 265.78 162.85 266.426 166.134 266.426C169.417 266.426 172.667 265.78 175.701 264.523C176.261 264.291 176.812 264.039 177.353 263.767C181.301 261.785 181.279 256.571 178.154 253.447L171.79 247.083C168.666 243.959 163.601 243.959 160.477 247.083L154.113 253.447Z"/></mask>
                                <mask :id="'gi'+gp.index+'m4'" fill="white"><path d="M150.335 226.113C147.21 222.989 141.997 222.966 140.014 226.914C139.743 227.455 139.491 228.006 139.258 228.566C138.002 231.6 137.355 234.85 137.355 238.134C137.355 241.417 138.002 244.667 139.258 247.701C139.491 248.261 139.743 248.812 140.014 249.353C141.997 253.301 147.21 253.279 150.335 250.154L156.698 243.79C159.823 240.666 159.823 235.601 156.698 232.477L150.335 226.113Z"/></mask>
                            </defs>

                            {{-- Контур корпуса --}}
                            <path d="M220.5 294.5C220.5 294.5 195 294.5 150 294.5C105 294.5 81.5 378.5 49.5 378.5C17.5 378.5 4 363.9 4 317.5C4 271.1 43.5 165.5 55 137.5C66.5 109.5 95.5 92.0001 128 92.0001C154 92.0001 200.5 92.0001 220.5 92.0001" stroke="rgba(0,0,0,0.15)" stroke-width="5" fill="none"/>
                            <path d="M220 294.5C220 294.5 245.5 294.5 290.5 294.5C335.5 294.5 359 378.5 391 378.5C423 378.5 436.5 363.9 436.5 317.5C436.5 271.1 397 165.5 385.5 137.5C374 109.5 345 92.0001 312.5 92.0001C286.5 92.0001 240 92.0001 220 92.0001" stroke="rgba(0,0,0,0.15)" stroke-width="5" fill="none"/>

                            {{-- L2 / R2 Триггеры (заливка по аналоговому значению) --}}
                            <path d="M152.5 37C152.5 41.1421 149.142 44.5 145 44.5H132C127.858 44.5 124.5 41.1421 124.5 37V16.5C124.5 8.76801 130.768 2.5 138.5 2.5C146.232 2.5 152.5 8.76801 152.5 16.5V37Z"
                                  :fill="'rgba(14,165,233,' + (gp.buttons[6]?.value ?? 0) + ')'"
                                  :stroke="(gp.buttons[6]?.value ?? 0) > 0.05 ? '#0284c7' : 'rgba(0,0,0,0.2)'"
                                  stroke-width="5"/>
                            <path d="M317.5 37C317.5 41.1421 314.142 44.5 310 44.5H297C292.858 44.5 289.5 41.1421 289.5 37V16.5C289.5 8.76801 295.768 2.5 303.5 2.5C311.232 2.5 317.5 8.76801 317.5 16.5V37Z"
                                  :fill="'rgba(14,165,233,' + (gp.buttons[7]?.value ?? 0) + ')'"
                                  :stroke="(gp.buttons[7]?.value ?? 0) > 0.05 ? '#0284c7' : 'rgba(0,0,0,0.2)'"
                                  stroke-width="5"/>

                            {{-- L1 / R1 Бамперы --}}
                            <rect x="111.5" y="61.5" width="41" height="13" rx="6.5"
                                  :fill="gp.buttons[4]?.pressed ? '#0ea5e9' : 'transparent'"
                                  :stroke="gp.buttons[4]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.2)'"
                                  stroke-width="5"/>
                            <rect x="289.5" y="61.5" width="41" height="13" rx="6.5"
                                  :fill="gp.buttons[5]?.pressed ? '#0ea5e9' : 'transparent'"
                                  :stroke="gp.buttons[5]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.2)'"
                                  stroke-width="5"/>

                            {{-- Сенсорная панель / тачпад (PS, кнопка 17) --}}
                            <rect x="160" y="96" width="122" height="20"
                                  :fill="gp.buttons[17]?.pressed ? 'rgba(14,165,233,0.22)' : 'rgba(0,0,0,0)'"
                                  :stroke="gp.buttons[17]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.12)'"
                                  stroke-width="4"/>

                            {{-- ── Левый стик ── --}}
                            <circle cx="113" cy="160" r="37.5" fill="none" stroke="rgba(0,0,0,0.12)" stroke-width="5"/>
                            <circle r="28"
                                    :cx="113 + (gp.displayAxes[0] ?? 0) * 20"
                                    :cy="160 + (gp.displayAxes[1] ?? 0) * 20"
                                    :fill="gp.buttons[10]?.pressed ? 'rgba(14,165,233,0.3)' : 'rgba(0,0,0,0)'"
                                    :stroke="gp.buttons[10]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.35)'"
                                    stroke-width="5"/>

                            {{-- ── Правый стик ── --}}
                            <circle cx="278" cy="238" r="37.5" fill="none" stroke="rgba(0,0,0,0.12)" stroke-width="5"/>
                            <circle r="28"
                                    :cx="278 + (gp.displayAxes[2] ?? 0) * 20"
                                    :cy="238 + (gp.displayAxes[3] ?? 0) * 20"
                                    :fill="gp.buttons[11]?.pressed ? 'rgba(14,165,233,0.3)' : 'rgba(0,0,0,0)'"
                                    :stroke="gp.buttons[11]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.35)'"
                                    stroke-width="5"/>

                            {{-- ── D-Pad ── --}}
                            <circle cx="166" cy="238" r="37.5" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="5"/>

                            {{-- D-Up (btn 12) --}}
                            <path d="M177.669 222.335C180.793 219.21 180.816 213.997 176.868 212.014C176.327 211.743 175.776 211.491 175.215 211.258C172.182 210.002 168.931 209.355 165.648 209.355C162.365 209.355 159.114 210.002 156.081 211.258C155.521 211.491 154.969 211.743 154.429 212.014C150.48 213.997 150.503 219.21 153.627 222.335L159.991 228.698C163.116 231.823 168.181 231.823 171.305 228.698L177.669 222.335Z"
                                  :fill="gp.buttons[12]?.pressed ? '#0ea5e9' : 'transparent'"
                                  :stroke="gp.buttons[12]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"/>

                            {{-- D-Right (btn 15) --}}
                            <path d="M181.447 249.669C184.571 252.793 189.785 252.816 191.768 248.868C192.039 248.327 192.291 247.776 192.523 247.215C193.78 244.182 194.426 240.931 194.426 237.648C194.426 234.365 193.78 231.114 192.523 228.081C192.291 227.521 192.039 226.969 191.768 226.429C189.785 222.48 184.571 222.503 181.447 225.627L175.083 231.991C171.959 235.116 171.959 240.181 175.083 243.305L181.447 249.669Z"
                                  :fill="gp.buttons[15]?.pressed ? '#0ea5e9' : 'transparent'"
                                  :stroke="gp.buttons[15]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"/>

                            {{-- D-Down (btn 13) --}}
                            <path d="M154.113 253.447C150.989 256.571 150.966 261.785 154.914 263.767C155.455 264.039 156.006 264.291 156.566 264.523C159.6 265.78 162.85 266.426 166.134 266.426C169.417 266.426 172.667 265.78 175.701 264.523C176.261 264.291 176.812 264.039 177.353 263.767C181.301 261.785 181.279 256.571 178.154 253.447L171.79 247.083C168.666 243.959 163.601 243.959 160.477 247.083L154.113 253.447Z"
                                  :fill="gp.buttons[13]?.pressed ? '#0ea5e9' : 'transparent'"
                                  :stroke="gp.buttons[13]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"/>

                            {{-- D-Left (btn 14) --}}
                            <path d="M150.335 226.113C147.21 222.989 141.997 222.966 140.014 226.914C139.743 227.455 139.491 228.006 139.258 228.566C138.002 231.6 137.355 234.85 137.355 238.134C137.355 241.417 138.002 244.667 139.258 247.701C139.491 248.261 139.743 248.812 140.014 249.353C141.997 253.301 147.21 253.279 150.335 250.154L156.698 243.79C159.823 240.666 159.823 235.601 156.698 232.477L150.335 226.113Z"
                                  :fill="gp.buttons[14]?.pressed ? '#0ea5e9' : 'transparent'"
                                  :stroke="gp.buttons[14]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"/>

                            {{-- Контур кнопок справа как в эталонном SVG --}}
                            <circle id="BOutline" cx="329" cy="160" r="37.5" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="5"/>
                            <path id="BTop" d="M177.669 222.335C180.793 219.21 180.816 213.997 176.868 212.014C176.327 211.743 175.776 211.491 175.215 211.258C172.182 210.002 168.931 209.355 165.648 209.355C162.365 209.355 159.114 210.002 156.081 211.258C155.521 211.491 154.969 211.743 154.429 212.014C150.48 213.997 150.503 219.21 153.627 222.335L159.991 228.698C163.116 231.823 168.181 231.823 171.305 228.698L177.669 222.335Z"
                                  transform="translate(163 -78)"
                                  :fill="gp.buttons[3]?.pressed ? '#0ea5e9' : 'rgba(0,0,0,0)'"
                                  :stroke="gp.buttons[3]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"></path>
                            <path id="BRight" d="M181.447 249.669C184.571 252.793 189.785 252.816 191.768 248.868C192.039 248.327 192.291 247.776 192.523 247.215C193.78 244.182 194.426 240.931 194.426 237.648C194.426 234.365 193.78 231.114 192.523 228.081C192.291 227.521 192.039 226.969 191.768 226.429C189.785 222.48 184.571 222.503 181.447 225.627L175.083 231.991C171.959 235.116 171.959 240.181 175.083 243.305L181.447 249.669Z"
                                  transform="translate(163 -78)"
                                  :fill="gp.buttons[1]?.pressed ? '#0ea5e9' : 'rgba(0,0,0,0)'"
                                  :stroke="gp.buttons[1]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"></path>
                            <path id="BBottom" d="M154.113 253.447C150.989 256.571 150.966 261.785 154.914 263.767C155.455 264.039 156.006 264.291 156.566 264.523C159.6 265.78 162.85 266.426 166.134 266.426C169.417 266.426 172.667 265.78 175.701 264.523C176.261 264.291 176.812 264.039 177.353 263.767C181.301 261.785 181.279 256.571 178.154 253.447L171.79 247.083C168.666 243.959 163.601 243.959 160.477 247.083L154.113 253.447Z"
                                  transform="translate(163 -78)"
                                  :fill="gp.buttons[0]?.pressed ? '#0ea5e9' : 'rgba(0,0,0,0)'"
                                  :stroke="gp.buttons[0]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"></path>
                            <path id="BLeft" d="M150.335 226.113C147.21 222.989 141.997 222.966 140.014 226.914C139.743 227.455 139.491 228.006 139.258 228.566C138.002 231.6 137.355 234.85 137.355 238.134C137.355 241.417 138.002 244.667 139.258 247.701C139.491 248.261 139.743 248.812 140.014 249.353C141.997 253.301 147.21 253.279 150.335 250.154L156.698 243.79C159.823 240.666 159.823 235.601 156.698 232.477L150.335 226.113Z"
                                  transform="translate(163 -78)"
                                  :fill="gp.buttons[2]?.pressed ? '#0ea5e9' : 'rgba(0,0,0,0)'"
                                  :stroke="gp.buttons[2]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.14)'"
                                  stroke-width="6"></path>

                            {{-- ── Центральные кнопки ── --}}
                            {{-- Back / Select / Create (btn 8) --}}
                            <circle cx="185" cy="172" r="10"
                                    :fill="gp.buttons[8]?.pressed ? '#0ea5e9' : 'transparent'"
                                    :stroke="gp.buttons[8]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.2)'"
                                    stroke-width="5"/>

                            {{-- Home / PS / Guide (btn 16) --}}
                            <circle cx="222" cy="145" r="15"
                                    :fill="gp.buttons[16]?.pressed ? '#0ea5e9' : 'transparent'"
                                    :stroke="gp.buttons[16]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.2)'"
                                    stroke-width="5"/>

                            {{-- Start / Options (btn 9) --}}
                            <circle cx="259" cy="172" r="10"
                                    :fill="gp.buttons[9]?.pressed ? '#0ea5e9' : 'transparent'"
                                    :stroke="gp.buttons[9]?.pressed ? '#0284c7' : 'rgba(0,0,0,0.2)'"
                                    stroke-width="5"/>

                        </svg>
                    </div>

                    <div class="xl:col-span-4 flex flex-col gap-3">
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-stone-500 mb-1">
                                <span class="font-medium">L2</span>
                                <span class="font-mono" x-text="Math.round((gp.buttons[6]?.value ?? 0) * 100) + '%'"></span>
                            </div>
                            <div class="h-2 rounded-full bg-stone-100 border border-stone-200 overflow-hidden">
                                <div class="h-full bg-sky-500 transition-all duration-75" :style="'width:' + ((gp.buttons[6]?.value ?? 0) * 100) + '%'"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-stone-500 mb-1">
                                <span class="font-medium">R2</span>
                                <span class="font-mono" x-text="Math.round((gp.buttons[7]?.value ?? 0) * 100) + '%'"></span>
                            </div>
                            <div class="h-2 rounded-full bg-stone-100 border border-stone-200 overflow-hidden">
                                <div class="h-full bg-sky-500 transition-all duration-75" :style="'width:' + ((gp.buttons[7]?.value ?? 0) * 100) + '%'"></div>
                            </div>
                        </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-2.5 sm:gap-3">
                        {{-- Левый стик --}}
                        <div class="flex flex-col items-center gap-1 rounded-xl border border-stone-200 bg-stone-50/50 p-2">
                            <div class="flex items-center justify-between w-full">
                                <p class="text-xs font-medium text-stone-500" x-text="gp.btnNames.ls"></p>
                                <button type="button"
                                        @click="clearTrail(gp.index, 'left')"
                                        class="text-[10px] text-stone-400 hover:text-rose-500 transition-colors px-1.5 py-0.5 rounded border border-transparent hover:border-rose-200">
                                    очистить след
                                </button>
                            </div>
                            <svg viewBox="0 0 160 160" class="w-full max-w-[118px] sm:max-w-[126px] aspect-square">
                                {{-- Граница --}}
                                <circle cx="80" cy="80" r="72" fill="rgba(0,0,0,0.03)" stroke="rgba(0,0,0,0.15)" stroke-width="1.5"/>
                                {{-- Перекрестие --}}
                                <line x1="8" y1="80" x2="152" y2="80" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>
                                <line x1="80" y1="8" x2="80" y2="152" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>
                                {{-- Мёртвая зона --}}
                                <circle cx="80" cy="80" r="7" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="1" stroke-dasharray="2 2"/>
                                {{-- Трейл --}}
                                <polyline
                                    :points="trailPoints(gp.leftTrail, 80, 80, 68)"
                                    fill="none" stroke="#0ea5e9" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
                                {{-- Контрольный след по ободу (поверх серого круга) --}}
                                <polyline
                                    :points="trailPointsOnRim(gp.leftTrail, 80, 80, 72, 0.88)"
                                    fill="none" stroke="#0ea5e9" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round" opacity="0.95"/>
                                {{-- Текущая позиция --}}
                                <circle
                                    :cx="80 + (gp.displayAxes[0] ?? 0) * 68"
                                    :cy="80 + (gp.displayAxes[1] ?? 0) * 68"
                                    r="4"
                                    :fill="gp.buttons[10]?.pressed ? '#0284c7' : '#0ea5e9'"
                                    stroke="white" stroke-width="2"/>
                                <line
                                    x1="80" y1="80"
                                    :x2="80 + (gp.displayAxes[0] ?? 0) * 68"
                                    :y2="80 + (gp.displayAxes[1] ?? 0) * 68"
                                    stroke="#0ea5e9" stroke-width="1" opacity="0.4"/>
                            </svg>
                            <div class="text-[10px] text-stone-400 font-mono">
                                X: <span :class="Math.abs(gp.axes[0] ?? 0) > 0.1 ? 'text-sky-600 font-semibold' : ''" x-text="(gp.axes[0] ?? 0).toFixed(3)"></span>
                                &nbsp; Y: <span :class="Math.abs(gp.axes[1] ?? 0) > 0.1 ? 'text-sky-600 font-semibold' : ''" x-text="(gp.axes[1] ?? 0).toFixed(3)"></span>
                            </div>
                        </div>

                        {{-- Правый стик --}}
                        <div class="flex flex-col items-center gap-1 rounded-xl border border-stone-200 bg-stone-50/50 p-2">
                            <div class="flex items-center justify-between w-full">
                                <p class="text-xs font-medium text-stone-500" x-text="gp.btnNames.rs"></p>
                                <button type="button"
                                        @click="clearTrail(gp.index, 'right')"
                                        class="text-[10px] text-stone-400 hover:text-rose-500 transition-colors px-1.5 py-0.5 rounded border border-transparent hover:border-rose-200">
                                    очистить след
                                </button>
                            </div>
                            <svg viewBox="0 0 160 160" class="w-full max-w-[118px] sm:max-w-[126px] aspect-square">
                                <circle cx="80" cy="80" r="72" fill="rgba(0,0,0,0.03)" stroke="rgba(0,0,0,0.15)" stroke-width="1.5"/>
                                <line x1="8" y1="80" x2="152" y2="80" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>
                                <line x1="80" y1="8" x2="80" y2="152" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>
                                <circle cx="80" cy="80" r="7" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="1" stroke-dasharray="2 2"/>
                                <polyline
                                    :points="trailPoints(gp.rightTrail, 80, 80, 68)"
                                    fill="none" stroke="#f59e0b" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" opacity="0.5"/>
                                {{-- Контрольный след по ободу (поверх серого круга) --}}
                                <polyline
                                    :points="trailPointsOnRim(gp.rightTrail, 80, 80, 72, 0.88)"
                                    fill="none" stroke="#f59e0b" stroke-width="2.2"
                                    stroke-linecap="round" stroke-linejoin="round" opacity="0.95"/>
                                <circle
                                    :cx="80 + (gp.displayAxes[2] ?? 0) * 68"
                                    :cy="80 + (gp.displayAxes[3] ?? 0) * 68"
                                    r="4"
                                    :fill="gp.buttons[11]?.pressed ? '#d97706' : '#f59e0b'"
                                    stroke="white" stroke-width="2"/>
                                <line
                                    x1="80" y1="80"
                                    :x2="80 + (gp.displayAxes[2] ?? 0) * 68"
                                    :y2="80 + (gp.displayAxes[3] ?? 0) * 68"
                                    stroke="#f59e0b" stroke-width="1" opacity="0.4"/>
                            </svg>
                            <div class="text-[10px] text-stone-400 font-mono">
                                X: <span :class="Math.abs(gp.axes[2] ?? 0) > 0.1 ? 'text-amber-600 font-semibold' : ''" x-text="(gp.axes[2] ?? 0).toFixed(3)"></span>
                                &nbsp; Y: <span :class="Math.abs(gp.axes[3] ?? 0) > 0.1 ? 'text-amber-600 font-semibold' : ''" x-text="(gp.axes[3] ?? 0).toFixed(3)"></span>
                            </div>
                        </div>
                    </div>
                    </div>

                </div>
            </div>
        </template>

        {{-- Справка по раскладке --}}
        <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden"
             x-data="{ helpOpen: false }" x-show="supported">
            <button type="button"
                    class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-stone-50 transition-colors"
                    @click="helpOpen = !helpOpen">
                <span class="flex items-center gap-2 text-sm font-medium text-stone-700">
                    @svg('heroicon-o-information-circle', 'w-4 h-4 text-stone-400')
                    Раскладка стандартного геймпада (индексы кнопок)
                </span>
                <svg class="w-4 h-4 text-stone-400 transition-transform duration-200" :class="helpOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="helpOpen" x-collapse class="border-t border-stone-100">
                <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs">
                    <div>
                        <p class="font-semibold text-stone-700 mb-2">Xbox / Generic</p>
                        <div class="space-y-1">
                            @foreach([['0','A'],['1','B'],['2','X'],['3','Y'],['4','LB'],['5','RB'],['6','LT'],['7','RT'],['8','Back'],['9','Start'],['10','L3'],['11','R3'],['12','▲'],['13','▼'],['14','◀'],['15','▶'],['16','Home']] as [$i, $l])
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-5 rounded bg-stone-100 border border-stone-200 flex items-center justify-center font-mono font-semibold text-stone-600 shrink-0">{{ $i }}</span>
                                    <span class="text-stone-500">{{ $l }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-stone-700 mb-2">PlayStation (DualSense / DS4)</p>
                        <div class="space-y-1">
                            @foreach([['0','× Cross'],['1','○ Circle'],['2','□ Square'],['3','△ Triangle'],['4','L1'],['5','R1'],['6','L2'],['7','R2'],['8','Select / Create'],['9','Options'],['10','L3'],['11','R3'],['12','▲'],['13','▼'],['14','◀'],['15','▶'],['16','PS']] as [$i, $l])
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-5 rounded bg-stone-100 border border-stone-200 flex items-center justify-center font-mono font-semibold text-stone-600 shrink-0">{{ $i }}</span>
                                    <span class="text-stone-500">{{ $l }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="font-semibold text-stone-700 mb-2">Nintendo Switch Pro</p>
                        <div class="space-y-1">
                            @foreach([['0','B'],['1','A'],['2','Y'],['3','X'],['4','L'],['5','R'],['6','ZL'],['7','ZR'],['8','−'],['9','+'],['10','L3'],['11','R3'],['12','▲'],['13','▼'],['14','◀'],['15','▶'],['16','Home']] as [$i, $l])
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-5 rounded bg-stone-100 border border-stone-200 flex items-center justify-center font-mono font-semibold text-stone-600 shrink-0">{{ $i }}</span>
                                    <span class="text-stone-500">{{ $l }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function gamepadTester() {
    return {
        supported: !!navigator.getGamepads,
        gamepads:  [],
        rafId:     null,

        init() {
            if (!this.supported) return;
            window.addEventListener('gamepadconnected',    () => this.startPolling());
            window.addEventListener('gamepaddisconnected', () => this.poll());
            if (Array.from(navigator.getGamepads()).some(Boolean)) this.startPolling();
        },

        startPolling() {
            if (this.rafId) return;
            this.poll();
        },

        poll() {
            this.rafId = requestAnimationFrame(() => {
                this.rafId = null;
                const raw = Array.from(navigator.getGamepads()).filter(Boolean);

                this.gamepads = raw.map(gp => {
                    const type     = this.detectType(gp.id);
                    const btnNames = this.getBtnNames(type);
                    const face     = this.getFaceButtons(type);

                    // Сохраняем трейлы из предыдущего кадра
                    const prev = this.gamepads.find(g => g.index === gp.index);
                    const MAX_TRAIL = 300;

                    let leftTrail  = prev ? prev.leftTrail.slice()  : [];
                    let rightTrail = prev ? prev.rightTrail.slice() : [];

                    const lx = gp.axes[0] ?? 0, ly = gp.axes[1] ?? 0;
                    const rx = gp.axes[2] ?? 0, ry = gp.axes[3] ?? 0;

                    const [dlx, dly] = this.normalizeStickForDisplay(lx, ly);
                    const [drx, dry] = this.normalizeStickForDisplay(rx, ry);

                    leftTrail.push([dlx, dly]);
                    if (leftTrail.length > MAX_TRAIL) leftTrail.shift();

                    rightTrail.push([drx, dry]);
                    if (rightTrail.length > MAX_TRAIL) rightTrail.shift();

                    return {
                        index:          gp.index,
                        id:             gp.id,
                        mapping:        gp.mapping,
                        controllerType: type,
                        btnNames,
                        face,
                        leftTrail,
                        rightTrail,
                        buttons: Array.from(gp.buttons).map(b => ({ pressed: b.pressed, value: b.value })),
                        axes:    Array.from(gp.axes),
                        displayAxes: [dlx, dly, drx, dry],
                    };
                });

                if (raw.length > 0) this.startPolling();
            });
        },

        trailPoints(trail, cx, cy, r) {
            if (!trail || trail.length < 2) return '';
            return trail.map(([x, y]) => `${(cx + x * r).toFixed(1)},${(cy + y * r).toFixed(1)}`).join(' ');
        },

        trailPointsOnRim(trail, cx, cy, r, minMag = 0.88) {
            if (!trail || trail.length < 2) return '';

            const points = [];
            for (const [xRaw, yRaw] of trail) {
                const x = xRaw ?? 0;
                const y = yRaw ?? 0;
                const mag = Math.sqrt(x * x + y * y);
                if (mag < minMag || mag === 0) continue;

                const nx = x / mag;
                const ny = y / mag;
                points.push(`${(cx + nx * r).toFixed(1)},${(cy + ny * r).toFixed(1)}`);
            }

            return points.length >= 2 ? points.join(' ') : '';
        },

        normalizeStickForDisplay(x, y) {
            const cx = Math.max(-1, Math.min(1, x ?? 0));
            const cy = Math.max(-1, Math.min(1, y ?? 0));
            const deadzone = 0.06;
            const edgeSnapFrom = 0.84;

            const mag = Math.sqrt(cx * cx + cy * cy);
            if (mag < deadzone || mag === 0) return [0, 0];

            const nx = cx / mag;
            const ny = cy / mag;
            let normalizedMag = (mag - deadzone) / (1 - deadzone);
            normalizedMag = Math.max(0, Math.min(1, normalizedMag));

            // Если стик почти у края, визуально "дотягиваем" до идеальной окружности.
            if (normalizedMag >= edgeSnapFrom) normalizedMag = 1;

            return [nx * normalizedMag, ny * normalizedMag];
        },

        clearTrail(gpIndex, side) {
            const gp = this.gamepads.find(g => g.index === gpIndex);
            if (!gp) return;
            if (side === 'left')  gp.leftTrail  = [];
            if (side === 'right') gp.rightTrail = [];
        },

        detectType(id) {
            const s = (id || '').toLowerCase();
            if (s.includes('dualsense') || s.includes('dualshock') || s.includes('playstation') || s.includes('054c:')) return 'playstation';
            if (s.includes('nintendo')  || s.includes('pro controller') || s.includes('joy-con') || s.includes('057e:')) return 'nintendo';
            if (s.includes('xbox')      || s.includes('045e:')) return 'xbox';
            return 'generic';
        },

        getBtnNames(type) {
            const map = {
                playstation: { lt:'L2', rt:'R2', lb:'L1', rb:'R1', back:'Select', start:'Options', home:'PS',   ls:'Левый стик',  rs:'Правый стик', l3:'L3', r3:'R3' },
                nintendo:    { lt:'ZL', rt:'ZR', lb:'L',  rb:'R',  back:'−',      start:'+',        home:'Home', ls:'Левый стик',  rs:'Правый стик', l3:'L3', r3:'R3' },
                xbox:        { lt:'LT', rt:'RT', lb:'LB', rb:'RB', back:'Back',   start:'Start',    home:'Home', ls:'Левый стик',  rs:'Правый стик', l3:'L3', r3:'R3' },
                generic:     { lt:'LT', rt:'RT', lb:'LB', rb:'RB', back:'Back',   start:'Start',    home:'Home', ls:'Левый стик',  rs:'Правый стик', l3:'L3', r3:'R3' },
            };
            return map[type] || map.generic;
        },

        getFaceButtons(type) {
            const PS = {
                top:    { i:3, svgLabel:'△', svgFill:'#2dd4bf', svgStroke:'#0d9488', svgIdleColor:'#5eead4' },
                left:   { i:2, svgLabel:'□', svgFill:'#e879f9', svgStroke:'#c026d3', svgIdleColor:'#d946ef' },
                right:  { i:1, svgLabel:'○', svgFill:'#f87171', svgStroke:'#dc2626', svgIdleColor:'#f87171' },
                bottom: { i:0, svgLabel:'✕', svgFill:'#60a5fa', svgStroke:'#2563eb', svgIdleColor:'#60a5fa' },
            };
            const NI = {
                top:    { i:3, svgLabel:'X', svgFill:'#38bdf8', svgStroke:'#0284c7', svgIdleColor:'#7dd3fc' },
                left:   { i:2, svgLabel:'Y', svgFill:'#fbbf24', svgStroke:'#d97706', svgIdleColor:'#fbbf24' },
                right:  { i:1, svgLabel:'A', svgFill:'#f87171', svgStroke:'#dc2626', svgIdleColor:'#f87171' },
                bottom: { i:0, svgLabel:'B', svgFill:'#fbbf24', svgStroke:'#d97706', svgIdleColor:'#fbbf24' },
            };
            const XB = {
                top:    { i:3, svgLabel:'Y', svgFill:'#fbbf24', svgStroke:'#d97706', svgIdleColor:'#fbbf24' },
                left:   { i:2, svgLabel:'X', svgFill:'#60a5fa', svgStroke:'#2563eb', svgIdleColor:'#7db9f7' },
                right:  { i:1, svgLabel:'B', svgFill:'#f87171', svgStroke:'#dc2626', svgIdleColor:'#f87171' },
                bottom: { i:0, svgLabel:'A', svgFill:'#34d399', svgStroke:'#059669', svgIdleColor:'#6ee7b7' },
            };
            return { playstation:PS, nintendo:NI, xbox:XB, generic:XB }[type] || XB;
        },

        destroy() {
            if (this.rafId) { cancelAnimationFrame(this.rafId); this.rafId = null; }
        },
    };
}
</script>
@endpush
@endsection
