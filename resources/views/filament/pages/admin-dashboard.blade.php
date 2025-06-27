<x-filament::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        @foreach ($this->getHeaderWidgets() as $widget)
            {{ $widget }}
        @endforeach
    </div>

    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->getWidgets() as $widget)
            {{ $widget }}
        @endforeach
    </div>
</x-filament::page> 