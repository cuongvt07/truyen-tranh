@php
    $adLink = $ad->link;
    $adImage = $ad->image;
    $adTitle = $ad->title ?? $ad->name;
@endphp

@if($adImage)
    <div class="chapter-inline-ad" data-ad-id="{{ $ad->id }}">
        <div class="chapter-inline-ad__inner">
            @if($adLink)
                <a href="{{ $adLink }}" target="_blank" rel="nofollow noopener">
            @endif
                <img src="{{ $adImage }}" alt="{{ $adTitle }}">
                <div class="chapter-inline-ad__title">{{ $adTitle }}</div>
            @if($adLink)
                </a>
            @endif
        </div>
    </div>
@endif
