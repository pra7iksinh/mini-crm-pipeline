@props(['lead'])

@php
    $avatarColors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300',
        'bg-violet-100 text-violet-700 dark:bg-violet-900/50 dark:text-violet-300',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
        'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
        'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300',
        'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300',
    ];
    $avatarColor = $avatarColors[abs(crc32($lead->id)) % count($avatarColors)];

    $words = preg_split('/\s+/', trim($lead->title));
    $initials = count($words) >= 2
        ? strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1))
        : strtoupper(mb_substr($lead->title, 0, 2));

    $wasUpdated = $lead->updated_at->gt($lead->created_at->addMinutes(5));
    $allStatuses = \App\Enums\LeadStatus::cases();

    $shortTime = function (string $time): string {
        $time = str_replace(
            ['a minute', 'an hour', 'a day', 'a week', 'a month', 'a year'],
            ['1 min',    '1 hr',    '1 d',   '1 wk',   '1 mo',    '1 yr'],
            $time
        );
        return preg_replace(
            ['/\bseconds?\b/', '/\bminutes?\b/', '/\bhours?\b/', '/\bdays?\b/', '/\bweeks?\b/', '/\bmonths?\b/', '/\byears?\b/'],
            ['sec',            'min',            'hr',           'd',           'wk',           'mo',            'yr'],
            $time
        );
    };
@endphp

<div
    x-data="{ expanded: false, flash: false, dragging: false }"
    @lead-highlight.window="if ($event.detail.leadId === '{{ $lead->id }}') { flash = true; setTimeout(() => flash = false, 1500) }"
    :class="{ 'opacity-40 scale-95 shadow-none': dragging }"
    class="group relative flex cursor-grab flex-col rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition-[border-color,box-shadow] duration-150 hover:border-zinc-300 hover:shadow-md active:cursor-grabbing dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600"
    draggable="true"
    @dragstart="dragging = true; $el.dataset.dragging = '1'; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('leadId', '{{ $lead->id }}')"
    @dragend="dragging = false; delete $el.dataset.dragging"
>
    {{-- Highlight overlay: fades out after card is created or moved to a new column --}}
    <div
        x-show="flash"
        x-transition:leave="transition-all duration-700 ease-out"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="pointer-events-none absolute inset-0 rounded-lg ring-2 ring-amber-400/80 bg-amber-50/60 dark:ring-amber-400/50 dark:bg-amber-900/25"
    ></div>

    {{-- Header: Avatar + Title + Actions --}}
    <div class="flex items-start gap-3">
        <div class="flex size-9 shrink-0 select-none items-center justify-center rounded-full text-xs font-bold {{ $avatarColor }}">
            {{ $initials }}
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <h4 class="text-sm font-semibold leading-snug text-zinc-900 line-clamp-2 dark:text-zinc-100">
                    {{ $lead->title }}
                </h4>

                <div class="flex shrink-0 items-center gap-1 max-lg:opacity-100 lg:opacity-0 lg:transition-opacity lg:group-hover:opacity-100">
                    <button type="button"
                        @click="$dispatch('open-edit-modal'); $wire.editLead('{{ $lead->id }}').then(() => { $dispatch('modal-loaded') })"
                        title="Edit"
                        class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-indigo-600 dark:hover:bg-zinc-800 dark:hover:text-indigo-400"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                            <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                        </svg>
                    </button>
                    <button type="button"
                        @click="$dispatch('confirm-delete', { id: '{{ $lead->id }}' })"
                        title="Delete"
                        class="rounded p-1 text-zinc-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm3.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Status Badge --}}
            <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium {{ $lead->status->badgeClasses() }}">
                <span class="size-1.5 rounded-full {{ $lead->status->dotClass() }}"></span>
                {{ $lead->status->label() }}
            </span>
        </div>
    </div>

    {{-- Toggle --}}
    <button
        @click="expanded = !expanded"
        type="button"
        class="mt-3 flex items-center gap-1 text-xs font-medium text-zinc-400 transition-colors hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="size-3 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
        <span x-text="expanded ? 'Hide details' : 'Show details'"></span>
    </button>

    {{-- Expandable Contact Details --}}
    <div x-show="expanded" x-collapse x-cloak class="space-y-1.5 border-t border-zinc-100 pt-3 mt-3 dark:border-zinc-800">
        <a href="mailto:{{ $lead->email }}" class="flex items-center gap-2 text-sm text-zinc-500 transition-colors hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
            </svg>
            <span class="truncate">{{ $lead->email }}</span>
        </a>

        @if($lead->phone)
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $lead->phone) }}" class="flex items-center gap-2 text-sm text-zinc-500 transition-colors hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 006.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd" />
                </svg>
                <span>{{ $lead->phone }}</span>
            </a>
        @endif
    </div>

    {{-- Move To: mobile-only dropdown (drag-and-drop unavailable on touch) --}}
    <div class="lg:hidden mt-3">
        <select
            @change="$wire.moveLeadToStatus('{{ $lead->id }}', $event.target.value); $event.target.value = ''"
            class="w-full cursor-pointer rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-500 transition-colors focus:border-indigo-400 focus:outline-none focus:ring-1 focus:ring-indigo-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400"
        >
            <option value="" disabled selected>Move to…</option>
            @foreach($allStatuses as $s)
                @if($s->value !== $lead->status->value)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endif
            @endforeach
        </select>
    </div>

    {{-- Footer --}}
    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 border-t border-zinc-100 pt-3 text-xs text-zinc-400 dark:border-zinc-800 dark:text-zinc-500">
        <span
            class="flex items-center gap-1 whitespace-nowrap"
            title="{{ $lead->created_at->toDayDateTimeString() }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="size-3 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
            </svg>
            <span>Created <span class="font-medium text-zinc-500 dark:text-zinc-400">{{ $shortTime($lead->created_at->diffForHumans()) }}</span></span>
        </span>

        @if($wasUpdated)
            <span
                class="flex items-center gap-1 whitespace-nowrap"
                title="{{ $lead->updated_at->toDayDateTimeString() }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                </svg>
                <span>Updated <span class="font-medium text-zinc-500 dark:text-zinc-400">{{ $shortTime($lead->updated_at->diffForHumans()) }}</span></span>
            </span>
        @endif
    </div>
</div>
