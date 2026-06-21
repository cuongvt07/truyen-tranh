@php
    $adLink = $ad->link;
    $adImage = $ad->image;
    $adScript = $ad->script_code;
    $adTitle = $ad->title ?? $ad->name;
@endphp

@if($adImage || $adScript)
    <div class="chapter-inline-ad" data-ad-id="{{ $ad->id }}">
        <div class="chapter-inline-ad__inner">
            @if($adScript)
                {!! $adScript !!}
            @else
                @if($adLink)
                <a href="{{ $adLink }}" target="_blank" rel="nofollow noopener">
                @endif
                    <img src="{{ $adImage }}" alt="{{ $adTitle }}">
                    <div class="chapter-inline-ad__title">{{ $adTitle }}</div>
                @if($adLink)
                </a>
                @endif
            @endif
        </div>
    </div>
@endif
