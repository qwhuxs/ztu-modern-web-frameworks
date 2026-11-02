<div class="pagination">
    <span>Усього записів: {{ $items->total() }}</span>
    @for ($page = 1; $page <= $items->lastPage(); $page++)
        @if ($page === $items->currentPage())
            <span class="current">{{ $page }}</span>
        @else
            <a href="{{ $items->url($page) }}">{{ $page }}</a>
        @endif
    @endfor
</div>