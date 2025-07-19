<div
    id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="group bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:shadow-2xl transition duration-300 ease-in-out rounded-2xl p-6 cursor-pointer space-y-4"
>
    {{-- Header --}}
    <div class="flex justify-between items-start">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white leading-snug line-clamp-2">
            {{ \Illuminate\Support\Str::limit($record->{static::$recordTitleAttribute}, 10) }}
        </h3>
        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 font-semibold">
            {{ $record->code }}
        </span>
    </div>

    {{-- Description --}}
    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
        {!! \Illuminate\Support\Str::limit($record->description) !!}
    </p>

    {{-- File & Photo Indicators --}}
    <div class="flex items-center gap-6 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-1">
            <x-heroicon-o-photo class="w-4 h-4" />
            ფოტო ({{ count($record->images) }})
        </div>
        <div class="flex items-center gap-1">
            <x-heroicon-o-document class="w-4 h-4" />
            ფაილი ({{ count($record->attachments) }})
        </div>
    </div>

    {{-- Created Date --}}
    <div class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
        <x-heroicon-o-calendar class="w-4 h-4" />
        {{ $record->started_at?->format('d M Y') ?? $record->created_at->translatedFormat('d M Y') }}
    </div>

    {{-- Assignees --}}
    <div class="flex items-center gap-3">
        <div class="flex -space-x-2">
            @foreach($record->assignees->take(3) as $user)
                <img
                    class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-900 object-cover"
                    src="{{ getImageUrl($user->image) }}"
                    alt="{{ $user->name }}"
                >
            @endforeach

            @if($record->assignees->count() > 3)
                <div class="w-8 h-8 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-900 text-gray-700 dark:text-gray-300">
                    +{{ $record->assignees->count() - 3 }}
                </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-800">
        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300" style="color:  {{ priority_array($record->priority)['color'] }} ">
            {{ priority_array($record->priority)['label'] }}
        </span>
    </div>
</div>
