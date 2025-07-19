<h3 class="mb-2 px-4 font-semibold text-lg" style="background-color: {{$status['color']}};
height: 50px;
min-height: 50px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
">
    <span class="text-primary-400">❖</span>
    {{ $status['title'] }}
    <span class="text-sm">({{ count($status['records']) }})</span>
</h3>
