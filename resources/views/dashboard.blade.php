<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col overflow-hidden">
        @livewire(\App\Livewire\CRM\PipelineBoard::class)
    </div>
</x-layouts::app>
