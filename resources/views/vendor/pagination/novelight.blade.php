@if ($paginator->hasPages())
    <div class="page-pagination">
        {{-- Previous --}}
        @if (!$paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-invincible page-num" rel="prev"><i class="fa fa-angle-left"></i></a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="btn btn-invincible page-num disabled">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn page-num active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-invincible page-num">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-invincible page-num" rel="next"><i class="fa fa-angle-right"></i></a>
        @endif
    </div>

    <style>
    .page-pagination .page-num {
        min-width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
        padding: 0 10px; border-radius: 6px; font-weight: 600; text-decoration: none;
        margin-right: 8px !important;
    }
    .page-pagination .page-num.active { background: #2b2b2b; color: #f0c040; }
    .page-pagination .page-num.disabled { opacity: .5; pointer-events: none; }
    </style>
@endif
