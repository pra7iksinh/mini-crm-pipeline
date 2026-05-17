@props(['status', 'leads'])

<div
    data-column="{{ $status->value }}"
    class="flex h-full w-80 shrink-0 flex-col gap-4 max-lg:w-[calc(100vw-3rem)] max-lg:snap-start max-lg:snap-always max-lg:pr-4"
>
    {{-- Column Header --}}
    <div class="sticky top-0 z-10 flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-2">
            <h3 class="font-medium text-zinc-900 dark:text-zinc-100">
                {{ $status->label() }}
            </h3>
            <span class="flex h-5 items-center justify-center rounded-full bg-zinc-200 px-2 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                {{ $leads->count() }}
            </span>
        </div>

        <button type="button" @click="$dispatch('open-add-modal'); $wire.addLead('{{ $status->value }}')" class="text-zinc-400 transition-colors hover:text-zinc-600 dark:hover:text-zinc-300">
            <svg wire:loading.remove wire:target="addLead" xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
            </svg>
            <svg wire:loading wire:target="addLead" class="size-5 shrink-0 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </div>

    {{-- Cards Container / Drop Zone --}}
    <div
        x-data="{
            dragOverCount: 0,
            dropIndex: null,
            _raf: null,

            ph() {
                return $el.querySelector('[data-placeholder]');
            },

            updateDrop(event) {
                const y = event.clientY;
                if (this._raf) cancelAnimationFrame(this._raf);
                this._raf = requestAnimationFrame(() => {
                    this._raf = null;
                    const ph = this.ph();

                    // Hide placeholder temporarily so card positions are unaffected by it
                    if (ph) ph.hidden = true;

                    // Sync placeholder height to the card being dragged
                    const dragging = document.querySelector('[data-dragging]');
                    if (ph && dragging) ph.style.height = dragging.offsetHeight + 'px';

                    // Cards excluding the one being dragged
                    const cards = [...$el.querySelectorAll('[draggable=true]:not([data-dragging])')];
                    let idx = cards.length;
                    let insertBefore = null;

                    for (let i = 0; i < cards.length; i++) {
                        const rect = cards[i].getBoundingClientRect();
                        if (y < rect.top + rect.height / 2) {
                            idx = i;
                            insertBefore = cards[i];
                            break;
                        }
                    }

                    this.dropIndex = idx;

                    // Re-insert placeholder at the correct position and show it
                    if (ph) {
                        if (insertBefore) {
                            $el.insertBefore(ph, insertBefore);
                        } else if (cards.length === 0) {
                            $el.insertBefore(ph, $el.firstElementChild);
                        } else {
                            $el.appendChild(ph);
                        }
                        ph.hidden = false;
                    }
                });
            },

            hidePlaceholder() {
                const ph = this.ph();
                if (ph) ph.hidden = true;
                if (this._raf) { cancelAnimationFrame(this._raf); this._raf = null; }
            }
        }"
        @dragenter="dragOverCount++"
        @dragleave="dragOverCount = Math.max(0, dragOverCount - 1); if (dragOverCount === 0) hidePlaceholder()"
        @dragover.prevent="updateDrop($event)"
        @drop.prevent="
            hidePlaceholder();
            const idx = dropIndex ?? 999;
            dragOverCount = 0;
            dropIndex = null;
            $wire.moveLeadToStatus($event.dataTransfer.getData('leadId'), '{{ $status->value }}', idx)
        "
        :class="{ 'ring-2 ring-amber-400/60 rounded-lg bg-amber-50/30 dark:bg-amber-900/10': dragOverCount > 0 }"
        class="crm-scroll relative min-h-0 flex flex-1 flex-col gap-3 overflow-y-auto pb-2"
    >
        {{-- Drop placeholder — hidden by default, moved into position by JS during drag --}}
        <div
            data-placeholder
            hidden
            class="shrink-0 rounded-lg border-2 border-dashed border-amber-400/80 bg-amber-50/50 dark:border-amber-500/60 dark:bg-amber-900/20"
        ></div>

        @forelse($leads as $lead)
            <x-crm.lead-card :lead="$lead" wire:key="lead-{{ $lead->id }}" />
        @empty
            <div data-empty-state class="flex flex-col items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-zinc-50/50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 size-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">No leads here</p>
                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Drag a lead to this column</p>
            </div>
        @endforelse
    </div>
</div>
