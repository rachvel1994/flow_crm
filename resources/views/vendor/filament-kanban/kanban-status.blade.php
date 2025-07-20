@props(['status'])
<div class="flex-shrink-0 mb-5 md:min-h-full flex flex-col"
     style="width: 300px; {{ 'display: ' . $status['visible']}}">
    @include(static::$headerView)

    <div
        data-status-id="{{ $status['id'] }}"
        class="flex flex-col flex-1 gap-2 p-3 bg-gray-200 dark:bg-gray-800 rounded-xl"
        style="background-color: #f3f2ff!important;"
    >
        @foreach($status['records'] as $record)
            @include(static::$recordView)
        @endforeach
    </div>
</div>
