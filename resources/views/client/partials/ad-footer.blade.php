{{-- Footer ads (display_mode = footer): chèn script/HTML nguyên văn ngay trước </body>.
     Dùng cho mã bên thứ ba: ad network loader, consent (Cookiebot), analytics...
     Lọc theo trang + lịch hẹn + ẩn-với-VIP qua ads_for(). Chỉ admin nhập nên in nguyên văn. --}}
@foreach((ads_for()['footer'] ?? collect()) as $__footerAd)
    @if($__footerAd->script_code)
        {!! $__footerAd->script_code !!}
    @elseif($__footerAd->image)
        @if($__footerAd->link)<a href="{{ $__footerAd->link }}" target="_blank" rel="nofollow noopener">@endif<img src="{{ $__footerAd->image }}" alt="{{ $__footerAd->name }}" style="max-width:100%">@if($__footerAd->link)</a>@endif
    @endif
@endforeach
