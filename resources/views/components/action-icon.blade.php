@props(['href' => '#', 'color' => 'gray', 'icon' => ''])

<a href="{{ $href }}" class="text-{{ $color }}-500 hover:text-{{ $color }}-600 transition" title="{{ $title ?? '' }}">
    <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5" />
</a>
