<div class="h-full w-full overflow-x-auto px-6 pt-4 pb-4"
    x-data="{ 
        showModal: @entangle('showModal'),
        deleteModalOpen: false,
        leadIdToDelete: null,
        isLoading: false
    }"
    @confirm-delete.window="deleteModalOpen = true; leadIdToDelete = $event.detail.id"
    @lead-deleted.window="deleteModalOpen = false"
    @open-edit-modal.window="showModal = true; isLoading = true"
    @open-add-modal.window="showModal = true; isLoading = false"
    @modal-loaded.window="isLoading = false"
>
    <div class="flex h-full gap-6 px-1">
        @foreach($this->statuses as $status)
            <x-crm.lead-column 
                :status="$status" 
                :leads="$this->leadsByStatus->get($status->value, collect())" 
            />
        @endforeach
    </div>

    {{-- Modal --}}
    <div x-cloak x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm transition-opacity"></div>
        
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showModal" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg dark:bg-zinc-900 dark:border dark:border-zinc-800"
                    @click.outside="showModal = false"
                >
                    <form wire:submit="saveLead">
                        <div class="relative px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            {{-- Centered Loader Overlay --}}
                            <div x-show="isLoading" x-transition.opacity class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/60 backdrop-blur-sm dark:bg-zinc-900/60">
                                <svg class="size-8 animate-spin text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            {{-- Form Content (Always rendered to maintain modal height) --}}
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100" id="modal-title">
                                    {{ $isEditing ? 'Edit Lead' : 'Add New Lead' }}
                                </h3>
                                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.blur="title" placeholder="e.g. Acme Corp Redesign" class="mt-1 block w-full rounded-md border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base dark:bg-zinc-800 dark:text-zinc-100 transition-colors @error('title') border-red-500 ring-1 ring-red-500 focus:border-red-500 focus:ring-red-500 dark:border-red-500 @else dark:border-zinc-700 @enderror">
                                        @error('title') <span class="mt-1 block text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email <span class="text-red-500">*</span></label>
                                        <input type="email" wire:model.blur="email" placeholder="contact@example.com" class="mt-1 block w-full rounded-md border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base dark:bg-zinc-800 dark:text-zinc-100 transition-colors @error('email') border-red-500 ring-1 ring-red-500 focus:border-red-500 focus:ring-red-500 dark:border-red-500 @else dark:border-zinc-700 @enderror">
                                        @error('email') <span class="mt-1 block text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Phone</label>
                                        <input type="tel" wire:model.blur="phone" placeholder="+1 (555) 123-4567" pattern="\+?[\d\s\-()]{7,20}" title="Enter a valid phone number (7–20 digits; may include +, spaces, dashes, parentheses)" x-on:input="$el.value = $el.value.replace(/[^\d\s\-()+]/g, ''); $el.dispatchEvent(new Event('input'))" class="mt-1 block w-full rounded-md border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base dark:bg-zinc-800 dark:text-zinc-100 transition-colors @error('phone') border-red-500 ring-1 ring-red-500 focus:border-red-500 focus:ring-red-500 dark:border-red-500 @else dark:border-zinc-700 @enderror">
                                        @error('phone') <span class="mt-1 block text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
                                        <select wire:model.blur="status" class="mt-1 block w-full rounded-md border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base dark:bg-zinc-800 dark:text-zinc-100 transition-colors @error('status') border-red-500 ring-1 ring-red-500 focus:border-red-500 focus:ring-red-500 dark:border-red-500 @else dark:border-zinc-700 @enderror">
                                            @foreach($this->statuses as $statusOption)
                                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('status') <span class="mt-1 block text-sm font-medium text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-zinc-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-zinc-800/50">
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto transition-colors relative">
                                <span wire:loading.remove wire:target="saveLead">Save Lead</span>
                                <span wire:loading.flex wire:target="saveLead" class="items-center justify-center gap-2">
                                    <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Saving...
                                </span>
                            </button>
                            <button type="button" @click="showModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-zinc-900 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-600 dark:hover:bg-zinc-700 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-cloak x-show="deleteModalOpen" class="relative z-50" aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
        <div x-show="deleteModalOpen" x-transition.opacity class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm transition-opacity"></div>
        
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="deleteModalOpen" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    @click.outside="deleteModalOpen = false"
                    @keydown.escape.window="deleteModalOpen = false"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md dark:bg-zinc-900 dark:border dark:border-zinc-800"
                >
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-zinc-900">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:size-10 dark:bg-red-900/30">
                                <svg class="size-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100" id="delete-modal-title">Delete Lead</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Are you sure you want to delete this lead? This action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-zinc-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-zinc-800/50">
                        <button type="button" 
                                @click="$wire.deleteLead(leadIdToDelete)"
                                wire:loading.attr="disabled"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto transition-colors"
                        >
                            <span wire:loading.remove wire:target="deleteLead">Yes, delete it!</span>
                            <span wire:loading wire:target="deleteLead" class="flex items-center gap-2">
                                <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Deleting...
                            </span>
                        </button>
                        <button type="button" @click="deleteModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-zinc-900 shadow-sm ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 sm:mt-0 sm:w-auto dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-600 dark:hover:bg-zinc-700 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
